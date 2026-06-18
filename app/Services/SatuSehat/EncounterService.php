<?php

namespace App\Services\SatuSehat;

use App\Models\MedicalRecord;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class EncounterService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createEncounter(MedicalRecord $mr): ?array
    {
        $resource = $this->buildEncounterResource($mr);

        $response = $this->client->post('Encounter', $resource);

        if ($response && isset($response['id'])) {
            $this->saveResourceReference($mr, 'Encounter', $response['id'], $response);

            Log::info('Encounter created on Satu Sehat', [
                'medical_record_id' => $mr->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function updateEncounter(MedicalRecord $mr): ?array
    {
        $resourceRef = $this->getResourceReference($mr, 'Encounter');
        if (!$resourceRef) {
            return $this->createEncounter($mr);
        }

        $resource = $this->buildEncounterResource($mr);
        $resource['id'] = $resourceRef->resource_id_ss;

        $response = $this->client->put('Encounter/' . $resourceRef->resource_id_ss, $resource);

        if ($response && isset($response['id'])) {
            $resourceRef->update([
                'version' => $response['meta']['versionId'] ?? ($resourceRef->version + 1),
                'payload' => $response,
                'sync_at' => now(),
            ]);
        }

        return $response;
    }

    public function syncEncounter(MedicalRecord $mr): ?array
    {
        $resourceRef = $this->getResourceReference($mr, 'Encounter');
        if ($resourceRef && $resourceRef->status === 'synced') {
            return $this->updateEncounter($mr);
        }
        return $this->createEncounter($mr);
    }

    public function getEncounter(string $ssId): ?array
    {
        return $this->client->getResourceById('Encounter', $ssId);
    }

    public function closeEncounter(MedicalRecord $mr): ?array
    {
        $resourceRef = $this->getResourceReference($mr, 'Encounter');
        if (!$resourceRef) {
            return null;
        }

        $existing = $this->getEncounter($resourceRef->resource_id_ss);
        if (!$existing) {
            return null;
        }

        $existing['status'] = 'finished';
        $existing['period']['end'] = now()->format('Y-m-d\TH:i:sP');

        $response = $this->client->put('Encounter/' . $resourceRef->resource_id_ss, $existing);

        if ($response && isset($response['id'])) {
            $resourceRef->update([
                'version' => $response['meta']['versionId'] ?? ($resourceRef->version + 1),
                'payload' => $response,
                'sync_at' => now(),
            ]);

            Log::info('Encounter closed on Satu Sehat', [
                'medical_record_id' => $mr->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    protected function buildEncounterResource(MedicalRecord $mr): array
    {
        $mr->loadMissing(['patient', 'doctor', 'polyclinic', 'registration']);

        $patientRef = $this->getPatientSsId($mr->patient);
        $practitionerRef = $this->getPractitionerSsId($mr->doctor);
        $locationRef = $this->getLocationSsId($mr->polyclinic);
        $orgId = config('satusehat.organization_id');

        $resource = [
            'resourceType' => 'Encounter',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => 'http://sys-ids.kemkes.go.id/encounter/' . $orgId,
                    'value' => $mr->registration?->registration_number ?? 'MR-' . $mr->id,
                ],
            ],
            'status' => 'arrived',
            'class' => [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ActCode',
                'code' => 'AMB',
                'display' => 'ambulatory',
            ],
            'subject' => [
                'reference' => 'Patient/' . ($patientRef ?? $mr->patient->nik),
                'display' => $mr->patient->name,
            ],
            'period' => [
                'start' => $mr->visit_date?->format('Y-m-d\TH:i:sP') ?? now()->format('Y-m-d\TH:i:sP'),
            ],
            'serviceProvider' => [
                'reference' => 'Organization/' . $orgId,
            ],
            'statusHistory' => [
                [
                    'status' => 'arrived',
                    'period' => [
                        'start' => $mr->visit_date?->format('Y-m-d\TH:i:sP') ?? now()->format('Y-m-d\TH:i:sP'),
                    ],
                ],
            ],
        ];

        if ($practitionerRef) {
            $resource['participant'] = [
                [
                    'individual' => [
                        'reference' => 'Practitioner/' . $practitionerRef,
                        'display' => $mr->doctor->name,
                    ],
                ],
            ];
        }

        if ($locationRef) {
            $resource['location'] = [
                [
                    'location' => [
                        'reference' => 'Location/' . $locationRef,
                        'display' => $mr->polyclinic?->name ?? 'Poli',
                    ],
                ],
            ];
        }

        return $resource;
    }

    protected function getLocationSsId($polyclinic): ?string
    {
        if (!$polyclinic) {
            return null;
        }

        return SatusehatResource::where('model_type', get_class($polyclinic))
            ->where('model_id', $polyclinic->id)
            ->where('resource_type', 'Location')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function getPatientSsId($patient): ?string
    {
        if (!$patient) {
            return null;
        }

        return SatusehatResource::where('model_type', get_class($patient))
            ->where('model_id', $patient->id)
            ->where('resource_type', 'Patient')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function getPractitionerSsId($doctor): ?string
    {
        if (!$doctor) {
            return null;
        }

        return SatusehatResource::where('model_type', get_class($doctor))
            ->where('model_id', $doctor->id)
            ->where('resource_type', 'Practitioner')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function getResourceReference(MedicalRecord $mr, string $resourceType): ?SatusehatResource
    {
        return SatusehatResource::where('model_type', get_class($mr))
            ->where('model_id', $mr->id)
            ->where('resource_type', $resourceType)
            ->first();
    }

    protected function saveResourceReference(MedicalRecord $mr, string $resourceType, string $ssId, array $response): SatusehatResource
    {
        $ref = $this->getResourceReference($mr, $resourceType);

        if ($ref) {
            $ref->update([
                'resource_id_ss' => $ssId,
                'version' => $response['meta']['versionId'] ?? 1,
                'payload' => $response,
                'status' => 'synced',
                'sync_at' => now(),
            ]);

            return $ref;
        }

        return SatusehatResource::create([
            'model_type' => get_class($mr),
            'model_id' => $mr->id,
            'resource_type' => $resourceType,
            'resource_id_ss' => $ssId,
            'version' => $response['meta']['versionId'] ?? 1,
            'payload' => $response,
            'status' => 'synced',
            'sync_at' => now(),
        ]);
    }
}

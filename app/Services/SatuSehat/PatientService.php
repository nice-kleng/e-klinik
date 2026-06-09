<?php

namespace App\Services\SatuSehat;

use App\Models\Patient;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class PatientService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createPatient(Patient $patient): ?array
    {
        $resource = $this->buildPatientResource($patient);

        $response = $this->client->post('Patient', $resource);

        if ($response && isset($response['id'])) {
            $this->saveResourceReference($patient, 'Patient', $response['id'], $response);

            Log::info('Patient created on Satu Sehat', [
                'patient_id' => $patient->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function updatePatient(Patient $patient): ?array
    {
        $resourceRef = $this->getResourceReference($patient, 'Patient');
        if (!$resourceRef) {
            return $this->createPatient($patient);
        }

        $resource = $this->buildPatientResource($patient);
        $resource['id'] = $resourceRef->resource_id_ss;

        $response = $this->client->put('Patient/' . $resourceRef->resource_id_ss, $resource);

        if ($response && isset($response['id'])) {
            $resourceRef->update([
                'resource_id_ss' => $response['id'],
                'version' => $response['meta']['versionId'] ?? ($resourceRef->version + 1),
                'payload' => $response,
                'status' => 'synced',
                'sync_at' => now(),
            ]);

            Log::info('Patient updated on Satu Sehat', [
                'patient_id' => $patient->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function getPatient(string $ssId): ?array
    {
        return $this->client->getResourceById('Patient', $ssId);
    }

    public function searchPatient(string $nik): ?array
    {
        return $this->client->get('Patient', ['identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik]);
    }

    public function syncPatient(Patient $patient): ?array
    {
        $resourceRef = $this->getResourceReference($patient, 'Patient');

        if ($resourceRef && $resourceRef->status === 'synced') {
            return $this->updatePatient($patient);
        }

        return $this->createPatient($patient);
    }

    protected function buildPatientResource(Patient $patient): array
    {
        $nameParts = explode(' ', $patient->name, 2);
        $givenName = $nameParts[0] ?? '';
        $familyName = $nameParts[1] ?? '';

        $resource = [
            'resourceType' => 'Patient',
            'identifier' => [
                [
                    'use' => 'official',
                    'system' => 'https://fhir.kemkes.go.id/id/nik',
                    'value' => $patient->nik,
                ],
            ],
            'name' => [
                [
                    'use' => 'official',
                    'family' => $familyName,
                    'given' => [$givenName],
                ],
            ],
            'gender' => $patient->gender === 'L' ? 'male' : 'female',
            'birthDate' => $patient->birth_date?->format('Y-m-d'),
            'address' => [
                [
                    'use' => 'home',
                    'line' => array_filter([$patient->address]),
                    'city' => $patient->city ?? '',
                    'district' => $patient->district ?? '',
                    'state' => $patient->province ?? '',
                ],
            ],
            'telecom' => [
                [
                    'system' => 'phone',
                    'value' => $patient->phone ?? '',
                    'use' => 'mobile',
                ],
            ],
        ];

        if (!empty($patient->birth_place)) {
            $resource['extension'] = [
                [
                    'url' => 'https://fhir.kemkes.go.id/id/extension/place-of-birth',
                    'valueString' => $patient->birth_place,
                ],
            ];
        }

        return $resource;
    }

    protected function getResourceReference(Patient $patient, string $resourceType): ?SatusehatResource
    {
        return SatusehatResource::where('model_type', get_class($patient))
            ->where('model_id', $patient->id)
            ->where('resource_type', $resourceType)
            ->first();
    }

    protected function saveResourceReference(Patient $patient, string $resourceType, string $ssId, array $response): SatusehatResource
    {
        $ref = $this->getResourceReference($patient, $resourceType);

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
            'model_type' => get_class($patient),
            'model_id' => $patient->id,
            'resource_type' => $resourceType,
            'resource_id_ss' => $ssId,
            'version' => $response['meta']['versionId'] ?? 1,
            'payload' => $response,
            'status' => 'synced',
            'sync_at' => now(),
        ]);
    }
}

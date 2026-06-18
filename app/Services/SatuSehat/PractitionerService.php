<?php

namespace App\Services\SatuSehat;

use App\Models\Doctor;
use App\Models\SatusehatResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PractitionerService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createPractitioner(Doctor $doctor): ?array
    {
        $resource = $this->buildPractitionerResource($doctor);

        $response = $this->client->post('Practitioner', $resource);

        if ($response && isset($response['id'])) {
            $this->saveResourceReference($doctor, 'Practitioner', $response['id'], $response);

            Log::info('Practitioner created on Satu Sehat', [
                'doctor_id' => $doctor->id,
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function getPractitioner(string $ssId): ?array
    {
        return $this->client->getResourceById('Practitioner', $ssId);
    }

    public function searchPractitioner(string $nik): ?array
    {
        return $this->client->get('Practitioner', ['identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik]);
    }

    public function syncPractitioner(Doctor $doctor): ?array
    {
        $resourceRef = $this->getResourceReference($doctor, 'Practitioner');
        if ($resourceRef && $resourceRef->status === 'synced') {
            return $resourceRef->payload;
        }

        if (!$resourceRef) {
            $nik = $doctor->user?->nik;
            if (!empty($nik)) {
                $existing = $this->searchPractitioner($nik);
                if ($existing && isset($existing['entry'][0]['resource']['id'])) {
                    $ssId = $existing['entry'][0]['resource']['id'];
                    $this->saveResourceReference($doctor, 'Practitioner', $ssId, $existing['entry'][0]['resource']);
                    return $existing['entry'][0]['resource'];
                }
            }
        }

        return $this->createPractitioner($doctor);
    }

    protected function buildPractitionerResource(Doctor $doctor): array
    {
        $doctor->loadMissing(['user']);

        $nameParts = explode(' ', $doctor->name, 2);
        $givenName = $nameParts[0] ?? '';
        $familyName = $nameParts[1] ?? '';
        $orgId = config('satusehat.organization_id');

        $identifiers = [];

        $nik = $doctor->user?->nik;
        if (!empty($nik)) {
            $identifiers[] = [
                'use' => 'official',
                'system' => 'https://fhir.kemkes.go.id/id/nik',
                'value' => $nik,
            ];
        }

        $identifiers[] = [
            'use' => 'official',
            'system' => 'http://sys-ids.kemkes.go.id/practitioner/' . $orgId,
            'value' => $doctor->sip_number ?? $doctor->code,
        ];

        $resource = [
            'resourceType' => 'Practitioner',
            'identifier' => $identifiers,
            'name' => [
                [
                    'use' => 'official',
                    'text' => $doctor->name,
                    'family' => $familyName,
                    'given' => [$givenName],
                ],
            ],
            'telecom' => [
                [
                    'system' => 'phone',
                    'value' => $doctor->phone ?? '',
                    'use' => 'mobile',
                ],
            ],
        ];

        if (!empty($doctor->sip_number)) {
            $resource['qualification'] = [
                [
                    'identifier' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/practitioner/' . $orgId,
                            'value' => $doctor->sip_number,
                        ],
                    ],
                    'code' => [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/v2-0360',
                                'code' => 'MD',
                                'display' => $doctor->specialist ?? 'Doctor',
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $resource;
    }

    protected function getResourceReference(Doctor $doctor, string $resourceType): ?SatusehatResource
    {
        return SatusehatResource::where('model_type', get_class($doctor))
            ->where('model_id', $doctor->id)
            ->where('resource_type', $resourceType)
            ->first();
    }

    protected function saveResourceReference(Doctor $doctor, string $resourceType, string $ssId, array $response): SatusehatResource
    {
        $ref = $this->getResourceReference($doctor, $resourceType);

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
            'model_type' => get_class($doctor),
            'model_id' => $doctor->id,
            'resource_type' => $resourceType,
            'resource_id_ss' => $ssId,
            'version' => $response['meta']['versionId'] ?? 1,
            'payload' => $response,
            'status' => 'synced',
            'sync_at' => now(),
        ]);
    }
}

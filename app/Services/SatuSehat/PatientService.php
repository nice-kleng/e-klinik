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
            return $resourceRef->payload;
        }

        if (!$resourceRef) {
            $existing = $this->searchPatient($patient->nik);
            if ($existing && isset($existing['entry'][0]['resource']['id'])) {
                $ssId = $existing['entry'][0]['resource']['id'];
                $this->saveResourceReference($patient, 'Patient', $ssId, $existing['entry'][0]['resource']);
                return $existing['entry'][0]['resource'];
            }
        }

        return $this->createPatient($patient);
    }

    protected function buildPatientResource(Patient $patient): array
    {
        $nameParts = explode(' ', $patient->name, 2);
        $givenName = $nameParts[0] ?? '';
        $familyName = $nameParts[1] ?? '';

        $addressExtensions = $this->buildAddressExtensions($patient);

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
                    'text' => $patient->name,
                    'family' => $familyName,
                    'given' => [$givenName],
                ],
            ],
            'gender' => $patient->gender === 'L' ? 'male' : 'female',
            'birthDate' => $patient->birth_date?->format('Y-m-d'),
            'multipleBirthBoolean' => false,
            'address' => [
                [
                    'use' => 'home',
                    'line' => array_filter([$patient->address]),
                    'city' => $patient->city ?? '',
                    'district' => $patient->district ?? '',
                    'state' => $patient->province ?? '',
                    'extension' => $addressExtensions,
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

        $extensions = [];

        if (!empty($patient->birth_place)) {
            $extensions[] = [
                'url' => 'https://fhir.kemkes.go.id/id/extension/place-of-birth',
                'valueString' => $patient->birth_place,
            ];
        }

        if (!empty($extensions)) {
            $resource['extension'] = $extensions;
        }

        return $resource;
    }

    protected function buildAddressExtensions(Patient $patient): array
    {
        $kdMap = $this->resolveKdCodes($patient);

        $subExtensions = [];
        $levels = ['province', 'city', 'district', 'village'];
        foreach ($levels as $level) {
            $code = $kdMap[$level] ?? '';
            if (!empty($code)) {
                $subExtensions[] = [
                    'url' => $level,
                    'valueCode' => $code,
                ];
            }
        }

        if (empty($subExtensions)) {
            return [];
        }

        return [[
            'url' => 'https://fhir.kemkes.go.id/r4/StructureDefinition/administrativeCode',
            'extension' => $subExtensions,
        ]];
    }

    protected function resolveKdCodes(Patient $patient): array
    {
        $kd = [
            'province' => $patient->province_kd,
            'city' => $patient->city_kd,
            'district' => $patient->district_kd,
            'village' => $patient->village_kd,
        ];

        if (!empty($kd['province'])) {
            return $kd;
        }

        return $this->lookupKdCodes($patient);
    }

    protected function lookupKdCodes(Patient $patient): array
    {
        $provinceMap = [
            'DKI Jakarta' => '31',
            'Jawa Barat' => '32',
            'Jawa Tengah' => '33',
            'Jawa Timur' => '35',
            'Sumatera Utara' => '12',
            'Bali' => '51',
            'Banten' => '36',
            'DI Yogyakarta' => '34',
            'Sulawesi Selatan' => '73',
            'Kalimantan Selatan' => '63',
            'Sumatera Selatan' => '16',
            'Aceh' => '11',
        ];

        $cityMap = [
            'Jakarta Pusat' => '3171',
            'Bandung' => '3273',
            'Semarang' => '3374',
            'Surabaya' => '3578',
            'Medan' => '1275',
            'Solo' => '3372',
            'Yogyakarta' => '3471',
            'Palembang' => '1671',
            'Makassar' => '7371',
            'Banda Aceh' => '1171',
        ];

        $province = trim($patient->province ?? '');
        $city = trim($patient->city ?? '');

        $provinceKd = $provinceMap[$province] ?? '';
        $cityKd = $cityMap[$city] ?? '';

        $districtMap = [
            '3171' => '317101',
            '3273' => '327301',
            '3374' => '337401',
            '3578' => '357801',
            '1275' => '127501',
            '3471' => '347101',
            '3372' => '337201',
            '1671' => '167101',
            '7371' => '737101',
            '1171' => '117103',
        ];

        $villageMap = [
            '317101' => '3171011001',
            '327301' => '3273011001',
            '337401' => '3374011001',
            '357801' => '3578011001',
            '127501' => '1275011001',
            '347101' => '3471011001',
            '337201' => '3372011001',
            '167101' => '1671011001',
            '737101' => '7371011001',
            '117103' => '1171032001',
        ];

        $districtKd = $districtMap[$cityKd] ?? '';
        $villageKd = $villageMap[$districtKd] ?? '';

        return [
            'province' => $provinceKd,
            'city' => $cityKd,
            'district' => $districtKd,
            'village' => $villageKd,
        ];
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

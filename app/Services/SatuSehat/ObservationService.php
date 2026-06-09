<?php

namespace App\Services\SatuSehat;

use App\Models\MedicalRecord;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class ObservationService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createVitalSign(MedicalRecord $mr): ?array
    {
        $mr->loadMissing(['patient']);

        $vitalSigns = $mr->vital_signs ?? [];
        if (empty($vitalSigns)) {
            return null;
        }

        $lastResponse = null;
        $observations = $this->buildVitalSignObservations($mr, $vitalSigns);

        foreach ($observations as $observation) {
            $response = $this->client->post('Observation', $observation);

            if ($response && isset($response['id'])) {
                $code = $observation['code']['coding'][0]['code'] ?? 'unknown';
                $this->saveResourceReference($mr, 'Observation', $response['id'], $response, $code);

                Log::info('Vital sign observation created on Satu Sehat', [
                    'medical_record_id' => $mr->id,
                    'code' => $code,
                    'ss_id' => $response['id'],
                ]);
            }

            $lastResponse = $response;
        }

        return $lastResponse;
    }

    public function createObservation(array $data): ?array
    {
        $resource = [
            'resourceType' => 'Observation',
            'status' => $data['status'] ?? 'final',
            'category' => [
                [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                            'code' => $data['category'] ?? 'vital-signs',
                            'display' => ucfirst(str_replace('-', ' ', $data['category'] ?? 'vital-signs')),
                        ],
                    ],
                ],
            ],
            'code' => [
                'coding' => [
                    [
                        'system' => $data['code_system'] ?? 'http://loinc.org',
                        'code' => $data['code'] ?? '',
                        'display' => $data['display'] ?? '',
                    ],
                ],
                'text' => $data['text'] ?? '',
            ],
            'subject' => [
                'reference' => 'Patient/' . ($data['patient_ss_id'] ?? ''),
            ],
            'effectiveDateTime' => $data['effective_date'] ?? now()->format('Y-m-d\TH:i:sP'),
            'valueQuantity' => [
                'value' => $data['value'],
                'unit' => $data['unit'] ?? '',
                'system' => $data['value_system'] ?? 'http://unitsofmeasure.org',
                'code' => $data['value_code'] ?? $data['unit'] ?? '',
            ],
        ];

        if (!empty($data['encounter_ss_id'])) {
            $resource['encounter'] = [
                'reference' => 'Encounter/' . $data['encounter_ss_id'],
            ];
        }

        $response = $this->client->post('Observation', $resource);

        if ($response && isset($response['id'])) {
            Log::info('Observation created on Satu Sehat', [
                'code' => $data['code'],
                'ss_id' => $response['id'],
            ]);
        }

        return $response;
    }

    public function syncObservation(MedicalRecord $mr): ?array
    {
        return $this->createVitalSign($mr);
    }

    public function getObservation(string $ssId): ?array
    {
        return $this->client->getResourceById('Observation', $ssId);
    }

    protected function buildVitalSignObservations(MedicalRecord $mr, array $vitalSigns): array
    {
        $patientSsId = $this->getPatientSsId($mr->patient);
        $encounterSsId = $this->getEncounterSsId($mr);
        $effectiveDate = ($mr->visit_date ?? now())->format('Y-m-d\TH:i:sP');

        $observations = [];

        $vitalSignMappings = [
            'systolic' => [
                'code' => '8480-6',
                'display' => 'Systolic blood pressure',
                'unit' => 'mm[Hg]',
                'value_code' => 'mm[Hg]',
            ],
            'diastolic' => [
                'code' => '8462-4',
                'display' => 'Diastolic blood pressure',
                'unit' => 'mm[Hg]',
                'value_code' => 'mm[Hg]',
            ],
            'heart_rate' => [
                'code' => '8867-4',
                'display' => 'Heart rate',
                'unit' => 'beats/minute',
                'value_code' => '/min',
            ],
            'respiratory_rate' => [
                'code' => '9279-1',
                'display' => 'Respiratory rate',
                'unit' => 'breaths/minute',
                'value_code' => '/min',
            ],
            'temperature' => [
                'code' => '8310-5',
                'display' => 'Body temperature',
                'unit' => 'C',
                'value_code' => 'Cel',
            ],
            'oxygen_saturation' => [
                'code' => '2708-6',
                'display' => 'Oxygen saturation',
                'unit' => '%',
                'value_code' => '%',
            ],
            'weight' => [
                'code' => '29463-7',
                'display' => 'Body weight',
                'unit' => 'kg',
                'value_code' => 'kg',
            ],
            'height' => [
                'code' => '8302-2',
                'display' => 'Body height',
                'unit' => 'cm',
                'value_code' => 'cm',
            ],
            'bmi' => [
                'code' => '39156-5',
                'display' => 'Body mass index (BMI)',
                'unit' => 'kg/m2',
                'value_code' => 'kg/m2',
            ],
        ];

        foreach ($vitalSignMappings as $key => $mapping) {
            if (!isset($vitalSigns[$key]) || $vitalSigns[$key] === '' || $vitalSigns[$key] === null) {
                continue;
            }

            $observation = [
                'resourceType' => 'Observation',
                'status' => 'final',
                'category' => [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                'code' => 'vital-signs',
                                'display' => 'Vital Signs',
                            ],
                        ],
                    ],
                ],
                'code' => [
                    'coding' => [
                        [
                            'system' => 'http://loinc.org',
                            'code' => $mapping['code'],
                            'display' => $mapping['display'],
                        ],
                    ],
                    'text' => $mapping['display'],
                ],
                'subject' => [
                    'reference' => 'Patient/' . ($patientSsId ?? $mr->patient->nik),
                    'display' => $mr->patient->name,
                ],
                'effectiveDateTime' => $effectiveDate,
                'valueQuantity' => [
                    'value' => (float) $vitalSigns[$key],
                    'unit' => $mapping['unit'],
                    'system' => 'http://unitsofmeasure.org',
                    'code' => $mapping['value_code'],
                ],
            ];

            if ($encounterSsId) {
                $observation['encounter'] = [
                    'reference' => 'Encounter/' . $encounterSsId,
                ];
            }

            $observations[] = $observation;
        }

        return $observations;
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

    protected function getEncounterSsId(MedicalRecord $mr): ?string
    {
        return SatusehatResource::where('model_type', get_class($mr))
            ->where('model_id', $mr->id)
            ->where('resource_type', 'Encounter')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function saveResourceReference(MedicalRecord $mr, string $resourceType, string $ssId, array $response, ?string $code = null): SatusehatResource
    {
        $query = SatusehatResource::where('model_type', get_class($mr))
            ->where('model_id', $mr->id)
            ->where('resource_type', $resourceType);

        if ($code) {
            $query->where('payload->code->coding->0->code', $code);
        }

        $ref = $query->first();

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

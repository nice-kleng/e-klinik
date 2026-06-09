<?php

namespace App\Services\SatuSehat;

use App\Models\MedicalRecord;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class ConditionService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createCondition(MedicalRecord $mr): ?array
    {
        $mr->loadMissing(['patient']);

        $diagnoses = $this->getDiagnoses($mr);
        if (empty($diagnoses)) {
            return null;
        }

        $lastResponse = null;

        foreach ($diagnoses as $index => $diagnosis) {
            $encounterRef = $this->getEncounterSsId($mr);

            $resource = [
                'resourceType' => 'Condition',
                'clinicalStatus' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                            'code' => 'active',
                            'display' => 'Active',
                        ],
                    ],
                ],
                'verificationStatus' => [
                    'coding' => [
                        [
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status',
                            'code' => 'confirmed',
                            'display' => 'Confirmed',
                        ],
                    ],
                ],
                'category' => [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                                'code' => 'encounter-diagnosis',
                                'display' => 'Encounter Diagnosis',
                            ],
                        ],
                    ],
                ],
                'code' => [
                    'coding' => [
                        [
                            'system' => 'http://hl7.org/fhir/sid/icd-10',
                            'code' => $diagnosis['code'],
                            'display' => $diagnosis['name'],
                        ],
                    ],
                ],
                'subject' => [
                    'reference' => 'Patient/' . ($this->getPatientSsId($mr->patient) ?? $mr->patient->nik),
                    'display' => $mr->patient->name,
                ],
                'encounter' => [
                    'reference' => 'Encounter/' . ($encounterRef ?? ''),
                ],
                'recordedDate' => ($mr->visit_date ?? now())->format('Y-m-d\TH:i:sP'),
                'notes' => [
                    [
                        'text' => $diagnosis['note'] ?? '',
                    ],
                ],
            ];

            $response = $this->client->post('Condition', $resource);

            if ($response && isset($response['id'])) {
                $diagnosisKey = $index === 0 ? 'diagnosis_primary' : 'diagnosis_secondary_' . $index;
                $this->saveResourceReference($mr, 'Condition', $response['id'], $response, $diagnosisKey);

                Log::info('Condition created on Satu Sehat', [
                    'medical_record_id' => $mr->id,
                    'code' => $diagnosis['code'],
                    'ss_id' => $response['id'],
                ]);
            }

            $lastResponse = $response;
        }

        return $lastResponse;
    }

    public function updateCondition(MedicalRecord $mr): ?array
    {
        $existingRefs = SatusehatResource::where('model_type', get_class($mr))
            ->where('model_id', $mr->id)
            ->where('resource_type', 'Condition')
            ->get();

        if ($existingRefs->isEmpty()) {
            return $this->createCondition($mr);
        }

        $lastResponse = null;

        foreach ($existingRefs as $ref) {
            $existing = $this->getCondition($ref->resource_id_ss);
            if (!$existing) {
                continue;
            }

            $existing['clinicalStatus']['coding'][0]['code'] = 'active';
            $existing['clinicalStatus']['coding'][0]['display'] = 'Active';

            $response = $this->client->put('Condition/' . $ref->resource_id_ss, $existing);

            if ($response && isset($response['id'])) {
                $ref->update([
                    'version' => $response['meta']['versionId'] ?? ($ref->version + 1),
                    'payload' => $response,
                    'sync_at' => now(),
                ]);
            }

            $lastResponse = $response;
        }

        return $lastResponse;
    }

    public function getCondition(string $ssId): ?array
    {
        return $this->client->getResourceById('Condition', $ssId);
    }

    protected function getDiagnoses(MedicalRecord $mr): array
    {
        $diagnoses = [];

        if (!empty($mr->diagnosis_primary)) {
            $diagnoses[] = [
                'code' => $mr->diagnosis_primary,
                'name' => $this->getIcd10Name($mr->diagnosis_primary),
                'note' => 'Primary diagnosis',
            ];
        }

        $secondary = $mr->diagnosis_secondary ?? [];
        if (is_string($secondary)) {
            $secondary = json_decode($secondary, true) ?? [];
        }

        foreach ($secondary as $idx => $diag) {
            if (is_string($diag)) {
                $diagnoses[] = [
                    'code' => $diag,
                    'name' => $this->getIcd10Name($diag),
                    'note' => 'Secondary diagnosis #' . ($idx + 1),
                ];
            } elseif (is_array($diag) && isset($diag['code'])) {
                $diagnoses[] = [
                    'code' => $diag['code'],
                    'name' => $diag['name'] ?? $this->getIcd10Name($diag['code']),
                    'note' => $diag['note'] ?? 'Secondary diagnosis #' . ($idx + 1),
                ];
            }
        }

        return $diagnoses;
    }

    protected function getIcd10Name(string $code): string
    {
        $diagnosis = \App\Models\Icd10Diagnosis::where('code', $code)->first();

        return $diagnosis?->name ?? $code;
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

    protected function saveResourceReference(MedicalRecord $mr, string $resourceType, string $ssId, array $response, ?string $diagnosisKey = null): SatusehatResource
    {
        $ref = SatusehatResource::where('model_type', get_class($mr))
            ->where('model_id', $mr->id)
            ->where('resource_type', $resourceType)
            ->where('payload->code->coding->0->code', $response['code']['coding'][0]['code'] ?? null)
            ->first();

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

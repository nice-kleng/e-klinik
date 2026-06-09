<?php

namespace App\Services\SatuSehat;

use App\Models\Prescription;
use App\Models\SatusehatResource;
use Illuminate\Support\Facades\Log;

class MedicationRequestService
{
    protected SatuSehatClient $client;

    public function __construct()
    {
        $this->client = app(SatuSehatClient::class);
    }

    public function createMedicationRequest(Prescription $prescription): ?array
    {
        $prescription->loadMissing(['patient', 'doctor', 'medicalRecord', 'items.medicine']);

        $encounterSsId = $this->getEncounterSsId($prescription->medicalRecord);
        $patientSsId = $this->getPatientSsId($prescription->patient);
        $practitionerSsId = $this->getPractitionerSsId($prescription->doctor);

        $lastResponse = null;

        foreach ($prescription->items as $item) {
            $resource = [
                'resourceType' => 'MedicationRequest',
                'status' => 'active',
                'intent' => 'order',
                'category' => [
                    [
                        'coding' => [
                            [
                                'system' => 'http://terminology.hl7.org/CodeSystem/medicationrequest-category',
                                'code' => 'outpatient',
                                'display' => 'Outpatient',
                            ],
                        ],
                    ],
                ],
                'medicationCodeableConcept' => [
                    'coding' => [
                        [
                            'system' => 'http://sys-ids.kemkes.go.id/kfa',
                            'code' => $item->medicine?->code ?? '',
                            'display' => $item->medicine?->name ?? '',
                        ],
                    ],
                    'text' => $item->medicine?->name ?? '',
                ],
                'subject' => [
                    'reference' => 'Patient/' . ($patientSsId ?? $prescription->patient->nik),
                    'display' => $prescription->patient->name,
                ],
                'encounter' => [
                    'reference' => 'Encounter/' . ($encounterSsId ?? ''),
                ],
                'authoredOn' => ($prescription->prescription_date ?? now())->format('Y-m-d'),
                'requester' => [
                    'reference' => 'Practitioner/' . ($practitionerSsId ?? ''),
                    'display' => $prescription->doctor->name,
                ],
                'dosageInstruction' => [
                    [
                        'text' => $this->buildDosageText($item),
                        'timing' => [
                            'repeat' => [
                                'frequency' => $item->dosage['frequency'] ?? 1,
                                'period' => 1,
                                'periodUnit' => 'd',
                            ],
                        ],
                        'doseAndRate' => [
                            [
                                'type' => [
                                    'coding' => [
                                        [
                                            'system' => 'http://terminology.hl7.org/CodeSystem/dose-rate-type',
                                            'code' => 'ordered',
                                            'display' => 'Ordered',
                                        ],
                                    ],
                                ],
                                'doseQuantity' => [
                                    'value' => (float) ($item->dosage['dose'] ?? $item->quantity),
                                    'unit' => $item->unit ?? '',
                                    'system' => 'http://unitsofmeasure.org',
                                    'code' => $item->unit ?? '',
                                ],
                            ],
                        ],
                    ],
                ],
                'quantity' => [
                    'value' => (float) $item->quantity,
                    'unit' => $item->unit ?? '',
                    'system' => 'http://unitsofmeasure.org',
                    'code' => $item->unit ?? '',
                ],
            ];

            $response = $this->client->post('MedicationRequest', $resource);

            if ($response && isset($response['id'])) {
                $this->saveResourceReference($prescription, 'MedicationRequest', $response['id'], $response, $item->id);

                Log::info('MedicationRequest created on Satu Sehat', [
                    'prescription_id' => $prescription->id,
                    'item_id' => $item->id,
                    'ss_id' => $response['id'],
                ]);
            }

            $lastResponse = $response;
        }

        return $lastResponse;
    }

    public function getMedicationRequest(string $ssId): ?array
    {
        return $this->client->getResourceById('MedicationRequest', $ssId);
    }

    protected function buildDosageText($item): string
    {
        $dosage = $item->dosage ?? [];
        $parts = [];

        if (!empty($dosage['dose'])) {
            $parts[] = $dosage['dose'] . ' ' . ($item->unit ?? '');
        }

        if (!empty($dosage['frequency'])) {
            $parts[] = $dosage['frequency'] . 'x sehari';
        }

        if (!empty($dosage['route'])) {
            $parts[] = $dosage['route'];
        }

        if (!empty($dosage['note'])) {
            $parts[] = '(' . $dosage['note'] . ')';
        }

        return implode(' ', $parts) ?: ($item->quantity . ' ' . ($item->unit ?? ''));
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

    protected function getEncounterSsId($medicalRecord): ?string
    {
        if (!$medicalRecord) {
            return null;
        }

        return SatusehatResource::where('model_type', get_class($medicalRecord))
            ->where('model_id', $medicalRecord->id)
            ->where('resource_type', 'Encounter')
            ->where('status', 'synced')
            ->value('resource_id_ss');
    }

    protected function saveResourceReference(Prescription $prescription, string $resourceType, string $ssId, array $response, ?int $itemId = null): SatusehatResource
    {
        $query = SatusehatResource::where('model_type', get_class($prescription))
            ->where('model_id', $prescription->id)
            ->where('resource_type', $resourceType);

        if ($itemId) {
            $query->where('resource_id_ss', $ssId);
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
            'model_type' => get_class($prescription),
            'model_id' => $prescription->id,
            'resource_type' => $resourceType,
            'resource_id_ss' => $ssId,
            'version' => $response['meta']['versionId'] ?? 1,
            'payload' => $response,
            'status' => 'synced',
            'sync_at' => now(),
        ]);
    }
}

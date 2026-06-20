<?php

namespace App\Services;

use App\Models\Icd9CmDiagnosis;
use App\Models\Icd10Diagnosis;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordDiagnosis;
use App\Models\MedicalRecordProcedure;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\BPJS\VClaimService;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\ObservationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalRecordService
{
    protected EncounterService $encounterService;
    protected ConditionService $conditionService;
    protected ObservationService $observationService;
    protected VClaimService $vClaimService;

    public function __construct(
        ?EncounterService $encounterService = null,
        ?ConditionService $conditionService = null,
        ?ObservationService $observationService = null,
        ?VClaimService $vClaimService = null
    ) {
        $this->encounterService = $encounterService ?? app(EncounterService::class);
        $this->conditionService = $conditionService ?? app(ConditionService::class);
        $this->observationService = $observationService ?? app(ObservationService::class);
        $this->vClaimService = $vClaimService ?? app(VClaimService::class);
    }

    public function createRecord(array $data): MedicalRecord
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            if (isset($data['visit_date']) && is_string($data['visit_date'])) {
                $data['visit_date'] = Carbon::parse($data['visit_date'])->toDateString();
            }

            if (isset($data['follow_up_date']) && is_string($data['follow_up_date'])) {
                $data['follow_up_date'] = Carbon::parse($data['follow_up_date'])->toDateString();
            }

            if (isset($data['vital_signs']) && is_array($data['vital_signs'])) {
                $data['vital_signs'] = $this->normalizeVitalSigns($data['vital_signs']);
            }

            // Normalize specialist_data: decode JSON strings to arrays (e.g. odontogram)
            if (isset($data['specialist_data']) && is_array($data['specialist_data'])) {
                $data['specialist_data'] = $this->normalizeSpecialistData($data['specialist_data']);
            }

            // Auto-set registration_id from queue if not provided
            if (empty($data['registration_id']) && !empty($data['queue_id'])) {
                $queue = \App\Models\Queue::find($data['queue_id']);
                if ($queue) {
                    $data['registration_id'] = $queue->registration_id;
                }
            }

            $record = MedicalRecord::create($data);

            // Legacy string columns for backward compatibility
            if (!empty($data['diagnosis_primary'])) {
                $record->update(['diagnosis_primary' => $data['diagnosis_primary']]);
            }

            if (!empty($data['diagnosis_secondary']) && is_array($data['diagnosis_secondary'])) {
                $record->update(['diagnosis_secondary' => $data['diagnosis_secondary']]);
            }

            // Save to normalized pivot tables
            if (!empty($data['diagnosis_primary_id'])) {
                $record->diagnoses()->create([
                    'icd10_diagnosis_id' => $data['diagnosis_primary_id'],
                    'type' => 'primary',
                    'order' => 0,
                ]);
            }

            if (!empty($data['diagnosis_secondary_ids']) && is_array($data['diagnosis_secondary_ids'])) {
                foreach ($data['diagnosis_secondary_ids'] as $order => $id) {
                    $record->diagnoses()->create([
                        'icd10_diagnosis_id' => $id,
                        'type' => 'secondary',
                        'order' => $order,
                    ]);
                }
            }

            if (!empty($data['diagnosis_differential_ids']) && is_array($data['diagnosis_differential_ids'])) {
                foreach ($data['diagnosis_differential_ids'] as $order => $id) {
                    $record->diagnoses()->create([
                        'icd10_diagnosis_id' => $id,
                        'type' => 'differential',
                        'order' => $order,
                    ]);
                }
            }

            if (!empty($data['procedure_ids']) && is_array($data['procedure_ids'])) {
                foreach ($data['procedure_ids'] as $order => $id) {
                    $notes = $data['procedure_notes'][$order] ?? null;
                    $record->procedures()->create([
                        'icd9_cm_diagnosis_id' => $id,
                        'notes' => $notes,
                        'order' => $order,
                    ]);
                }
            }

            return $record;
        });
    }

    public function updateRecord(MedicalRecord $mr, array $data): MedicalRecord
    {
        return DB::transaction(function () use ($mr, $data) {
            if (isset($data['vital_signs']) && is_array($data['vital_signs'])) {
                $data['vital_signs'] = $this->normalizeVitalSigns($data['vital_signs']);
            }

            if (isset($data['visit_date']) && is_string($data['visit_date'])) {
                $data['visit_date'] = Carbon::parse($data['visit_date'])->toDateString();
            }

            if (isset($data['follow_up_date']) && is_string($data['follow_up_date'])) {
                $data['follow_up_date'] = Carbon::parse($data['follow_up_date'])->toDateString();
            }

            // Normalize specialist_data: decode JSON strings to arrays
            if (isset($data['specialist_data']) && is_array($data['specialist_data'])) {
                $data['specialist_data'] = $this->normalizeSpecialistData($data['specialist_data']);
            }

            $mr->update($data);

            // Sync normalized pivot tables
            if (isset($data['diagnosis_primary_id'])) {
                $mr->diagnoses()->where('type', 'primary')->delete();
                $mr->diagnoses()->create([
                    'icd10_diagnosis_id' => $data['diagnosis_primary_id'],
                    'type' => 'primary',
                    'order' => 0,
                ]);
            }

            if (isset($data['diagnosis_secondary_ids']) && is_array($data['diagnosis_secondary_ids'])) {
                $mr->diagnoses()->where('type', 'secondary')->delete();
                foreach ($data['diagnosis_secondary_ids'] as $order => $id) {
                    $mr->diagnoses()->create([
                        'icd10_diagnosis_id' => $id,
                        'type' => 'secondary',
                        'order' => $order,
                    ]);
                }
            }

            if (isset($data['diagnosis_differential_ids']) && is_array($data['diagnosis_differential_ids'])) {
                $mr->diagnoses()->where('type', 'differential')->delete();
                foreach ($data['diagnosis_differential_ids'] as $order => $id) {
                    $mr->diagnoses()->create([
                        'icd10_diagnosis_id' => $id,
                        'type' => 'differential',
                        'order' => $order,
                    ]);
                }
            }

            if (isset($data['procedure_ids']) && is_array($data['procedure_ids'])) {
                $mr->procedures()->delete();
                foreach ($data['procedure_ids'] as $order => $id) {
                    $notes = $data['procedure_notes'][$order] ?? null;
                    $mr->procedures()->create([
                        'icd9_cm_diagnosis_id' => $id,
                        'notes' => $notes,
                        'order' => $order,
                    ]);
                }
            }

            return $mr->fresh();
        });
    }

    public function getPatientRecords(Patient $patient): Collection
    {
        return MedicalRecord::with(['doctor', 'polyclinic', 'prescriptions.items.medicine'])
            ->where('patient_id', $patient->id)
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRecordWithRelations(MedicalRecord $mr): MedicalRecord
    {
        return $mr->load([
            'patient',
            'doctor',
            'polyclinic',
            'queue',
            'prescriptions.items.medicine',
            'creator',
        ]);
    }

    public function createPrescription(MedicalRecord $mr, array $data): Prescription
    {
        return DB::transaction(function () use ($mr, $data) {
            $prescriptionNumber = $this->generatePrescriptionNumber();

            $prescription = Prescription::create([
                'medical_record_id' => $mr->id,
                'patient_id' => $mr->patient_id,
                'doctor_id' => $mr->doctor_id,
                'prescription_number' => $data['prescription_number'] ?? $prescriptionNumber,
                'prescription_date' => $data['prescription_date'] ?? now()->toDateString(),
                'status' => $data['status'] ?? 'active',
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'dosage' => $item['dosage'] ?? null,
                        'subtotal' => $item['subtotal'] ?? 0,
                    ]);
                }
            }

            return $prescription->load('items.medicine');
        });
    }

    public function addDiagnosis(MedicalRecord $mr, string $icd10Code, string $type): MedicalRecord
    {
        $diagnosis = Icd10Diagnosis::where('code', $icd10Code)->first();

        if (!$diagnosis) {
            throw new \RuntimeException("ICD-10 code not found: {$icd10Code}");
        }

        return DB::transaction(function () use ($mr, $icd10Code, $type) {
            if ($type === 'primary') {
                $mr->update(['diagnosis_primary' => $icd10Code]);
            } elseif ($type === 'secondary') {
                $existing = $mr->diagnosis_secondary ?? [];
                if (!in_array($icd10Code, $existing)) {
                    $existing[] = $icd10Code;
                    $mr->update(['diagnosis_secondary' => $existing]);
                }
            } else {
                throw new \InvalidArgumentException("Invalid diagnosis type: {$type}. Must be 'primary' or 'secondary'.");
            }

            return $mr->fresh();
        });
    }

    public function getVitalSigns(MedicalRecord $mr): array
    {
        $signs = $mr->vital_signs ?? [];

        $normalRanges = [
            'systolic' => ['label' => 'Systolic (mmHg)', 'min' => 90, 'max' => 140],
            'diastolic' => ['label' => 'Diastolic (mmHg)', 'min' => 60, 'max' => 90],
            'heart_rate' => ['label' => 'Heart Rate (bpm)', 'min' => 60, 'max' => 100],
            'respiratory_rate' => ['label' => 'Respiratory Rate (/min)', 'min' => 12, 'max' => 24],
            'temperature' => ['label' => 'Temperature (°C)', 'min' => 36.0, 'max' => 37.5],
            'oxygen_saturation' => ['label' => 'O2 Saturation (%)', 'min' => 95, 'max' => 100],
            'weight' => ['label' => 'Weight (kg)', 'min' => null, 'max' => null],
            'height' => ['label' => 'Height (cm)', 'min' => null, 'max' => null],
            'bmi' => ['label' => 'BMI', 'min' => 18.5, 'max' => 24.9],
            'gcs' => ['label' => 'GCS', 'min' => 3, 'max' => 15],
            'blood_glucose' => ['label' => 'Blood Glucose (mg/dL)', 'min' => 70, 'max' => 180],
        ];

        $parsed = [];
        foreach ($normalRanges as $key => $range) {
            if (isset($signs[$key]) && $signs[$key] !== null && $signs[$key] !== '') {
                $value = $signs[$key];
                $status = 'normal';
                if ($range['min'] !== null && $value < $range['min']) {
                    $status = 'low';
                } elseif ($range['max'] !== null && $value > $range['max']) {
                    $status = 'high';
                }

                $parsed[$key] = [
                    'value' => $value,
                    'label' => $range['label'],
                    'status' => $status,
                    'min' => $range['min'],
                    'max' => $range['max'],
                ];
            }
        }

        return $parsed;
    }

    public function submitToSatusehat(MedicalRecord $mr): array
    {
        $results = [];

        try {
            $encounterResult = $this->encounterService->createEncounter($mr);
            $results['encounter'] = $encounterResult;
        } catch (\Exception $e) {
            Log::error('Failed to submit encounter to Satu Sehat', [
                'medical_record_id' => $mr->id,
                'error' => $e->getMessage(),
            ]);
            $results['encounter'] = ['error' => $e->getMessage()];
        }

        try {
            $conditionResult = $this->conditionService->createCondition($mr);
            $results['condition'] = $conditionResult;
        } catch (\Exception $e) {
            Log::error('Failed to submit condition to Satu Sehat', [
                'medical_record_id' => $mr->id,
                'error' => $e->getMessage(),
            ]);
            $results['condition'] = ['error' => $e->getMessage()];
        }

        if ($mr->vital_signs) {
            try {
                $observationResult = $this->observationService->createObservations($mr);
                $results['observations'] = $observationResult;
            } catch (\Exception $e) {
                Log::error('Failed to submit observations to Satu Sehat', [
                    'medical_record_id' => $mr->id,
                    'error' => $e->getMessage(),
                ]);
                $results['observations'] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    public function submitToBpjs(MedicalRecord $mr): ?array
    {
        if ($mr->patient->insurance_type !== 'BPJS') {
            return null;
        }

        $bpjsPatient = $mr->patient->bpjsPatient;
        if (!$bpjsPatient || !$bpjsPatient->no_kartu) {
            Log::warning('Cannot submit claim to BPJS: patient has no card number', [
                'medical_record_id' => $mr->id,
            ]);
            return null;
        }

        try {
            $claimData = [
                'noKartu' => $bpjsPatient->no_kartu,
                'tglPelayanan' => $mr->visit_date->format('Y-m-d'),
                'diagnosa' => $mr->diagnosis_primary,
                'poli' => $mr->polyclinic?->code ?? '',
                'noSep' => $bpjsPatient->no_sep ?? '',
            ];

            $response = $this->vClaimService->submitClaim($claimData);

            Log::info('BPJS claim submitted', [
                'medical_record_id' => $mr->id,
                'response' => $response,
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Failed to submit BPJS claim', [
                'medical_record_id' => $mr->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function generatePrescriptionNumber(): string
    {
        $datePrefix = 'RX-' . now()->format('Ymd') . '-';

        $lastPrescription = Prescription::where('prescription_number', 'like', "{$datePrefix}%")
            ->orderBy('prescription_number', 'desc')
            ->first();

        if ($lastPrescription && preg_match('/-(\d{4})$/', $lastPrescription->prescription_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $datePrefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function normalizeVitalSigns(array $signs): array
    {
        $numericKeys = [
            'systolic', 'diastolic', 'heart_rate', 'respiratory_rate',
            'oxygen_saturation', 'weight', 'height', 'bmi', 'gcs',
            'blood_glucose',
        ];

        foreach ($numericKeys as $key) {
            if (isset($signs[$key])) {
                $signs[$key] = is_numeric($signs[$key]) ? (float) $signs[$key] : null;
            }
        }

        if (isset($signs['temperature']) && $signs['temperature'] !== null) {
            $signs['temperature'] = is_numeric($signs['temperature']) ? (float) $signs['temperature'] : null;
        }

        if (isset($signs['systolic'], $signs['weight'], $signs['height']) && $signs['height'] > 0) {
            if (empty($signs['bmi'])) {
                $heightInM = $signs['height'] / 100;
                $signs['bmi'] = round($signs['weight'] / ($heightInM * $heightInM), 1);
            }
        }

        return $signs;
    }

    protected function normalizeSpecialistData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value) && str_starts_with($value, '{') && json_decode($value) !== null) {
                $data[$key] = json_decode($value, true);
            } elseif (is_array($value)) {
                $data[$key] = $this->normalizeSpecialistData($value);
            }
        }
        return $data;
    }
}

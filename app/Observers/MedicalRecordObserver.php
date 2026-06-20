<?php

namespace App\Observers;

use App\Models\MedicalRecord;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\ObservationService;
use Illuminate\Support\Facades\Log;

class MedicalRecordObserver
{
    protected array $trackedFields = [
        'subjective_complaint', 'anamnesis', 'past_history', 'medication_history',
        'objective_finding', 'physical_exam', 'vital_signs',
        'assessment', 'differential_diagnosis',
        'plan', 'notes', 'follow_up_date', 'diagnosis_primary', 'diagnosis_secondary',
        'specialist_data',
    ];

    public function __construct(
        protected EncounterService $encounterService,
        protected ConditionService $conditionService,
        protected ObservationService $observationService,
    ) {}

    public function created(MedicalRecord $record): void
    {
        $this->logAudit($record, 'created');

        try {
            $this->encounterService->syncEncounter($record);
            $this->conditionService->syncCondition($record);
            $this->observationService->syncObservation($record);
        } catch (\Exception $e) {
            Log::error("Failed to sync medical record {$record->id} to Satu Sehat: " . $e->getMessage());
        }
    }

    public function updated(MedicalRecord $record): void
    {
        $this->logAudit($record, 'updated');

        if ($record->wasChanged(['diagnosis_primary', 'diagnosis_secondary', 'vital_signs'])) {
            try {
                $this->encounterService->syncEncounter($record);
                $this->conditionService->syncCondition($record);
            } catch (\Exception $e) {
                Log::error("Failed to sync medical record {$record->id} update to Satu Sehat: " . $e->getMessage());
            }
        }
    }

    public function deleted(MedicalRecord $record): void
    {
        $this->logAudit($record, 'deleted');
    }

    public function restored(MedicalRecord $record): void
    {
        $this->logAudit($record, 'restored');
    }

    protected function logAudit(MedicalRecord $record, string $action): void
    {
        try {
            $user = auth()->user();

            if ($action === 'created') {
                $record->audits()->create([
                    'user_id' => $user?->id ?? $record->created_by,
                    'action' => 'created',
                    'field_name' => null,
                    'old_value' => null,
                    'new_value' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return;
            }

            if ($action === 'deleted' || $action === 'restored') {
                $record->audits()->create([
                    'user_id' => $user?->id ?? 1,
                    'action' => $action,
                    'field_name' => null,
                    'old_value' => null,
                    'new_value' => null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                return;
            }

            $changed = $record->getDirty();

            foreach ($this->trackedFields as $field) {
                if (array_key_exists($field, $changed)) {
                    $record->audits()->create([
                        'user_id' => $user?->id ?? 1,
                        'action' => 'updated',
                        'field_name' => $field,
                        'old_value' => is_array($record->getOriginal($field))
                            ? json_encode($record->getOriginal($field))
                            : $record->getOriginal($field),
                        'new_value' => is_array($record->$field)
                            ? json_encode($record->$field)
                            : $record->$field,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning("Failed to log audit for medical record {$record->id}: " . $e->getMessage());
        }
    }
}

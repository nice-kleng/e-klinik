<?php

namespace App\Observers;

use App\Models\MedicalRecord;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\ObservationService;
use Illuminate\Support\Facades\Log;

class MedicalRecordObserver
{
    public function __construct(
        protected EncounterService $encounterService,
        protected ConditionService $conditionService,
        protected ObservationService $observationService,
    ) {}

    public function created(MedicalRecord $record): void
    {
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
        if ($record->wasChanged(['diagnosis_primary', 'diagnosis_secondary', 'vital_signs'])) {
            try {
                $this->encounterService->syncEncounter($record);
                $this->conditionService->syncCondition($record);
            } catch (\Exception $e) {
                Log::error("Failed to sync medical record {$record->id} update to Satu Sehat: " . $e->getMessage());
            }
        }
    }
}

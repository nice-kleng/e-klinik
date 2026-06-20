<?php

namespace App\Observers;

use App\Models\MedicalRecordAudit;
use App\Models\Prescription;
use App\Services\SatuSehat\MedicationRequestService;
use Illuminate\Support\Facades\Log;

class PrescriptionObserver
{
    public function __construct(
        protected MedicationRequestService $medicationRequestService,
    ) {}

    public function created(Prescription $prescription): void
    {
        try {
            $this->medicationRequestService->syncMedicationRequest($prescription);
        } catch (\Exception $e) {
            Log::error("Failed to sync prescription {$prescription->id} to Satu Sehat: " . $e->getMessage());
        }

        try {
            if ($prescription->medical_record_id) {
                MedicalRecordAudit::create([
                    'medical_record_id' => $prescription->medical_record_id,
                    'user_id' => $prescription->created_by ?? auth()->id(),
                    'action' => 'created',
                    'field_name' => 'prescription.created',
                    'new_value' => 'Resep #' . $prescription->id . ' (' . ($prescription->items_count ?? $prescription->items()->count()) . ' item)',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to log prescription audit: " . $e->getMessage());
        }
    }

    public function updated(Prescription $prescription): void
    {
        try {
            if ($prescription->medical_record_id) {
                MedicalRecordAudit::create([
                    'medical_record_id' => $prescription->medical_record_id,
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'field_name' => 'prescription.updated',
                    'new_value' => 'Resep #' . $prescription->id . ' diperbarui',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to log prescription audit: " . $e->getMessage());
        }
    }
}

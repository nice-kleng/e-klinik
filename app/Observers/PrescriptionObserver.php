<?php

namespace App\Observers;

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
    }
}

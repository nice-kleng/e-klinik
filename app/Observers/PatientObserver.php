<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\SatusehatResource;
use App\Services\SatuSehat\PatientService;
use Illuminate\Support\Facades\Log;

class PatientObserver
{
    public function __construct(
        protected PatientService $satusehatPatientService,
    ) {}

    public function created(Patient $patient): void
    {
        try {
            $this->satusehatPatientService->syncPatient($patient);
        } catch (\Exception $e) {
            Log::error("Failed to sync patient {$patient->id} to Satu Sehat: " . $e->getMessage());
        }
    }

    public function updated(Patient $patient): void
    {
        if ($patient->wasChanged(['name', 'nik', 'address', 'phone', 'birth_date', 'gender'])) {
            try {
                $this->satusehatPatientService->syncPatient($patient);
            } catch (\Exception $e) {
                Log::error("Failed to sync patient {$patient->id} update to Satu Sehat: " . $e->getMessage());
            }
        }
    }

    public function deleted(Patient $patient): void
    {
        SatusehatResource::where('model_type', Patient::class)
            ->where('model_id', $patient->id)
            ->update(['status' => 'deleted']);
    }
}

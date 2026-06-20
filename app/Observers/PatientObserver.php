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

        Log::info('Patient created', [
            'patient_id' => $patient->id,
            'name' => $patient->name,
            'no_rm' => $patient->no_rm,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }

    public function updated(Patient $patient): void
    {
        if ($patient->wasChanged(['name', 'nik', 'address', 'phone', 'birth_date', 'gender'])) {
            try {
                $this->satusehatPatientService->syncPatient($patient);
            } catch (\Exception $e) {
                Log::error("Failed to sync patient {$patient->id} update to Satu Sehat: " . $e->getMessage());
            }

            $changed = $patient->getChanges();
            unset($changed['updated_at']);

            Log::info('Patient updated', [
                'patient_id' => $patient->id,
                'name' => $patient->name,
                'changes' => array_keys($changed),
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
            ]);
        }
    }

    public function deleted(Patient $patient): void
    {
        SatusehatResource::where('model_type', Patient::class)
            ->where('model_id', $patient->id)
            ->update(['status' => 'deleted']);

        Log::info('Patient deleted', [
            'patient_id' => $patient->id,
            'name' => $patient->name,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
        ]);
    }
}

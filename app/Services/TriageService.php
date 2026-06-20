<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Triage;
use Illuminate\Support\Facades\DB;

class TriageService
{
    public function create(array $data): Triage
    {
        if (Triage::where('registration_id', $data['registration_id'])->exists()) {
            throw new \RuntimeException('Pasien sudah memiliki data triage');
        }

        return DB::transaction(function () use ($data) {
            $triage = Triage::create($data);

            Registration::where('id', $data['registration_id'])
                ->where('service_status', 'registered')
                ->update(['service_status' => 'triage']);

            return $triage;
        });
    }

    public function update(Triage $triage, array $data): Triage
    {
        $triage->update($data);
        return $triage->fresh();
    }
}

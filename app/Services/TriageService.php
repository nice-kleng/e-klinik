<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Triage;
use Illuminate\Support\Facades\DB;

class TriageService
{
    public function create(array $data): Triage
    {
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

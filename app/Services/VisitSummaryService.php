<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\VisitSummary;
use Illuminate\Support\Facades\DB;

class VisitSummaryService
{
    public function create(Registration $registration, array $data): VisitSummary
    {
        return DB::transaction(function () use ($registration, $data) {
            $data['registration_id'] = $registration->id;
            $data['created_by'] = $data['created_by'] ?? auth()->id();
            $summary = VisitSummary::create($data);

            $registration->update(['service_status' => 'completed']);

            return $summary;
        });
    }

    public function update(VisitSummary $summary, array $data): VisitSummary
    {
        return DB::transaction(function () use ($summary, $data) {
            $summary->update($data);
            return $summary->fresh();
        });
    }
}

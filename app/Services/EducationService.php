<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\PatientEducation;
use Illuminate\Support\Facades\DB;

class EducationService
{
    public function create(MedicalRecord $mr, array $data): PatientEducation
    {
        return DB::transaction(function () use ($mr, $data) {
            $data['medical_record_id'] = $mr->id;
            return PatientEducation::create($data);
        });
    }

    public function update(PatientEducation $education, array $data): PatientEducation
    {
        return DB::transaction(function () use ($education, $data) {
            $education->update($data);
            return $education->fresh();
        });
    }
}

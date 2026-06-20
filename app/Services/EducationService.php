<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\PatientEducation;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class EducationService
{
    public function create(MedicalRecord $mr, array $data): PatientEducation
    {
        return DB::transaction(function () use ($mr, $data) {
            $data['medical_record_id'] = $mr->id;
            $education = PatientEducation::create($data);

            $reg = $mr->registration;
            if ($reg && $reg->service_status === 'in_consultation') {
                $reg->update(['service_status' => 'education']);
            }

            return $education;
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

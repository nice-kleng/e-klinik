<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordDiagnosis extends Model
{
    protected $fillable = [
        'medical_record_id',
        'icd10_diagnosis_id',
        'type',
        'notes',
        'order',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function icd10Diagnosis(): BelongsTo
    {
        return $this->belongsTo(Icd10Diagnosis::class, 'icd10_diagnosis_id');
    }
}

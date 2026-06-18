<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordProcedure extends Model
{
    protected $fillable = [
        'medical_record_id',
        'icd9_cm_diagnosis_id',
        'notes',
        'order',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function icd9CmDiagnosis(): BelongsTo
    {
        return $this->belongsTo(Icd9CmDiagnosis::class, 'icd9_cm_diagnosis_id');
    }
}

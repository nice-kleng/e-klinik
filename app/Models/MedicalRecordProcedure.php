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
        'operator_id',
        'performed_at',
        'result',
        'status',
        'informed_consent',
        'informed_consent_file',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'informed_consent' => 'boolean',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function icd9CmDiagnosis(): BelongsTo
    {
        return $this->belongsTo(Icd9CmDiagnosis::class, 'icd9_cm_diagnosis_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}

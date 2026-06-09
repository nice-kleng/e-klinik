<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsClaim extends Model
{
    protected $fillable = [
        'medical_record_id',
        'patient_id',
        'sep_number',
        'claim_type',
        'treatment_type',
        'admission_date',
        'discharge_date',
        'diagnosis_code',
        'procedure_code',
        'tariff',
        'status',
        'response',
        'submitted_at',
        'verified_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'admission_date' => 'date',
            'discharge_date' => 'date',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}

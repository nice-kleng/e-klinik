<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformedConsent extends Model
{
    use HasCreatedBy, SoftDeletes;

    protected $fillable = [
        'registration_id',
        'patient_id',
        'medical_record_id',
        'consent_type',
        'procedure_name',
        'procedure_icd9_id',
        'diagnosis',
        'purpose',
        'risks',
        'benefits',
        'alternatives',
        'doctor_recommendation',
        'patient_name',
        'patient_agreed',
        'patient_signed_at',
        'patient_signature_hash',
        'witness_name',
        'status',
        'signature_hash',
        'signed_by',
        'signed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'patient_agreed' => 'boolean',
            'patient_signed_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function procedureIcd9(): BelongsTo
    {
        return $this->belongsTo(Icd9CmDiagnosis::class, 'procedure_icd9_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function procedures()
    {
        return $this->hasMany(MedicalRecordProcedure::class, 'informed_consent_id');
    }
}

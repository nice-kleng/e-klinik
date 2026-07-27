<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecordProcedure extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'medical_record_id',
        'icd9_cm_diagnosis_id',
        'notes',
        'fee',
        'order',
        'operator_id',
        'performed_at',
        'result',
        'status',
        'informed_consent',
        'informed_consent_file',
        'informed_consent_id',
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

    public function informedConsent(): BelongsTo
    {
        return $this->belongsTo(InformedConsent::class, 'informed_consent_id');
    }
}

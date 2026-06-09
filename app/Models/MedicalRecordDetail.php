<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordDetail extends Model
{
    protected $fillable = [
        'medical_record_id',
        'type',
        'icd10_code',
        'icd10_name',
        'icd9_code',
        'description',
        'notes',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }
}

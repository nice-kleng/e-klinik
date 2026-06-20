<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalRecordAudit extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'medical_record_id',
        'user_id',
        'action',
        'field_name',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class LabRequest extends Model
{
    use HasCreatedBy;
    protected $fillable = [
        'medical_record_id', 'patient_id', 'doctor_id',
        'request_number', 'notes', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function registration(): HasOneThrough
    {
        return $this->hasOneThrough(
            Registration::class,
            MedicalRecord::class,
            'id',
            'id',
            'medical_record_id',
            'registration_id'
        );
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabRequestItem::class);
    }
}

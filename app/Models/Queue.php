<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Queue extends Model
{
    use HasCreatedBy, Filterable, HasFactory;
    protected $fillable = [
        'patient_id',
        'polyclinic_id',
        'doctor_id',
        'queue_number',
        'queue_date',
        'status',
        'estimated_wait_time',
        'check_in_at',
        'called_at',
        'completed_at',
        'service_type',
        'bpjs_antrian_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'check_in_at' => 'datetime',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'queue_id');
    }

    public function bpjsSep(): HasOne
    {
        return $this->hasOne(BpjsSep::class, 'queue_id');
    }

    public function bpjsAntrean(): HasMany
    {
        return $this->hasMany(BpjsAntrean::class, 'queue_id');
    }
}

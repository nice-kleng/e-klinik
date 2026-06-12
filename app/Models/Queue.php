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
        'registration_id',
        'polyclinic_id',
        'queue_sequence',
        'queue_number',
        'queue_date',
        'source',
        'status',
        'check_in_at',
        'confirmed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'check_in_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'registration_id', 'id')
            ->whereRaw('1 = 0'); // fallback — use registration()->patient() instead
    }

    public function getPatientNameAttribute(): ?string
    {
        return $this->registration?->patient?->name;
    }

    public function getDoctorNameAttribute(): ?string
    {
        return $this->registration?->doctor?->name;
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'queue_id');
    }

    public function queueCalls(): HasMany
    {
        return $this->hasMany(QueueCall::class, 'queue_id');
    }

    public function queueMilestones(): HasMany
    {
        return $this->hasMany(QueueMilestone::class, 'queue_id');
    }
}

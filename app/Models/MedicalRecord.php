<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasCreatedBy, HasFactory, SoftDeletes, Filterable;

    protected $fillable = [
        'registration_id',
        'patient_id',
        'doctor_id',
        'polyclinic_id',
        'queue_id',
        'visit_date',
        'visit_type',
        'subjective_complaint',
        'objective_finding',
        'assessment',
        'plan',
        'diagnosis_primary',
        'diagnosis_secondary',
        'anamnesis',
        'physical_exam',
        'vital_signs',
        'notes',
        'follow_up_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'diagnosis_secondary' => 'array',
            'vital_signs' => 'array',
            'follow_up_date' => 'date',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class, 'polyclinic_id');
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class, 'medical_record_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(MedicalRecordDetail::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }
}

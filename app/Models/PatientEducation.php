<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientEducation extends Model
{
    protected $fillable = [
        'medical_record_id',
        'diagnosis_explained',
        'medication_instructions',
        'diet_instructions',
        'activity_instructions',
        'follow_up_plan',
        'educator_id',
        'education_date',
    ];

    protected function casts(): array
    {
        return [
            'education_date' => 'date',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function educator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'educator_id');
    }
}

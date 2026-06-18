<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Triage extends Model
{
    use HasFactory;

    protected $table = 'triage';

    protected $fillable = [
        'registration_id',
        'systolic', 'diastolic', 'heart_rate', 'respiratory_rate',
        'temperature', 'oxygen_saturation', 'weight', 'height', 'bmi',
        'gcs', 'blood_glucose',
        'chief_complaint', 'pain_scale', 'allergy_notes',
        'fall_risk', 'nutrition_status', 'smoking_status', 'pregnancy_status',
        'triage_by', 'triage_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'triage_at' => 'datetime',
            'fall_risk' => 'boolean',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function triageBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triage_by');
    }
}

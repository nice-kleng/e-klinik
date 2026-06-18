<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitSummary extends Model
{
    protected $fillable = [
        'registration_id',
        'final_diagnosis',
        'discharge_status',
        'follow_up_plan',
        'referral_notes',
        'referral_to',
        'sick_leave_days',
        'sick_leave_from',
        'sick_leave_to',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sick_leave_from' => 'date',
            'sick_leave_to' => 'date',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabQueue extends Model
{
    use HasCreatedBy, HasFactory;

    protected $fillable = [
        'registration_id',
        'lab_request_id',
        'queue_number',
        'queue_date',
        'queue_sequence',
        'status',
        'called_by',
        'called_at',
        'completed_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function calledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

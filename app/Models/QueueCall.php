<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'polyclinic_id',
        'called_by',
        'call_sequence',
        'called_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'called_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function polyclinic(): BelongsTo
    {
        return $this->belongsTo(Polyclinic::class);
    }

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'called_by');
    }
}

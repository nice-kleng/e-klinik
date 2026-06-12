<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id',
        'task_id',
        'task_name',
        'task_time',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'task_time' => 'datetime',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

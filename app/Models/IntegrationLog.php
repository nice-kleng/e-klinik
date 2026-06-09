<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationLog extends Model
{
    use HasCreatedBy;
    const UPDATED_AT = null;

    protected $fillable = [
        'integration_type',
        'endpoint',
        'method',
        'request_body',
        'response_body',
        'status_code',
        'status',
        'error_message',
        'duration_ms',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'request_body' => 'array',
            'response_body' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

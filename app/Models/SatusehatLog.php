<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatusehatLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'resource_type',
        'resource_id',
        'action',
        'request',
        'response',
        'status',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'request' => 'array',
            'response' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}

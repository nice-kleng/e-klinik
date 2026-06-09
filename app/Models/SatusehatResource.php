<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SatusehatResource extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'resource_type',
        'resource_id_ss',
        'version',
        'payload',
        'status',
        'sync_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sync_at' => 'datetime',
        ];
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}

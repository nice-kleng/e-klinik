<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsAntrean extends Model
{
    protected $table = 'bpjs_antrean';

    protected $fillable = [
        'queue_id',
        'patient_id',
        'no_kartu',
        'no_antrean',
        'kode_poli',
        'kode_dokter',
        'nomor_sep',
        'status',
        'response',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}

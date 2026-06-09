<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsReferral extends Model
{
    protected $fillable = [
        'patient_id',
        'no_kunjungan',
        'no_sep',
        'tgl_kunjungan',
        'ppk_dirujuk',
        'diagnose',
        'tipe_referensi',
        'status',
        'response',
    ];

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'tgl_kunjungan' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}

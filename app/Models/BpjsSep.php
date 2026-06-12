<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsSep extends Model
{
    use HasCreatedBy, HasFactory;
    protected $fillable = [
        'patient_id',
        'queue_id',
        'registration_id',
        'no_sep',
        'no_kartu',
        'tgl_pelayanan',
        'kode_poli',
        'kode_dokter',
        'diagnosa',
        'no_rujukan',
        'catatan',
        'status',
        'response_raw',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_pelayanan' => 'date',
            'response_raw' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class, 'queue_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

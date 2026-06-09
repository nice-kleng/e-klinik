<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsPatient extends Model
{
    protected $fillable = [
        'patient_id',
        'no_kartu',
        'nama',
        'tgl_lahir',
        'jk',
        'no_ktp',
        'alamat',
        'hak_kelas',
        'jenis_peserta',
        'status_peserta',
        'asuransi_kesehatan',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}

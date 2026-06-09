<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsJadwal extends Model
{
    protected $table = 'bpjs_jadwal';

    protected $fillable = [
        'kode_poli',
        'nama_poli',
        'kode_dokter',
        'nama_dokter',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'kuota',
    ];

    protected function casts(): array
    {
        return [
            'hari' => 'integer',
            'kuota' => 'integer',
        ];
    }
}

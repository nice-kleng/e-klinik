<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10Diagnosis extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

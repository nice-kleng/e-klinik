<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd9CmDiagnosis extends Model
{
    protected $fillable = [
        'code',
        'name',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('code', 'like', "%{$keyword}%")
              ->orWhere('name', 'like', "%{$keyword}%");
        });
    }
}

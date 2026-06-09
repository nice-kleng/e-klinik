<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestCategory extends Model
{
    protected $fillable = [
        'code', 'name', 'description', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class, 'category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'quantity',
        'unit',
        'dosage',
        'subtotal',
        'is_compound',
        'compound_name',
        'total_packets',
        'instruction',
        'tuslah',
        'embalase',
    ];

    protected function casts(): array
    {
        return [
            'dosage' => 'array',
            'is_compound' => 'boolean',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(PrescriptionItemIngredient::class, 'prescription_item_id');
    }
}

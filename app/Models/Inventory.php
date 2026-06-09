<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $fillable = [
        'medicine_id',
        'supplier_id',
        'batch_number',
        'quantity',
        'unit_price',
        'selling_price',
        'production_date',
        'expired_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'expired_date' => 'date',
        ];
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_id');
    }

    public function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->expired_date && Carbon::now()->gte($this->expired_date),
        );
    }
}

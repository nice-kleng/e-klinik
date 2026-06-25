<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'category_id',
        'manufacturer',
        'unit',
        'content',
        'description',
        'is_generic',
        'requires_prescription',
        'is_active',
        'kfa_code',
        'kfa_name',
        'dosage_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'is_generic' => 'boolean',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class, 'medicine_id');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class, 'medicine_id');
    }

    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'medicine_id');
    }
}

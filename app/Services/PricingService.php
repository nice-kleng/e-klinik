<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\PrescriptionItem;
use Illuminate\Support\Facades\Log;

class PricingService
{
    public function autoCalcIngredient(float $qtyPerPacket, int $totalPackets, ?float $dosagePerUnit): ?float
    {
        if (!$dosagePerUnit || $dosagePerUnit <= 0) {
            return null;
        }

        return ceil(($qtyPerPacket * $totalPackets) / $dosagePerUnit);
    }

    public function getFifoSellingPrice(int $medicineId): float
    {
        $batch = Inventory::where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now()->toDateString());
            })
            ->orderBy('expired_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$batch) {
            Log::warning("No stock found for medicine #{$medicineId} — harga 0");

            return 0;
        }

        return (float) $batch->selling_price;
    }

    public function calcNonCompound(Medicine $medicine, int $quantity): float
    {
        $unitPrice = $this->getFifoSellingPrice($medicine->id);

        return round($unitPrice * $quantity, 2);
    }

    public function calcCompound(PrescriptionItem $item): float
    {
        $ingredientCost = 0;

        foreach ($item->ingredients as $ingredient) {
            if (!$ingredient->calculated_qty || $ingredient->calculated_qty <= 0) {
                continue;
            }

            $unitPrice = $this->getFifoSellingPrice($ingredient->medicine_id);
            $ingredientCost += $unitPrice * $ingredient->calculated_qty;
        }

        $tuslah = $item->tuslah ?? config('pharmacy.tuslah', 3000);
        $embalase = $item->embalase ?? config('pharmacy.embalase', 1000);

        return round($ingredientCost + $tuslah + $embalase, 2);
    }

    public function calcSubtotal(PrescriptionItem $item): float
    {
        if ($item->is_compound) {
            return $this->calcCompound($item);
        }

        return $this->calcNonCompound($item->medicine, $item->quantity);
    }
}

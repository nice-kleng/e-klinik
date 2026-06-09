<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Medicine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function getStock(Medicine $medicine): int
    {
        return (int) Inventory::where('medicine_id', $medicine->id)
            ->where(function ($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now()->toDateString());
            })
            ->sum('quantity');
    }

    public function getStockByBatch(Medicine $medicine): Collection
    {
        return Inventory::with(['supplier'])
            ->where('medicine_id', $medicine->id)
            ->where(function ($query) {
                $query->whereNull('expired_date')
                    ->orWhere('expired_date', '>=', now()->toDateString());
            })
            ->where('quantity', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function addStock(Medicine $medicine, array $data): Inventory
    {
        return DB::transaction(function () use ($medicine, $data) {
            $inventory = Inventory::create([
                'medicine_id' => $medicine->id,
                'supplier_id' => $data['supplier_id'] ?? null,
                'batch_number' => $data['batch_number'] ?? $this->generateBatchNumber($medicine),
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? 0,
                'selling_price' => $data['selling_price'] ?? 0,
                'production_date' => $data['production_date'] ?? null,
                'expired_date' => $data['expired_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            $this->createTransaction('purchase', [
                'inventory_id' => $inventory->id,
                'medicine_id' => $medicine->id,
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'] ?? 0,
                'total_price' => ($data['unit_price'] ?? 0) * $data['quantity'],
                'reference_type' => 'purchase_order',
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => 'Stock addition: ' . ($data['notes'] ?? 'purchase'),
            ]);

            return $inventory;
        });
    }

    public function removeStock(Medicine $medicine, int $quantity, string $batchNumber): bool
    {
        return DB::transaction(function () use ($medicine, $quantity, $batchNumber) {
            $inventory = Inventory::where('medicine_id', $medicine->id)
                ->where('batch_number', $batchNumber)
                ->where('quantity', '>', 0)
                ->orderBy('expired_date', 'asc')
                ->first();

            if (!$inventory) {
                throw new \RuntimeException("No available stock found for batch: {$batchNumber}");
            }

            if ($inventory->quantity < $quantity) {
                throw new \RuntimeException(
                    "Insufficient stock in batch {$batchNumber}. Available: {$inventory->quantity}, requested: {$quantity}"
                );
            }

            $inventory->decrement('quantity', $quantity);

            $this->createTransaction('sale', [
                'inventory_id' => $inventory->id,
                'medicine_id' => $medicine->id,
                'quantity' => -$quantity,
                'unit_price' => $inventory->selling_price,
                'total_price' => $inventory->selling_price * $quantity,
                'reference_type' => 'prescription',
                'reference_id' => null,
                'notes' => "Stock removal: {$quantity} units from batch {$batchNumber}",
            ]);

            return true;
        });
    }

    public function adjustStock(Inventory $inventory, int $newQuantity, string $reason): Inventory
    {
        return DB::transaction(function () use ($inventory, $newQuantity, $reason) {
            $oldQuantity = $inventory->quantity;
            $difference = $newQuantity - $oldQuantity;

            if ($difference === 0) {
                return $inventory;
            }

            $inventory->update(['quantity' => $newQuantity]);

            $this->createTransaction('adjustment', [
                'inventory_id' => $inventory->id,
                'medicine_id' => $inventory->medicine_id,
                'quantity' => $difference,
                'unit_price' => $inventory->unit_price,
                'total_price' => $inventory->unit_price * $difference,
                'reference_type' => 'stock_opname',
                'reference_id' => $inventory->id,
                'notes' => "Stock opname adjustment: {$reason} (was {$oldQuantity}, now {$newQuantity})",
            ]);

            return $inventory->fresh();
        });
    }

    public function getExpiringMedicine(int $days = 30): Collection
    {
        $targetDate = Carbon::now()->addDays($days)->toDateString();

        return Inventory::with(['medicine', 'supplier'])
            ->whereNotNull('expired_date')
            ->where('expired_date', '>=', now()->toDateString())
            ->where('expired_date', '<=', $targetDate)
            ->where('quantity', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->get();
    }

    public function getLowStockMedicines(int $threshold = 10): Collection
    {
        return Medicine::with(['category'])
            ->where('is_active', true)
            ->whereHas('inventories', function ($query) use ($threshold) {
                $query->selectRaw('SUM(quantity) as total_stock')
                    ->where(function ($q) {
                        $q->whereNull('expired_date')
                            ->orWhere('expired_date', '>=', now()->toDateString());
                    })
                    ->havingRaw('COALESCE(SUM(quantity), 0) < ?', [$threshold]);
            })
            ->orWhereDoesntHave('inventories')
            ->get()
            ->map(function ($medicine) {
                $medicine->current_stock = $this->getStock($medicine);
                return $medicine;
            })
            ->filter(fn ($m) => $m->current_stock < $threshold)
            ->values();
    }

    public function getExpiredMedicine(): Collection
    {
        return Inventory::with(['medicine', 'supplier'])
            ->whereNotNull('expired_date')
            ->where('expired_date', '<', now()->toDateString())
            ->where('quantity', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->get();
    }

    public function createTransaction(string $type, array $data): InventoryTransaction
    {
        return InventoryTransaction::create([
            'inventory_id' => $data['inventory_id'] ?? null,
            'medicine_id' => $data['medicine_id'],
            'type' => $type,
            'quantity' => $data['quantity'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'unit_price' => $data['unit_price'] ?? 0,
            'total_price' => $data['total_price'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);
    }

    public function getMedicineReport(string $startDate, string $endDate): array
    {
        $transactions = InventoryTransaction::with(['medicine', 'inventory'])
            ->whereBetween('created_at', [$startDate, Carbon::parse($endDate)->endOfDay()])
            ->get();

        $summary = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'by_medicine' => $transactions->groupBy('medicine_id')
                ->map(function ($items) {
                    $medicine = $items->first()->medicine;
                    return [
                        'medicine_id' => $items->first()->medicine_id,
                        'medicine_name' => $medicine?->name ?? 'Unknown',
                        'medicine_code' => $medicine?->code ?? 'Unknown',
                        'total_purchased' => $items->where('type', 'purchase')->sum('quantity'),
                        'total_sold' => abs($items->where('type', 'sale')->sum('quantity')),
                        'total_adjustments' => $items->where('type', 'adjustment')->sum('quantity'),
                        'transaction_count' => $items->count(),
                        'total_value' => $items->sum('total_price'),
                    ];
                })
                ->values()
                ->toArray(),
            'summary' => [
                'total_transactions' => $transactions->count(),
                'total_purchases' => $transactions->where('type', 'purchase')->count(),
                'total_sales' => $transactions->where('type', 'sale')->count(),
                'total_adjustments' => $transactions->where('type', 'adjustment')->count(),
                'total_purchase_value' => $transactions->where('type', 'purchase')->sum('total_price'),
                'total_sale_value' => $transactions->where('type', 'sale')->sum('total_price'),
            ],
        ];

        return $summary;
    }

    protected function generateBatchNumber(Medicine $medicine): string
    {
        return strtoupper(substr($medicine->code, 0, 3)) . '-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}

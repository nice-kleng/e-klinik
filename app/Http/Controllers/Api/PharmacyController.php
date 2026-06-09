<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Medicine;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PharmacyController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function medicines(Request $request): JsonResponse
    {
        try {
            $query = Medicine::with(['category', 'inventories']);

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('generic_name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $medicines = $query->orderBy('name', 'asc')
                ->paginate($request->per_page ?? 15);

            $medicines->getCollection()->transform(function ($medicine) {
                $medicine->current_stock = $this->inventoryService->getStock($medicine);
                return $medicine;
            });

            return response()->json([
                'success' => true,
                'data' => $medicines->items(),
                'meta' => [
                    'current_page' => $medicines->currentPage(),
                    'last_page' => $medicines->lastPage(),
                    'per_page' => $medicines->perPage(),
                    'total' => $medicines->total(),
                ],
                'message' => 'Daftar obat berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function medicineStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:medicines,code',
                'name' => 'required|string|max:255',
                'generic_name' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:medicine_categories,id',
                'manufacturer' => 'nullable|string|max:255',
                'unit' => 'required|string|max:50',
                'content' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'is_generic' => 'boolean',
                'requires_prescription' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $medicine = Medicine::create($validated);

            return response()->json([
                'success' => true,
                'data' => $medicine->load('category'),
                'message' => 'Obat berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function medicineShow(Medicine $medicine): JsonResponse
    {
        try {
            $medicine->load(['category', 'inventories.supplier']);
            $medicine->current_stock = $this->inventoryService->getStock($medicine);
            $medicine->stock_by_batch = $this->inventoryService->getStockByBatch($medicine);

            return response()->json([
                'success' => true,
                'data' => $medicine,
                'message' => 'Detail obat berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function medicineUpdate(Request $request, Medicine $medicine): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:medicines,code,' . $medicine->id,
                'name' => 'required|string|max:255',
                'generic_name' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:medicine_categories,id',
                'manufacturer' => 'nullable|string|max:255',
                'unit' => 'required|string|max:50',
                'content' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'is_generic' => 'boolean',
                'requires_prescription' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $medicine->update($validated);

            return response()->json([
                'success' => true,
                'data' => $medicine->fresh()->load('category'),
                'message' => 'Obat berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inventoryIndex(Request $request): JsonResponse
    {
        try {
            $query = Inventory::with(['medicine', 'supplier', 'creator']);

            if ($request->filled('medicine_id')) {
                $query->where('medicine_id', $request->medicine_id);
            }

            if ($request->filled('supplier_id')) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('expired')) {
                if ($request->boolean('expired')) {
                    $query->whereNotNull('expired_date')->where('expired_date', '<', now()->toDateString());
                } else {
                    $query->where(function ($q) {
                        $q->whereNull('expired_date')->orWhere('expired_date', '>=', now()->toDateString());
                    });
                }
            }

            $inventories = $query->orderBy('expired_date', 'asc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $inventories->items(),
                'meta' => [
                    'current_page' => $inventories->currentPage(),
                    'last_page' => $inventories->lastPage(),
                    'per_page' => $inventories->perPage(),
                    'total' => $inventories->total(),
                ],
                'message' => 'Daftar inventaris obat berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat inventaris: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat inventaris obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inventoryStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'medicine_id' => 'required|exists:medicines,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'batch_number' => 'nullable|string|max:100',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'nullable|numeric|min:0',
                'selling_price' => 'nullable|numeric|min:0',
                'production_date' => 'nullable|date',
                'expired_date' => 'nullable|date|after:today',
                'notes' => 'nullable|string',
            ]);

            $medicine = Medicine::findOrFail($validated['medicine_id']);
            $inventory = $this->inventoryService->addStock($medicine, $validated);

            return response()->json([
                'success' => true,
                'data' => $inventory->load(['medicine', 'supplier']),
                'message' => 'Stok obat berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan stok: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan stok obat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inventoryShow(Inventory $inventory): JsonResponse
    {
        try {
            $inventory->load(['medicine.category', 'supplier', 'creator', 'transactions']);

            return response()->json([
                'success' => true,
                'data' => $inventory,
                'message' => 'Detail inventaris berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail inventaris: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail inventaris',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function inventoryAdjust(Request $request, Inventory $inventory): JsonResponse
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:0',
                'reason' => 'required|string|max:255',
            ]);

            $inventory = $this->inventoryService->adjustStock(
                $inventory,
                $validated['quantity'],
                $validated['reason']
            );

            return response()->json([
                'success' => true,
                'data' => $inventory->load(['medicine', 'supplier']),
                'message' => 'Stok berhasil disesuaikan',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyesuaikan stok: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyesuaikan stok',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function lowStock(): JsonResponse
    {
        try {
            $medicines = $this->inventoryService->getLowStockMedicines();

            return response()->json([
                'success' => true,
                'data' => $medicines,
                'message' => 'Obat dengan stok menipis',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat stok menipis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data stok menipis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function expiringSoon(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $inventories = $this->inventoryService->getExpiringMedicine((int) $days);

            return response()->json([
                'success' => true,
                'data' => $inventories,
                'message' => "Obat yang akan kedaluwarsa dalam {$days} hari",
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat obat kedaluwarsa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data obat kedaluwarsa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function expired(): JsonResponse
    {
        try {
            $inventories = $this->inventoryService->getExpiredMedicine();

            return response()->json([
                'success' => true,
                'data' => $inventories,
                'message' => 'Obat yang sudah kedaluwarsa',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat obat kedaluwarsa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data obat kedaluwarsa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function transactions(Request $request): JsonResponse
    {
        try {
            $query = \App\Models\InventoryTransaction::with(['medicine', 'inventory', 'creator']);

            if ($request->filled('medicine_id')) {
                $query->where('medicine_id', $request->medicine_id);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
                'message' => 'Riwayat transaksi inventaris',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat transaksi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat transaksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

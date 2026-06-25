<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request): View
    {
        $query = Inventory::with(['medicine', 'supplier', 'creator']);

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $inventories = $query->where('quantity', '>', 0)
            ->orderBy('expired_date', 'asc')
            ->paginate(20)
            ->withQueryString();

        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventories.index', compact('inventories', 'medicines', 'suppliers'));
    }

    public function create(): View
    {
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventories.create', compact('medicines', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'production_date' => 'nullable|date',
            'expired_date' => 'nullable|date|after_or_equal:production_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $medicine = Medicine::findOrFail($validated['medicine_id']);

            $this->inventoryService->addStock($medicine, $validated);

            return redirect()->route('inventories.index')
                ->with('success', 'Stok obat berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan stok: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan stok: ' . $e->getMessage());
        }
    }

    public function show(Inventory $inventory): View
    {
        $inventory->load(['medicine.category', 'supplier', 'creator', 'transactions']);

        return view('inventories.show', compact('inventory'));
    }

    public function lowStock(): View
    {
        $medicines = Medicine::with(['category'])
            ->where('is_active', true)
            ->get()
            ->map(function ($m) {
                $m->current_stock = $this->inventoryService->getStock($m);
                return $m;
            })
            ->filter(function ($m) {
                $threshold = $m->minimum_stock ?? 10;
                return $m->current_stock < $threshold;
            })
            ->values();

        return view('inventories.low-stock', compact('medicines'));
    }

    public function expiring(): View
    {
        $days = 30;
        $items = $this->inventoryService->getExpiringMedicine($days);

        return view('inventories.expiring', compact('items', 'days'));
    }

    public function expired(): View
    {
        $items = $this->inventoryService->getExpiredMedicine();

        return view('inventories.expired', compact('items'));
    }

    public function edit(Inventory $inventory): View
    {
        $inventory->load('medicine');
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('inventories.edit', compact('inventory', 'medicines', 'suppliers'));
    }

    public function update(Request $request, Inventory $inventory): RedirectResponse
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'batch_number' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'production_date' => 'nullable|date',
            'expired_date' => 'nullable|date|after_or_equal:production_date',
            'notes' => 'nullable|string',
        ]);

        try {
            $inventory->update($validated);

            return redirect()->route('inventories.show', $inventory)
                ->with('success', 'Batch stok berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui batch stok: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui batch stok');
        }
    }

    public function destroy(Inventory $inventory): RedirectResponse
    {
        try {
            $inventory->transactions()->delete();
            $inventory->delete();

            return redirect()->route('inventories.index')
                ->with('success', 'Batch stok berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus batch stok: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal menghapus batch stok');
        }
    }

    public function opname(Request $request): View|RedirectResponse
    {
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get()
            ->map(function ($m) {
                $m->current_stock = $this->inventoryService->getStock($m);
                return $m;
            });

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.medicine_id' => 'required|exists:medicines,id',
                'items.*.actual_qty' => 'required|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            try {
                $adjusted = 0;
                foreach ($validated['items'] as $item) {
                    $medicine = Medicine::findOrFail($item['medicine_id']);
                    $result = $this->inventoryService->opnameStock(
                        $medicine,
                        (int) $item['actual_qty'],
                        auth()->id()
                    );
                    if ($result) $adjusted++;
                }

                return redirect()->route('inventories.opname')
                    ->with('success', "Stok opname selesai. $adjusted obat disesuaikan.");
            } catch (\Exception $e) {
                Log::error('Gagal stok opname: ' . $e->getMessage());

                return redirect()->back()->with('error', 'Gagal melakukan stok opname: ' . $e->getMessage());
            }
        }

        return view('inventories.opname', compact('medicines'));
    }
}

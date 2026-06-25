<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MedicineController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request): View
    {
        $query = Medicine::with(['category']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('generic_name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        $medicines = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($medicine) {
                $medicine->current_stock = $this->inventoryService->getStock($medicine);
                return $medicine;
            });

        return view('medicines.index', compact('medicines'));
    }

    public function create(): View
    {
        $categories = MedicineCategory::orderBy('name')->get();

        return view('medicines.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:medicines,code',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'dosage_per_unit' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'content' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_generic' => 'nullable|boolean',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            Medicine::create($validated);

            return redirect()->route('medicines.index')
                ->with('success', 'Obat berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan obat: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan obat: ' . $e->getMessage());
        }
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['category', 'inventories.supplier']);
        $currentStock = $this->inventoryService->getStock($medicine);
        $batches = $this->inventoryService->getStockByBatch($medicine);

        return view('medicines.show', compact('medicine', 'currentStock', 'batches'));
    }

    public function edit(Medicine $medicine): View
    {
        $categories = MedicineCategory::orderBy('name')->get();

        return view('medicines.edit', compact('medicine', 'categories'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:medicines,code,' . $medicine->id,
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:medicine_categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'dosage_per_unit' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
            'content' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_generic' => 'nullable|boolean',
            'requires_prescription' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $medicine->update($validated);

            return redirect()->route('medicines.show', $medicine)
                ->with('success', 'Data obat berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui obat: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data obat');
        }
    }
}

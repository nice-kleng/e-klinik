<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionItemIngredient;
use App\Services\InventoryService;
use App\Services\MedicalRecordService;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    protected MedicalRecordService $medicalRecordService;
    protected PricingService $pricingService;
    protected InventoryService $inventoryService;

    public function __construct(
        MedicalRecordService $medicalRecordService,
        PricingService $pricingService,
        InventoryService $inventoryService
    ) {
        $this->medicalRecordService = $medicalRecordService;
        $this->pricingService = $pricingService;
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request): View
    {
        $query = Prescription::with(['patient', 'doctor', 'medicalRecord'])->withCount('items');

        if ($request->filled('date_from')) {
            $query->whereDate('prescription_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('prescription_date', '<=', $request->date_to);
        }

        $prescriptions = $query->orderBy('prescription_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function pending(Request $request): View
    {
        $query = Prescription::with(['patient', 'doctor', 'medicalRecord'])->withCount('items')
            ->where('status', 'active');

        if ($request->filled('date_from')) {
            $query->whereDate('prescription_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('prescription_date', '<=', $request->date_to);
        }

        $prescriptions = $query->orderBy('prescription_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('prescriptions.pending', compact('prescriptions'));
    }

    public function create(Request $request): View
    {
        $medicalRecords = MedicalRecord::with(['patient', 'doctor'])
            ->orderBy('visit_date', 'desc')
            ->get();
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();
        $selectedMedicalRecordId = $request->get('medical_record_id');

        return view('prescriptions.create', compact('medicalRecords', 'medicines', 'selectedMedicalRecordId'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.dosage' => 'nullable|string',
            'items.*.is_compound' => 'nullable|boolean',
            'items.*.compound_name' => 'nullable|string|max:100',
            'items.*.total_packets' => 'nullable|integer|min:1',
            'items.*.instruction' => 'nullable|string',
            'items.*.ingredients' => 'nullable|array',
            'items.*.ingredients.*.medicine_id' => 'required_with:items.*.ingredients|exists:medicines,id',
            'items.*.ingredients.*.qty_per_packet' => 'required_with:items.*.ingredients|numeric|min:0.01',
            'items.*.ingredients.*.unit' => 'nullable|string|max:20',
            'items.*.ingredients.*.calculated_qty' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $mr = MedicalRecord::findOrFail($validated['medical_record_id']);

                $prescriptionNumber = $this->generatePrescriptionNumber();

                $prescription = Prescription::create([
                    'medical_record_id' => $mr->id,
                    'patient_id' => $mr->patient_id,
                    'doctor_id' => $mr->doctor_id,
                    'prescription_number' => $prescriptionNumber,
                    'prescription_date' => now()->toDateString(),
                    'status' => 'active',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => Auth::id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $medicine = Medicine::findOrFail($item['medicine_id']);
                    $isCompound = $item['is_compound'] ?? false;

                    $prescriptionItem = PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'dosage' => $item['dosage'] ?? null,
                        'subtotal' => 0,
                        'is_compound' => $isCompound,
                        'compound_name' => $item['compound_name'] ?? null,
                        'total_packets' => $item['total_packets'] ?? null,
                        'instruction' => $item['instruction'] ?? null,
                    ]);

                    if ($isCompound && !empty($item['ingredients'])) {
                        foreach ($item['ingredients'] as $ingredient) {
                            PrescriptionItemIngredient::create([
                                'prescription_item_id' => $prescriptionItem->id,
                                'medicine_id' => $ingredient['medicine_id'],
                                'qty_per_packet' => $ingredient['qty_per_packet'],
                                'unit' => $ingredient['unit'] ?? 'mg',
                                'calculated_qty' => $ingredient['calculated_qty'] ?? null,
                            ]);
                        }
                    }

                    $prescriptionItem->load('ingredients');
                    $prescriptionItem->subtotal = $this->pricingService->calcSubtotal($prescriptionItem);
                    $prescriptionItem->save();
                }
            });

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Resep berhasil ditambahkan']);
            }

            return redirect()->route('prescriptions.index')
                ->with('success', 'Resep berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan resep: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gagal menambahkan resep: ' . $e->getMessage()], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan resep: ' . $e->getMessage());
        }
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine', 'items.ingredients.medicine', 'creator', 'dispenser']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription): View
    {
        if ($prescription->status !== 'active') {
            return redirect()->route('prescriptions.show', $prescription)
                ->with('error', 'Resep sudah ' . $prescription->status . ', tidak bisa diedit');
        }

        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine', 'items.ingredients.medicine']);
        $medicalRecords = MedicalRecord::with(['patient', 'doctor'])
            ->where('patient_id', $prescription->patient_id)
            ->orderBy('visit_date', 'desc')
            ->get();
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        return view('prescriptions.edit', compact('prescription', 'medicalRecords', 'medicines'));
    }

    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status !== 'active') {
            return redirect()->route('prescriptions.show', $prescription)
                ->with('error', 'Resep sudah ' . $prescription->status . ', tidak bisa diubah');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.dosage' => 'nullable|string',
            'items.*.is_compound' => 'nullable|boolean',
            'items.*.compound_name' => 'nullable|string|max:100',
            'items.*.total_packets' => 'nullable|integer|min:1',
            'items.*.instruction' => 'nullable|string',
            'items.*.ingredients' => 'nullable|array',
            'items.*.ingredients.*.medicine_id' => 'required_with:items.*.ingredients|exists:medicines,id',
            'items.*.ingredients.*.qty_per_packet' => 'required_with:items.*.ingredients|numeric|min:0.01',
            'items.*.ingredients.*.unit' => 'nullable|string|max:20',
            'items.*.ingredients.*.calculated_qty' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($prescription, $validated) {
                $prescription->update([
                    'notes' => $validated['notes'] ?? null,
                ]);

                $prescription->items()->delete();

                foreach ($validated['items'] as $item) {
                    $isCompound = $item['is_compound'] ?? false;

                    $prescriptionItem = PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'dosage' => $item['dosage'] ?? null,
                        'subtotal' => 0,
                        'is_compound' => $isCompound,
                        'compound_name' => $item['compound_name'] ?? null,
                        'total_packets' => $item['total_packets'] ?? null,
                        'instruction' => $item['instruction'] ?? null,
                    ]);

                    if ($isCompound && !empty($item['ingredients'])) {
                        foreach ($item['ingredients'] as $ingredient) {
                            PrescriptionItemIngredient::create([
                                'prescription_item_id' => $prescriptionItem->id,
                                'medicine_id' => $ingredient['medicine_id'],
                                'qty_per_packet' => $ingredient['qty_per_packet'],
                                'unit' => $ingredient['unit'] ?? 'mg',
                                'calculated_qty' => $ingredient['calculated_qty'] ?? null,
                            ]);
                        }
                    }

                    $prescriptionItem->load('ingredients');
                    $prescriptionItem->subtotal = $this->pricingService->calcSubtotal($prescriptionItem);
                    $prescriptionItem->save();
                }
            });

            return redirect()->route('prescriptions.show', $prescription)
                ->with('success', 'Resep berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui resep: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui resep: ' . $e->getMessage());
        }
    }

    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status !== 'active') {
            return redirect()->back()->with('error', 'Resep sudah ' . $prescription->status);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($prescription, $validated) {
                $prescription->load('items.ingredients.medicine', 'items.medicine');

                foreach ($prescription->items as $item) {
                    if ($item->is_compound) {
                        foreach ($item->ingredients as $ingredient) {
                            $qty = (int) ($ingredient->calculated_qty ?? 0);
                            if ($qty > 0) {
                                $this->inventoryService->deductFifo(
                                    $ingredient->medicine,
                                    $qty,
                                    $prescription->id
                                );
                            }
                        }
                    } else {
                        $this->inventoryService->deductFifo(
                            $item->medicine,
                            $item->quantity,
                            $prescription->id
                        );
                    }
                }

                $prescription->update([
                    'status' => 'dispensed',
                    'dispensed_by' => Auth::id(),
                    'dispensed_at' => now(),
                    'notes' => $validated['notes'] ?? $prescription->notes,
                ]);

                $registration = $prescription->medicalRecord?->registration;
                if ($registration && $registration->service_status === 'pharmacy') {
                    $remainingActive = \App\Models\Prescription::where('status', 'active')
                        ->whereHas('medicalRecord', fn ($q) => $q->where('registration_id', $registration->id))
                        ->exists();

                    if (!$remainingActive) {
                        $pharmacyQueue = \App\Models\PharmacyQueue::where('registration_id', $registration->id)
                            ->where('status', '!=', 'completed')
                            ->latest()
                            ->first();

                        if ($pharmacyQueue && in_array($pharmacyQueue->status, ['waiting', 'called', 'in_progress'])) {
                            $pharmacyQueue->update(['status' => 'completed', 'completed_at' => now()]);
                        }

                        $registration->update(['service_status' => 'cashier']);
                    }
                }
            });

            return redirect()->route('prescriptions.show', $prescription)
                ->with('success', 'Resep berhasil diserahkan ke pasien. Stok obat telah dikurangi.');
        } catch (\RuntimeException $e) {
            Log::error('Gagal mendispense (stok): ' . $e->getMessage());

            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal mendispense resep: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memproses resep: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Prescription $prescription): RedirectResponse
    {
        if ($prescription->status !== 'active') {
            return redirect()->back()->with('error', 'Resep sudah ' . $prescription->status);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        try {
            $prescription->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['cancellation_reason'] ?? null,
            ]);

            return redirect()->route('prescriptions.index')
                ->with('success', 'Resep berhasil dibatalkan');
        } catch (\Exception $e) {
            Log::error('Gagal membatalkan resep: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal membatalkan resep');
        }
    }

    public function lastByPatient(Patient $patient): JsonResponse
    {
        $prescription = Prescription::with(['items.medicine', 'items.ingredients.medicine'])
            ->where('patient_id', $patient->id)
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->first();

        if (!$prescription) {
            return response()->json(['success' => false, 'message' => 'Tidak ada resep sebelumnya']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'prescription_number' => $prescription->prescription_number,
                'prescription_date' => $prescription->prescription_date?->format('d/m/Y'),
                'doctor_name' => $prescription->doctor?->name,
                'items' => $prescription->items->map(function ($item) {
                    return [
                        'medicine_id' => $item->medicine_id,
                        'medicine_name' => $item->medicine?->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'dosage' => $item->dosage,
                        'is_compound' => $item->is_compound,
                        'compound_name' => $item->compound_name,
                        'total_packets' => $item->total_packets,
                        'instruction' => $item->instruction,
                        'ingredients' => $item->ingredients->map(function ($ing) {
                            return [
                                'medicine_id' => $ing->medicine_id,
                                'medicine_name' => $ing->medicine?->name,
                                'qty_per_packet' => $ing->qty_per_packet,
                                'unit' => $ing->unit,
                                'calculated_qty' => $ing->calculated_qty,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    public function print(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine', 'items.ingredients.medicine']);

        return view('prescriptions.print', compact('prescription'));
    }

    public function etiket(Prescription $prescription): View
    {
        $prescription->load(['patient', 'items.medicine', 'items.prescription']);
        $items = $prescription->items;
        $patient = $prescription->patient;

        return view('prescriptions.etiket', compact('items', 'patient'));
    }

    protected function generatePrescriptionNumber(): string
    {
        $date = now()->format('Ymd');
        $last = Prescription::whereDate('created_at', today())->count();

        return 'RX-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }
}

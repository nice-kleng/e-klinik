<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Services\MedicalRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    protected MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    public function index(Request $request): View
    {
        $query = Prescription::with(['patient', 'doctor', 'medicalRecord']);

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

    public function create(Request $request): View
    {
        $medicalRecords = MedicalRecord::with(['patient', 'doctor'])
            ->orderBy('visit_date', 'desc')
            ->get();
        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();
        $selectedMedicalRecordId = $request->get('medical_record_id');

        return view('prescriptions.create', compact('medicalRecords', 'medicines', 'selectedMedicalRecordId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'medical_record_id' => 'required|exists:medical_records,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.dosage' => 'nullable|string',
            'items.*.subtotal' => 'nullable|numeric|min:0',
        ]);

        try {
            $mr = MedicalRecord::findOrFail($validated['medical_record_id']);

            $this->medicalRecordService->createPrescription($mr, [
                'items' => $validated['items'],
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('prescriptions.index')
                ->with('success', 'Resep berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan resep: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan resep: ' . $e->getMessage());
        }
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine', 'creator']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function print(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine']);

        return view('prescriptions.print', compact('prescription'));
    }
}

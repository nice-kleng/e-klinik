<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Services\MedicalRecordService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    protected MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    public function index(Request $request): View
    {
        $query = MedicalRecord::with(['patient', 'doctor', 'polyclinic']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('visit_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('visit_date', '<=', $request->date_to);
        }

        $records = $query->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $patients = Patient::orderBy('name')->get();

        return view('medical-records.index', compact('records', 'patients'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::orderBy('name')->get();
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();
        $queueId = $request->get('queue_id');
        $selectedPatientId = $request->get('patient_id');

        return view('medical-records.create', compact('patients', 'polyclinics', 'doctors', 'queueId', 'selectedPatientId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'queue_id' => 'nullable|exists:queues,id',
            'visit_date' => 'required|date',
            'visit_type' => 'nullable|string|max:50',
            'subjective_complaint' => 'nullable|string',
            'objective_finding' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'diagnosis_primary' => 'nullable|string|max:20',
            'diagnosis_secondary' => 'nullable|array',
            'anamnesis' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        try {
            $this->medicalRecordService->createRecord($validated);

            return redirect()->route('medical-records.index')
                ->with('success', 'Rekam medis berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan rekam medis: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan rekam medis: ' . $e->getMessage());
        }
    }

    public function show(MedicalRecord $medicalRecord): View
    {
        $medicalRecord->load(['patient', 'doctor', 'polyclinic', 'queue', 'prescriptions.items.medicine', 'creator']);

        return view('medical-records.show', compact('medicalRecord'));
    }

    public function edit(MedicalRecord $medicalRecord): View
    {
        $medicalRecord->load(['patient', 'doctor', 'polyclinic']);
        $patients = Patient::orderBy('name')->get();
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();

        return view('medical-records.edit', compact('medicalRecord', 'patients', 'polyclinics', 'doctors'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'visit_date' => 'required|date',
            'visit_type' => 'nullable|string|max:50',
            'subjective_complaint' => 'nullable|string',
            'objective_finding' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'diagnosis_primary' => 'nullable|string|max:20',
            'diagnosis_secondary' => 'nullable|array',
            'anamnesis' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        try {
            $this->medicalRecordService->updateRecord($medicalRecord, $validated);

            return redirect()->route('medical-records.show', $medicalRecord)
                ->with('success', 'Rekam medis berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui rekam medis: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui rekam medis: ' . $e->getMessage());
        }
    }

    public function destroy(MedicalRecord $medicalRecord): RedirectResponse
    {
        try {
            $medicalRecord->delete();

            return redirect()->route('medical-records.index')
                ->with('success', 'Rekam medis berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus rekam medis: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus rekam medis');
        }
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Icd9CmDiagnosis;
use App\Models\Icd10Diagnosis;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\Registration;
use App\Services\MedicalRecordService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
        $date = $request->get('date', now()->toDateString());
        $polyclinicId = $request->get('polyclinic_id');

        $query = Queue::with([
            'registration.patient',
            'registration.doctor',
            'polyclinic',
            'medicalRecord',
        ])->where('queue_date', $date);

        if (auth()->user()->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if ($doctor) {
                $query->whereHas('registration', fn($q) => $q->where('doctor_id', $doctor->id));
            }
        }

        if ($polyclinicId) {
            $query->where('polyclinic_id', $polyclinicId);
        }

        $queues = $query->orderBy('queue_sequence', 'asc')->paginate(20)->withQueryString();

        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();

        return view('medical-records.index', compact('queues', 'polyclinics', 'date', 'polyclinicId'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::orderBy('name')->get();
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();
        $queueId = $request->get('queue_id');
        $selectedPatientId = $request->get('patient_id');
        $selectedDoctorId = null;
        $selectedPolyclinicId = null;
        $registrationId = null;
        $visitType = null;

        $selectedPatientId = $request->get('patient_id');

        if ($queueId) {
            $queue = Queue::with('registration')->find($queueId);
            if ($queue && $queue->registration) {
                $reg = $queue->registration;
                $selectedPatientId = $reg->patient_id;
                $selectedDoctorId = $reg->doctor_id;
                $selectedPolyclinicId = $reg->polyclinic_id;
                $registrationId = $reg->id;
                $visitType = $reg->visit_type;
            }
        }

        return view('medical-records.create', compact(
            'patients', 'polyclinics', 'doctors',
            'queueId', 'selectedPatientId', 'selectedDoctorId', 'selectedPolyclinicId', 'registrationId', 'visitType'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'registration_id' => 'nullable|exists:registrations,id',
            'queue_id' => 'nullable|exists:queues,id',
            'visit_date' => 'required|date',
            'visit_type' => 'nullable|string|in:Baru,Lama,Kontrol,Rujukan',
            'subjective_complaint' => 'nullable|string',
            'objective_finding' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'diagnosis_primary' => 'nullable|string|max:20',
            'diagnosis_secondary' => 'nullable|array',
            'diagnosis_primary_id' => 'nullable|exists:icd10_diagnoses,id',
            'diagnosis_secondary_ids' => 'nullable|array',
            'diagnosis_secondary_ids.*' => 'exists:icd10_diagnoses,id',
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'exists:icd9_cm_diagnoses,id',
            'procedure_notes' => 'nullable|array',
            'anamnesis' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        if (empty($validated['visit_type']) && !empty($validated['registration_id'])) {
            $reg = Registration::find($validated['registration_id']);
            if ($reg && $reg->visit_type) {
                $validated['visit_type'] = $reg->visit_type;
            }
        }

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
        $medicalRecord->load(['patient', 'doctor', 'polyclinic', 'queue', 'prescriptions.items.medicine', 'creator', 'diagnoses.icd10Diagnosis', 'procedures.icd9CmDiagnosis']);

        $previousRecords = MedicalRecord::with(['polyclinic', 'doctor', 'registration'])
            ->where('patient_id', $medicalRecord->patient_id)
            ->where('id', '!=', $medicalRecord->id)
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('medical-records.show', compact('medicalRecord', 'previousRecords'));
    }

    public function workspace(Queue $queue): View
    {
        $queue->load([
            'registration.patient',
            'registration.doctor',
            'registration.polyclinic',
            'polyclinic',
            'medicalRecord',
        ]);

        $patientId = $queue->registration?->patient_id;
        $previousRecords = collect();
        if ($patientId) {
            $previousRecords = MedicalRecord::with(['polyclinic', 'doctor', 'registration'])
                ->where('patient_id', $patientId)
                ->orderBy('visit_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        }

        return view('medical-records.workspace', compact('queue', 'previousRecords'));
    }

    public function edit(MedicalRecord $medicalRecord): View
    {
        $medicalRecord->load(['patient', 'doctor', 'polyclinic', 'diagnoses.icd10Diagnosis', 'procedures.icd9CmDiagnosis']);
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
            'visit_type' => 'nullable|string|in:Baru,Lama,Kontrol,Rujukan',
            'subjective_complaint' => 'nullable|string',
            'objective_finding' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'diagnosis_primary' => 'nullable|string|max:20',
            'diagnosis_secondary' => 'nullable|array',
            'diagnosis_primary_id' => 'nullable|exists:icd10_diagnoses,id',
            'diagnosis_secondary_ids' => 'nullable|array',
            'diagnosis_secondary_ids.*' => 'exists:icd10_diagnoses,id',
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'exists:icd9_cm_diagnoses,id',
            'procedure_notes' => 'nullable|array',
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

    public function icd10Search(Request $request): JsonResponse
    {
        $keyword = $request->get('q', '');
        $diagnoses = Icd10Diagnosis::where(function ($q) use ($keyword) {
            $q->where('code', 'like', "%{$keyword}%")
              ->orWhere('name', 'like', "%{$keyword}%");
        })
            ->where('is_active', true)
            ->orderBy('code')
            ->limit(20)
            ->get(['id', 'code', 'name']);

        return response()->json($diagnoses)->header('Content-Type', 'application/json');
    }

    public function icd9Search(Request $request): JsonResponse
    {
        $keyword = $request->get('q', '');
        $procedures = Icd9CmDiagnosis::where(function ($q) use ($keyword) {
            $q->where('code', 'like', "%{$keyword}%")
              ->orWhere('name', 'like', "%{$keyword}%");
        })
            ->where('is_active', true)
            ->orderBy('code')
            ->limit(20)
            ->get(['id', 'code', 'name']);

        return response()->json($procedures)->header('Content-Type', 'application/json');
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

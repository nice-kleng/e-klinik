<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Icd9CmDiagnosis;
use App\Models\InformedConsent;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Registration;
use App\Services\InformedConsentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InformedConsentController extends Controller
{
    protected InformedConsentService $consentService;

    public function __construct(InformedConsentService $consentService)
    {
        $this->consentService = $consentService;
    }

    public function index(Request $request): View
    {
        $query = InformedConsent::with(['patient', 'medicalRecord.doctor', 'signer', 'creator'])
            ->latest();

        if (auth()->user()->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', auth()->id())->first();
            if ($doctor) {
                $query->whereHas('medicalRecord', fn($q) => $q->where('doctor_id', $doctor->id));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('consent_type')) {
            $query->where('consent_type', $request->consent_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('no_rm', 'like', "%{$search}%"));
        }

        $consents = $query->paginate(20)->withQueryString();

        $consentTypes = [
            'general' => 'Persetujuan Umum',
            'procedure' => 'Tindakan Medis',
            'surgery' => 'Operasi',
            'anesthesia' => 'Anestesi',
            'transfusion' => 'Transfusi Darah',
            'other' => 'Lainnya',
        ];

        return view('informed-consents.index', compact('consents', 'consentTypes'));
    }

    public function create(Request $request): View
    {
        $patients = Patient::orderBy('name')->get();
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();
        $consentTypes = [
            'general' => 'Persetujuan Umum',
            'procedure' => 'Tindakan Medis',
            'surgery' => 'Operasi',
            'anesthesia' => 'Anestesi',
            'transfusion' => 'Transfusi Darah',
            'other' => 'Lainnya',
        ];

        $selectedRegistrationId = $request->get('registration_id');
        $selectedMrId = $request->get('medical_record_id');
        $selectedPatientId = $request->get('patient_id');
        $selectedProcedures = collect();

        if ($selectedMrId) {
            $mr = MedicalRecord::with('procedures.icd9CmDiagnosis')->find($selectedMrId);
            $selectedPatientId = $mr?->patient_id;
            $selectedProcedures = $mr?->procedures ?? collect();

            if (!$selectedRegistrationId) {
                $selectedRegistrationId = $mr?->registration_id;
            }
        }

        return view('informed-consents.create', compact(
            'patients', 'polyclinics', 'doctors', 'consentTypes',
            'selectedRegistrationId', 'selectedMrId', 'selectedPatientId', 'selectedProcedures'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'registration_id' => 'nullable|exists:registrations,id',
            'consent_type' => 'required|string|in:general,procedure,surgery,anesthesia,transfusion,other',
            'procedure_name' => 'nullable|string|max:255',
            'procedure_icd9_id' => 'nullable|exists:icd9_cm_diagnoses,id',
            'diagnosis' => 'nullable|string',
            'purpose' => 'nullable|string',
            'risks' => 'nullable|string',
            'benefits' => 'nullable|string',
            'alternatives' => 'nullable|string',
            'doctor_recommendation' => 'nullable|string',
            'witness_name' => 'nullable|string|max:255',
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'exists:medical_record_procedures,id',
        ]);

        try {
            $this->consentService->createConsent($validated);

            return redirect()->route('informed-consents.index')
                ->with('success', 'Informed consent berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal membuat informed consent: ' . $e->getMessage());
        }
    }

    public function show(InformedConsent $informedConsent): View
    {
        $informedConsent->load(['patient', 'medicalRecord.doctor', 'medicalRecord.polyclinic', 'signer', 'creator', 'procedureIcd9', 'procedures.icd9CmDiagnosis']);

        $consentTypes = [
            'general' => 'Persetujuan Umum',
            'procedure' => 'Tindakan Medis',
            'surgery' => 'Operasi',
            'anesthesia' => 'Anestesi',
            'transfusion' => 'Transfusi Darah',
            'other' => 'Lainnya',
        ];

        return view('informed-consents.show', compact('informedConsent', 'consentTypes'));
    }

    public function edit(InformedConsent $informedConsent): View
    {
        if ($informedConsent->status === 'signed') {
            abort(403, 'Informed consent sudah ditandatangani, tidak bisa diedit');
        }

        $informedConsent->load(['patient', 'medicalRecord', 'procedures']);
        $patients = Patient::orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();
        $consentTypes = [
            'general' => 'Persetujuan Umum',
            'procedure' => 'Tindakan Medis',
            'surgery' => 'Operasi',
            'anesthesia' => 'Anestesi',
            'transfusion' => 'Transfusi Darah',
            'other' => 'Lainnya',
        ];

        $selectedProcedures = $informedConsent->medicalRecord?->procedures ?? collect();

        return view('informed-consents.edit', compact('informedConsent', 'patients', 'doctors', 'consentTypes', 'selectedProcedures'));
    }

    public function update(Request $request, InformedConsent $informedConsent): RedirectResponse
    {
        if ($informedConsent->status === 'signed') {
            return redirect()->back()->with('error', 'Informed consent sudah ditandatangani, tidak bisa diubah');
        }

        $validated = $request->validate([
            'consent_type' => 'required|string|in:general,procedure,surgery,anesthesia,transfusion,other',
            'procedure_name' => 'nullable|string|max:255',
            'procedure_icd9_id' => 'nullable|exists:icd9_cm_diagnoses,id',
            'diagnosis' => 'nullable|string',
            'purpose' => 'nullable|string',
            'risks' => 'nullable|string',
            'benefits' => 'nullable|string',
            'alternatives' => 'nullable|string',
            'doctor_recommendation' => 'nullable|string',
            'witness_name' => 'nullable|string|max:255',
            'procedure_ids' => 'nullable|array',
            'procedure_ids.*' => 'exists:medical_record_procedures,id',
        ]);

        try {
            $this->consentService->updateConsent($informedConsent, $validated);

            return redirect()->route('informed-consents.show', $informedConsent)
                ->with('success', 'Informed consent berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal memperbarui informed consent: ' . $e->getMessage());
        }
    }

    public function signPatient(Request $request, InformedConsent $informedConsent): RedirectResponse
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
        ]);

        try {
            $this->consentService->signPatient($informedConsent, $validated['patient_name']);

            return redirect()->route('informed-consents.show', $informedConsent)
                ->with('success', 'Pasien berhasil menyetujui informed consent');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function signDoctor(Request $request, InformedConsent $informedConsent): RedirectResponse
    {
        try {
            $this->consentService->signDoctor($informedConsent, auth()->user());

            return redirect()->route('informed-consents.show', $informedConsent)
                ->with('success', 'Dokter berhasil menandatangani informed consent');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(InformedConsent $informedConsent): RedirectResponse
    {
        try {
            $this->consentService->cancel($informedConsent);

            return redirect()->route('informed-consents.index')
                ->with('success', 'Informed consent berhasil dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadPdf(InformedConsent $informedConsent)
    {
        $informedConsent->load(['patient', 'medicalRecord.doctor', 'signer', 'procedureIcd9', 'procedures.icd9CmDiagnosis']);

        $qrCodeSvg = null;
        if ($informedConsent->signature_hash) {
            $qrCodeSvg = QrCode::size(80)->generate(route('informed-consents.verify', $informedConsent->signature_hash));
        }

        return Pdf::loadView('informed-consents.pdf', compact('informedConsent', 'qrCodeSvg'))
            ->stream('informed-consent-' . $informedConsent->id . '.pdf');
    }

    public function verifyPdf(string $hash): View
    {
        $consent = $this->consentService->verifyHash($hash);

        return view('informed-consents.verify', compact('consent'));
    }

    public function proceduresByMedicalRecord(Request $request): JsonResponse
    {
        $mrId = $request->get('medical_record_id');
        $procedures = collect();

        if ($mrId) {
            $procedures = MedicalRecordProcedure::with('icd9CmDiagnosis')
                ->where('medical_record_id', $mrId)
                ->whereNull('informed_consent_id')
                ->orderBy('order')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'text' => ($p->icd9CmDiagnosis?->code ?? '') . ' — ' . ($p->icd9CmDiagnosis?->name ?? 'Tindakan #' . $p->id)]);
        }

        return response()->json($procedures);
    }
}

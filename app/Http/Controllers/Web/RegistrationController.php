<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\Registration;
use App\Services\PatientService;
use App\Services\QueueService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    protected PatientService $patientService;
    protected QueueService $queueService;

    public function __construct(PatientService $patientService, QueueService $queueService)
    {
        $this->patientService = $patientService;
        $this->queueService = $queueService;
    }

    public function index(): View
    {
        $polyclinics = Polyclinic::where('is_active', true)->orderBy('name')->get();
        $doctors = Doctor::with('polyclinic')->where('is_active', true)->orderBy('name')->get();

        $todayQueues = Queue::with(['registration.patient', 'polyclinic'])
            ->whereDate('queue_date', now()->toDateString())
            ->orderBy('queue_sequence', 'asc')
            ->get()
            ->groupBy('polyclinic_id');

        $mjknQueues = \App\Models\BpjsAntrean::whereNull('queue_id')
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $history = Registration::with(['patient', 'polyclinic', 'queue'])
            ->whereDate('registration_date', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('registration.index', compact(
            'polyclinics', 'doctors', 'todayQueues', 'mjknQueues', 'history'
        ));
    }

    public function searchPatient(Request $request): JsonResponse
    {
        $keyword = $request->get('keyword');

        if (!$keyword || strlen($keyword) < 3) {
            return response()->json(['found' => false, 'message' => 'Minimal 3 karakter']);
        }

        $patients = Patient::where(function ($q) use ($keyword) {
            $q->where('nik', 'like', "%{$keyword}%")
              ->orWhere('no_rm', 'like', "%{$keyword}%")
              ->orWhere('name', 'like', "%{$keyword}%")
              ->orWhere('phone', 'like', "%{$keyword}%");
        })->take(10)->get();

        if ($patients->isEmpty()) {
            return response()->json(['found' => false, 'message' => 'Pasien tidak ditemukan']);
        }

        return response()->json([
            'found' => true,
            'data' => $patients->map(fn ($p) => [
                'id' => $p->id,
                'no_rm' => $p->no_rm,
                'nik' => $p->nik,
                'name' => $p->name,
                'birth_date' => $p->birth_date?->format('Y-m-d'),
                'gender' => $p->gender,
                'phone' => $p->phone,
                'address' => $p->address,
                'insurance_type' => $p->insurance_type,
                'insurance_number' => $p->insurance_number,
                'occupation' => $p->occupation,
                'education' => $p->education,
                'marriage_status' => $p->marriage_status,
                'mother_name' => $p->mother_name,
                'emergency_contact' => $p->emergency_contact,
                'allergy' => $p->allergy,
                'religion' => $p->religion,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'patient_id' => 'nullable|exists:patients,id',
            'nik' => 'nullable|required_without:patient_id|string|size:16',
            'name' => 'nullable|required_without:patient_id|string|max:255',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|required_without:patient_id|date',
            'gender' => 'nullable|required_without:patient_id|in:L,P',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:100',
            'education' => 'nullable|in:SD,SMP,SMA,D1,D2,D3,S1,S2,S3',
            'marriage_status' => 'nullable|in:Belum Kawin,Kawin,Cerai',
            'mother_name' => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:200',
            'allergy' => 'nullable|string',
            'religion' => 'nullable|string|max:50',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'insurance_type' => 'required|string|in:Umum,BPJS,Asuransi Lain',
            'insurance_number' => 'nullable|required_if:insurance_type,BPJS|string|max:50',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'source' => 'required|in:walk_in,mjkn',
            'bpjs_antrian_id' => 'nullable|string|max:50',
            'no_sep' => 'nullable|string|max:50',
            'visit_type' => 'nullable|in:Baru,Lama,Kontrol,Rujukan',
            'notes' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        try {
            if (!empty($validated['patient_id'])) {
                $patient = Patient::findOrFail($validated['patient_id']);
            } else {
                $existing = $this->patientService->findByNik($validated['nik']);
                if ($existing) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Pasien dengan NIK ' . $validated['nik'] . ' sudah terdaftar sebagai ' . $existing->name . ' (RM: ' . $existing->no_rm . '). Gunakan pencarian untuk memilih pasien yang sudah ada.');
                }

                $patientData = [
                    'nik' => $validated['nik'],
                    'name' => $validated['name'],
                    'birth_place' => $validated['birth_place'] ?? null,
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'occupation' => $validated['occupation'] ?? null,
                    'education' => $validated['education'] ?? null,
                    'marriage_status' => $validated['marriage_status'] ?? null,
                    'mother_name' => $validated['mother_name'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'allergy' => $validated['allergy'] ?? null,
                    'religion' => $validated['religion'] ?? null,
                    'blood_type' => $validated['blood_type'] ?? null,
                    'insurance_type' => $validated['insurance_type'],
                    'insurance_number' => $validated['insurance_number'] ?? null,
                ];

                $patient = $this->patientService->register($patientData);
            }

            $polyclinic = Polyclinic::findOrFail($validated['polyclinic_id']);
            $doctor = ($validated['doctor_id'] ?? null) ? Doctor::find($validated['doctor_id']) : null;

            $result = $this->queueService->registerQueue(
                $patient,
                $polyclinic,
                $doctor,
                $validated['source'],
                $validated['bpjs_antrian_id'] ?? null,
                $validated['no_sep'] ?? null,
                $validated['visit_type'] ?? null,
            );

            return redirect()->route('registration.index')
                ->with('success', 'Pasien berhasil didaftarkan. No. Registrasi: ' . $result['registration']->registration_number . ', Antrean: ' . $result['queue']->queue_number)
                ->with('queue_id', $result['queue']->id);
        } catch (\Exception $e) {
            Log::error('Gagal mendaftarkan pasien: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mendaftarkan: ' . $e->getMessage());
        }
    }

    public function checkin(Request $request, $bpjsAntreanId): RedirectResponse
    {
        try {
            $bpjsAntrean = \App\Models\BpjsAntrean::findOrFail($bpjsAntreanId);
            $patient = $bpjsAntrean->patient;

            if (!$patient) {
                return redirect()->back()->with('error', 'Data pasien tidak ditemukan');
            }

            $polyclinic = Polyclinic::where('code', $bpjsAntrean->kode_poli)->first();
            if (!$polyclinic) {
                return redirect()->back()->with('error', 'Poli tidak ditemukan');
            }

            $result = $this->queueService->registerQueue(
                $patient,
                $polyclinic,
                null,
                'mjkn',
                $bpjsAntrean->id,
                $bpjsAntrean->nomor_sep,
            );

            $bpjsAntrean->update([
                'queue_id' => $result['queue']->id,
                'status' => 'confirmed',
            ]);

            return redirect()->route('registration.index')
                ->with('success', 'Pasien MJKN berhasil check-in. Antrean: ' . $result['queue']->queue_number)
                ->with('queue_id', $result['queue']->id);
        } catch (\Exception $e) {
            Log::error('Gagal check-in MJKN: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal check-in: ' . $e->getMessage());
        }
    }

    public function printTicket(Queue $queue)
    {
        $queue->load(['registration.patient', 'polyclinic']);

        return view('registration.ticket', compact('queue'));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Services\PatientService;
use App\Services\QueueService;
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

        return view('registration.index', compact('polyclinics', 'doctors'));
    }

    public function searchPatient(Request $request): JsonResponse
    {
        $nik = $request->get('nik');

        if (!$nik || strlen($nik) < 4) {
            return response()->json(['found' => false, 'message' => 'NIK minimal 4 karakter']);
        }

        $patient = $this->patientService->findByNik($nik);

        if (!$patient) {
            return response()->json(['found' => false, 'message' => 'Pasien tidak ditemukan']);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'id' => $patient->id,
                'no_rm' => $patient->no_rm,
                'nik' => $patient->nik,
                'name' => $patient->name,
                'birth_date' => $patient->birth_date?->format('Y-m-d'),
                'gender' => $patient->gender,
                'phone' => $patient->phone,
                'address' => $patient->address,
                'insurance_type' => $patient->insurance_type,
                'insurance_number' => $patient->insurance_number,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'patient_id' => 'nullable|exists:patients,id',
            'nik' => 'required_without:patient_id|string|size:16',
            'name' => 'required_without:patient_id|string|max:255',
            'birth_date' => 'required_without:patient_id|date',
            'gender' => 'required_without:patient_id|in:L,P',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'insurance_type' => 'required|string|in:Umum,BPJS,Asuransi Lain',
            'insurance_number' => 'required_if:insurance_type,BPJS|string|max:50',
            'polyclinic_id' => 'required|exists:polyclinics,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'service_type' => 'required|string|in:umum,BPJS,Asuransi',
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
                    'birth_date' => $validated['birth_date'],
                    'gender' => $validated['gender'],
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'insurance_type' => $validated['insurance_type'],
                    'insurance_number' => $validated['insurance_number'] ?? null,
                ];

                $patient = $this->patientService->register($patientData);
            }

            $polyclinic = Polyclinic::findOrFail($validated['polyclinic_id']);
            $doctor = ($validated['doctor_id'] ?? null) ? Doctor::find($validated['doctor_id']) : null;

            $queue = $this->queueService->registerQueue(
                $patient,
                $polyclinic,
                $doctor,
                $validated['service_type']
            );

            return redirect()->route('registration.index')
                ->with('success', 'Pasien berhasil didaftarkan. Nomor antrean: ' . $queue->queue_number);
        } catch (\Exception $e) {
            Log::error('Gagal mendaftarkan pasien: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mendaftarkan: ' . $e->getMessage());
        }
    }
}

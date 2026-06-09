<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRequest;
use App\Http\Resources\PatientCollection;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Patient::with(['bpjsPatient', 'creator']);

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('nik', 'like', "%{$keyword}%")
                        ->orWhere('no_rm', 'like', "%{$keyword}%");
                });
            }

            $patients = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => new PatientCollection($patients),
                'message' => 'Daftar pasien berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PatientRequest $request): JsonResponse
    {
        try {
            $patient = $this->patientService->register($request->validated());

            return response()->json([
                'success' => true,
                'data' => new PatientResource($patient->load(['bpjsPatient'])),
                'message' => 'Pasien berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Patient $patient): JsonResponse
    {
        try {
            $patient->load(['bpjsPatient', 'creator']);

            if ($patient->insurance_type === 'BPJS' && $patient->insurance_number) {
                try {
                    $this->patientService->checkBpjsStatus($patient);
                    $patient->load(['bpjsPatient']);
                } catch (\Exception $e) {
                    Log::warning('Gagal memperbarui status BPJS: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => new PatientResource($patient),
                'message' => 'Detail pasien berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PatientRequest $request, Patient $patient): JsonResponse
    {
        try {
            $patient->update($request->validated());

            if ($patient->wasChanged('insurance_number') && $patient->insurance_type === 'BPJS') {
                try {
                    $this->patientService->checkBpjsStatus($patient);
                } catch (\Exception $e) {
                    Log::warning('Gagal memperbarui status BPJS: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => new PatientResource($patient->fresh()->load(['bpjsPatient'])),
                'message' => 'Data pasien berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Patient $patient): JsonResponse
    {
        try {
            $patient->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Pasien berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate(['keyword' => 'required|string|min:2']);

            $patients = $this->patientService->search($request->keyword);

            return response()->json([
                'success' => true,
                'data' => new PatientCollection($patients),
                'message' => 'Hasil pencarian pasien',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencari pasien: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari pasien',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function visitHistory(Patient $patient): JsonResponse
    {
        try {
            $history = $this->patientService->getVisitHistory($patient);

            return response()->json([
                'success' => true,
                'data' => $history,
                'message' => 'Riwayat kunjungan pasien berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat riwayat kunjungan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat kunjungan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bpjsStatus(Patient $patient): JsonResponse
    {
        try {
            if ($patient->insurance_type !== 'BPJS') {
                return response()->json([
                    'success' => true,
                    'data' => ['bpjs_status' => null],
                    'message' => 'Pasien bukan peserta BPJS',
                ]);
            }

            $status = $this->patientService->checkBpjsStatus($patient);

            return response()->json([
                'success' => true,
                'data' => [
                    'bpjs_status' => $patient->bpjs_status,
                    'bpjs_patient' => $patient->bpjsPatient,
                    'raw_response' => $status,
                ],
                'message' => 'Status BPJS berhasil diperiksa',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memeriksa status BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalRecordRequest;
use App\Http\Resources\MedicalRecordResource;
use App\Models\MedicalRecord;
use App\Services\MedicalRecordService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MedicalRecordController extends Controller
{
    protected MedicalRecordService $medicalRecordService;

    public function __construct(MedicalRecordService $medicalRecordService)
    {
        $this->medicalRecordService = $medicalRecordService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = MedicalRecord::with(['patient', 'doctor', 'polyclinic', 'prescriptions']);

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            if ($request->filled('polyclinic_id')) {
                $query->where('polyclinic_id', $request->polyclinic_id);
            }

            if ($request->filled('date_from')) {
                $query->where('visit_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('visit_date', '<=', $request->date_to);
            }

            if ($request->filled('visit_type')) {
                $query->where('visit_type', $request->visit_type);
            }

            $records = $query->orderBy('visit_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => MedicalRecordResource::collection($records),
                'message' => 'Daftar rekam medis berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat rekam medis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat rekam medis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(MedicalRecordRequest $request): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->createRecord($request->validated());

            return response()->json([
                'success' => true,
                'data' => new MedicalRecordResource(
                    $this->medicalRecordService->getRecordWithRelations($record)
                ),
                'message' => 'Rekam medis berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan rekam medis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan rekam medis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->getRecordWithRelations($medicalRecord);
            $vitalSigns = $this->medicalRecordService->getVitalSigns($medicalRecord);

            return response()->json([
                'success' => true,
                'data' => new MedicalRecordResource($record),
                'meta' => [
                    'vital_signs_parsed' => $vitalSigns,
                ],
                'message' => 'Detail rekam medis berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail rekam medis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail rekam medis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(MedicalRecordRequest $request, MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->updateRecord($medicalRecord, $request->validated());

            return response()->json([
                'success' => true,
                'data' => new MedicalRecordResource(
                    $this->medicalRecordService->getRecordWithRelations($record)
                ),
                'message' => 'Rekam medis berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui rekam medis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui rekam medis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $medicalRecord->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Rekam medis berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus rekam medis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus rekam medis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPrescription(Request $request, MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.medicine_id' => 'required|exists:medicines,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit' => 'nullable|string|max:50',
                'items.*.dosage' => 'nullable|array',
                'items.*.subtotal' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $prescription = $this->medicalRecordService->createPrescription(
                $medicalRecord,
                $validated
            );

            return response()->json([
                'success' => true,
                'data' => $prescription,
                'message' => 'Resep berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan resep: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitSatusehat(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->getRecordWithRelations($medicalRecord);
            $result = $this->medicalRecordService->submitToSatusehat($record);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Data berhasil dikirim ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim ke Satu Sehat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim data ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitBpjs(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->getRecordWithRelations($medicalRecord);
            $result = $this->medicalRecordService->submitToBpjs($record);

            if ($result === null && $medicalRecord->patient->insurance_type !== 'BPJS') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pasien bukan peserta BPJS',
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Klaim BPJS berhasil dikirim',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim klaim BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim klaim BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

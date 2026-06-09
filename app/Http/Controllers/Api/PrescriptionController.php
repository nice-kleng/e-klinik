<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrescriptionRequest;
use App\Http\Resources\PrescriptionResource;
use App\Models\Prescription;
use App\Services\MedicalRecordService;
use App\Services\SatuSehat\MedicationRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    protected MedicalRecordService $medicalRecordService;
    protected MedicationRequestService $medicationRequestService;

    public function __construct(
        MedicalRecordService $medicalRecordService,
        MedicationRequestService $medicationRequestService
    ) {
        $this->medicalRecordService = $medicalRecordService;
        $this->medicationRequestService = $medicationRequestService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Prescription::with(['patient', 'doctor', 'medicalRecord', 'items.medicine']);

            if ($request->filled('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            if ($request->filled('doctor_id')) {
                $query->where('doctor_id', $request->doctor_id);
            }

            if ($request->filled('medical_record_id')) {
                $query->where('medical_record_id', $request->medical_record_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('prescription_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('prescription_date', '<=', $request->date_to);
            }

            $prescriptions = $query->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 15);

            return response()->json([
                'success' => true,
                'data' => PrescriptionResource::collection($prescriptions),
                'message' => 'Daftar resep berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat daftar resep: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(PrescriptionRequest $request): JsonResponse
    {
        try {
            $medicalRecord = \App\Models\MedicalRecord::findOrFail($request->medical_record_id);

            $prescription = $this->medicalRecordService->createPrescription(
                $medicalRecord,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'data' => new PrescriptionResource(
                    $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine'])
                ),
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

    public function show(Prescription $prescription): JsonResponse
    {
        try {
            $prescription->load(['patient', 'doctor', 'medicalRecord', 'items.medicine', 'creator']);

            return response()->json([
                'success' => true,
                'data' => new PrescriptionResource($prescription),
                'message' => 'Detail resep berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail resep: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(PrescriptionRequest $request, Prescription $prescription): JsonResponse
    {
        try {
            $prescription->update($request->validated());

            if ($request->has('items')) {
                $prescription->items()->delete();

                foreach ($request->items as $item) {
                    $prescription->items()->create([
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'dosage' => $item['dosage'] ?? null,
                        'subtotal' => $item['subtotal'] ?? 0,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => new PrescriptionResource(
                    $prescription->fresh()->load(['patient', 'doctor', 'medicalRecord', 'items.medicine'])
                ),
                'message' => 'Resep berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui resep: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Prescription $prescription): JsonResponse
    {
        try {
            $prescription->items()->delete();
            $prescription->delete();

            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Resep berhasil dihapus',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus resep: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function print(Prescription $prescription): JsonResponse
    {
        try {
            $prescription->load(['patient', 'doctor', 'items.medicine', 'medicalRecord']);

            $printData = [
                'prescription_number' => $prescription->prescription_number,
                'prescription_date' => $prescription->prescription_date->format('d/m/Y'),
                'patient' => [
                    'name' => $prescription->patient->name,
                    'nik' => $prescription->patient->nik,
                    'no_rm' => $prescription->patient->no_rm,
                    'birth_date' => $prescription->patient->birth_date?->format('d/m/Y'),
                    'address' => $prescription->patient->address,
                ],
                'doctor' => [
                    'name' => $prescription->doctor->name,
                    'sip_number' => $prescription->doctor->sip_number,
                ],
                'items' => $prescription->items->map(fn ($item) => [
                    'medicine_name' => $item->medicine?->name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'dosage' => $item->dosage,
                    'subtotal' => $item->subtotal,
                ]),
                'notes' => $prescription->notes,
            ];

            return response()->json([
                'success' => true,
                'data' => $printData,
                'message' => 'Data cetak resep berhasil disiapkan',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menyiapkan data cetak: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan data cetak resep',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function submitSatusehat(Prescription $prescription): JsonResponse
    {
        try {
            $prescription->load(['patient', 'doctor', 'items.medicine']);

            $result = $this->medicationRequestService->createMedicationRequest($prescription);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Resep berhasil dikirim ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim resep ke Satu Sehat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim resep ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

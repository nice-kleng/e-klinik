<?php

namespace App\Http\Controllers\Api\SatuSehat;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\MedicalRecordService;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\MedicationRequestService;
use App\Services\SatuSehat\ObservationService;
use App\Services\SatuSehat\PatientService as SatuSehatPatientService;
use App\Services\SatuSehat\TerminologyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FHIRController extends Controller
{
    protected SatuSehatPatientService $satuSehatPatientService;
    protected EncounterService $encounterService;
    protected ConditionService $conditionService;
    protected ObservationService $observationService;
    protected MedicationRequestService $medicationRequestService;
    protected MedicalRecordService $medicalRecordService;
    protected TerminologyService $terminologyService;

    public function __construct(
        SatuSehatPatientService $satuSehatPatientService,
        EncounterService $encounterService,
        ConditionService $conditionService,
        ObservationService $observationService,
        MedicationRequestService $medicationRequestService,
        MedicalRecordService $medicalRecordService,
        TerminologyService $terminologyService
    ) {
        $this->satuSehatPatientService = $satuSehatPatientService;
        $this->encounterService = $encounterService;
        $this->conditionService = $conditionService;
        $this->observationService = $observationService;
        $this->medicationRequestService = $medicationRequestService;
        $this->medicalRecordService = $medicalRecordService;
        $this->terminologyService = $terminologyService;
    }

    public function syncPatient(Patient $patient): JsonResponse
    {
        try {
            $result = $this->satuSehatPatientService->syncPatient($patient);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Pasien berhasil disinkronkan ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi pasien ke Satu Sehat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi pasien ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncEncounter(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->getRecordWithRelations($medicalRecord);
            $result = $this->encounterService->createEncounter($record);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Encounter berhasil disinkronkan ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi encounter: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi encounter ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncCondition(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $results = [];

            if ($medicalRecord->diagnosis_primary) {
                $primaryResult = $this->conditionService->createCondition(
                    $medicalRecord->patient,
                    $medicalRecord->diagnosis_primary,
                    'primary',
                    $medicalRecord
                );
                $results['primary'] = $primaryResult;
            }

            if (!empty($medicalRecord->diagnosis_secondary)) {
                foreach ($medicalRecord->diagnosis_secondary as $index => $code) {
                    $secondaryResult = $this->conditionService->createCondition(
                        $medicalRecord->patient,
                        $code,
                        'secondary',
                        $medicalRecord
                    );
                    $results['secondary_' . $index] = $secondaryResult;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => 'Kondisi diagnosis berhasil disinkronkan ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi kondisi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi kondisi ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncObservation(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            if (!$medicalRecord->vital_signs) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data tanda vital untuk disinkronkan',
                ], 400);
            }

            $result = $this->observationService->createObservations($medicalRecord);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Observasi tanda vital berhasil disinkronkan ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi observasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi observasi ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncMedicationRequest(Prescription $prescription): JsonResponse
    {
        try {
            $prescription->load(['patient', 'doctor', 'items.medicine']);
            $result = $this->medicationRequestService->createMedicationRequest($prescription);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Resep obat berhasil disinkronkan ke Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi resep obat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi resep obat ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchPatient(Request $request): JsonResponse
    {
        try {
            $request->validate(['identifier' => 'required|string']);

            $result = $this->satuSehatPatientService->searchPatient($request->identifier);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Pencarian pasien di Satu Sehat berhasil',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencari pasien di Satu Sehat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari pasien di Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchIcd10(Request $request): JsonResponse
    {
        try {
            $request->validate(['keyword' => 'required|string|min:2']);

            $result = $this->terminologyService->searchIcd10($request->keyword);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Pencarian ICD-10 di Satu Sehat berhasil',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencari ICD-10: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari ICD-10 di Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchLoinc(Request $request): JsonResponse
    {
        try {
            $request->validate(['keyword' => 'required|string|min:2']);

            $result = $this->terminologyService->searchLoinc($request->keyword);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Pencarian LOINC di Satu Sehat berhasil',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencari LOINC: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari LOINC di Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function status(): JsonResponse
    {
        try {
            $stats = [
                'patients_synced' => \App\Models\SatusehatResource::where('model_type', Patient::class)->count(),
                'encounters_synced' => \App\Models\SatusehatResource::where('resource_type', 'Encounter')->count(),
                'conditions_synced' => \App\Models\SatusehatResource::where('resource_type', 'Condition')->count(),
                'observations_synced' => \App\Models\SatusehatResource::where('resource_type', 'Observation')->count(),
                'medication_requests_synced' => \App\Models\SatusehatResource::where('resource_type', 'MedicationRequest')->count(),
                'last_sync' => \App\Models\SatusehatLog::orderBy('synced_at', 'desc')->first()?->synced_at,
                'failed_syncs' => \App\Models\SatusehatLog::where('status', 'error')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Status sinkronasi Satu Sehat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat status sinkronasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status sinkronasi Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncAll(MedicalRecord $medicalRecord): JsonResponse
    {
        try {
            $record = $this->medicalRecordService->getRecordWithRelations($medicalRecord);
            $results = $this->medicalRecordService->submitToSatusehat($record);

            $syncedCount = collect($results)->filter(fn ($r) => !isset($r['error']))->count();
            $failedCount = collect($results)->filter(fn ($r) => isset($r['error']))->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'summary' => [
                        'total' => count($results),
                        'synced' => $syncedCount,
                        'failed' => $failedCount,
                    ],
                ],
                'message' => "Sinkronasi Satu Sehat selesai: {$syncedCount} berhasil, {$failedCount} gagal",
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal sinkronasi semua data ke Satu Sehat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronasi data ke Satu Sehat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

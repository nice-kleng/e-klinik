<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Icd10Diagnosis;
use App\Services\Icd10Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiagnosisController extends Controller
{
    protected Icd10Service $icd10Service;

    public function __construct(Icd10Service $icd10Service)
    {
        $this->icd10Service = $icd10Service;
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate(['keyword' => 'required|string|min:2']);

            $diagnoses = $this->icd10Service->search($request->keyword);

            return response()->json([
                'success' => true,
                'data' => $diagnoses->items(),
                'meta' => [
                    'current_page' => $diagnoses->currentPage(),
                    'last_page' => $diagnoses->lastPage(),
                    'per_page' => $diagnoses->perPage(),
                    'total' => $diagnoses->total(),
                ],
                'message' => 'Hasil pencarian diagnosis ICD-10',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mencari diagnosis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari diagnosis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Icd10Diagnosis $icd10Diagnosis): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $icd10Diagnosis,
                'message' => 'Detail diagnosis berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail diagnosis: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail diagnosis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->icd10Service->getCategoryList();

            return response()->json([
                'success' => true,
                'data' => $categories,
                'message' => 'Daftar kategori diagnosis',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat kategori: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat kategori diagnosis',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function common(): JsonResponse
    {
        try {
            $diagnoses = $this->icd10Service->getCommonDiagnoses();

            return response()->json([
                'success' => true,
                'data' => $diagnoses,
                'message' => 'Diagnosis yang sering digunakan',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat diagnosis umum: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat diagnosis umum',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

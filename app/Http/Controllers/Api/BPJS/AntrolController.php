<?php

namespace App\Http\Controllers\Api\BPJS;

use App\Http\Controllers\Controller;
use App\Services\BPJS\AntrolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AntrolController extends Controller
{
    protected AntrolService $antrolService;

    public function __construct(AntrolService $antrolService)
    {
        $this->antrolService = $antrolService;
    }

    public function addAntrean(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'noKartu' => 'required|string',
                'nik' => 'nullable|string',
                'noRm' => 'nullable|string',
                'kodePoli' => 'required|string',
                'kodeDokter' => 'nullable|string',
                'noAntrean' => 'required|string',
                'tanggal' => 'required|date',
                'jamPendaftaran' => 'required|string',
            ]);

            $response = $this->antrolService->addAntrean($validated);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Antrean BPJS berhasil ditambahkan',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan antrean BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan antrean BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAntrean(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'noKartu' => 'required|string',
                'kodePoli' => 'required|string',
                'noAntrean' => 'required|string',
                'tanggal' => 'required|date',
            ]);

            $response = $this->antrolService->updateAntrean($validated);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Antrean BPJS berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui antrean BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui antrean BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAntrean(Request $request): JsonResponse
    {
        try {
            $request->validate(['no_antrean' => 'required|string']);

            $response = $this->antrolService->deleteAntrean($request->no_antrean);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Antrean BPJS berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus antrean BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus antrean BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAntreanPoli(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'kode_poli' => 'required|string',
                'tanggal' => 'required|date',
            ]);

            $response = $this->antrolService->getAntreanPerPoli(
                $request->kode_poli,
                $request->tanggal
            );

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Data antrean poli berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat antrean poli: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data antrean poli',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dashboardTanggal(Request $request): JsonResponse
    {
        try {
            $request->validate(['tanggal' => 'required|date']);

            $response = $this->antrolService->getDashboardPerTanggal($request->tanggal);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Dashboard harian BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat dashboard harian: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat dashboard harian BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function dashboardBulan(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'bulan' => 'required|numeric|between:1,12',
                'tahun' => 'required|numeric|digits:4',
            ]);

            $response = $this->antrolService->getDashboardPerBulan(
                $request->bulan,
                $request->tahun
            );

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Dashboard bulanan BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat dashboard bulanan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat dashboard bulanan BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

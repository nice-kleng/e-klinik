<?php

namespace App\Http\Controllers\Api\BPJS;

use App\Http\Controllers\Controller;
use App\Services\BPJS\VClaimService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VClaimController extends Controller
{
    protected VClaimService $vClaimService;

    public function __construct(VClaimService $vClaimService)
    {
        $this->vClaimService = $vClaimService;
    }

    public function peserta(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'no_kartu' => 'required_without:nik|string',
                'nik' => 'required_without:no_kartu|string',
                'tgl_pelayanan' => 'required|date',
            ]);

            $tglPelayanan = $request->tgl_pelayanan;

            if ($request->filled('no_kartu')) {
                $response = $this->vClaimService->getPeserta($request->no_kartu, $tglPelayanan);
            } else {
                $response = $this->vClaimService->getPesertaByNik($request->nik, $tglPelayanan);
            }

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Data peserta BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat data peserta BPJS: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data peserta BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sepStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'noKartu' => 'required|string',
                'tglPelayanan' => 'required|date',
                'kodePoli' => 'required|string',
                'kodeDokter' => 'required|string',
                'diagnosa' => 'required|string',
                'noRujukan' => 'nullable|string',
                'catatan' => 'nullable|string',
            ]);

            $response = $this->vClaimService->insertSep($validated);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'SEP berhasil dibuat',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal membuat SEP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat SEP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sepShow(string $noSep): JsonResponse
    {
        try {
            $response = $this->vClaimService->getSep($noSep);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'SEP tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Detail SEP berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat detail SEP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail SEP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sepUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'noSep' => 'required|string',
                'noKartu' => 'required|string',
                'tglPelayanan' => 'required|date',
                'kodePoli' => 'required|string',
                'kodeDokter' => 'required|string',
                'diagnosa' => 'required|string',
            ]);

            $response = $this->vClaimService->updateSep($validated);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'SEP berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui SEP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui SEP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sepDelete(Request $request): JsonResponse
    {
        try {
            $request->validate(['no_sep' => 'required|string']);

            $response = $this->vClaimService->deleteSep($request->no_sep);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'SEP berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menghapus SEP: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus SEP',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function claimStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'noKartu' => 'required|string',
                'tglPelayanan' => 'required|date',
                'diagnosa' => 'required|string',
                'poli' => 'required|string',
                'noSep' => 'nullable|string',
                'prosedur' => 'nullable|string',
                'tarif' => 'nullable|numeric',
            ]);

            $response = $this->vClaimService->submitClaim($validated);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Klaim berhasil dikirim',
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim klaim: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim klaim BPJS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function claimStatus(string $noSep): JsonResponse
    {
        try {
            $response = $this->vClaimService->getClaimStatus($noSep);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Status klaim berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat status klaim: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat status klaim',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function referensiDiagnosa(Request $request): JsonResponse
    {
        try {
            $request->validate(['keyword' => 'required|string|min:2']);

            $response = $this->vClaimService->getDiagnosa($request->keyword);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Referensi diagnosa BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat referensi diagnosa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat referensi diagnosa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function referensiPoli(Request $request): JsonResponse
    {
        try {
            $request->validate(['kode_poli' => 'required|string']);

            $response = $this->vClaimService->getPoli($request->kode_poli);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Referensi poli BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat referensi poli: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat referensi poli',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function referensiFaskes(Request $request): JsonResponse
    {
        try {
            $request->validate(['kode_faskes' => 'required|string']);

            $response = $this->vClaimService->getFaskes($request->kode_faskes);

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Referensi faskes BPJS berhasil dimuat',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memuat referensi faskes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat referensi faskes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

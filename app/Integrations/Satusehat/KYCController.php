<?php

namespace App\Integrations\Satusehat;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KYCController
{
    protected string $kycEndpoint;

    public function __construct()
    {
        $this->kycEndpoint = config('satusehat.kyc_endpoint');
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|string',
            'organization_name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
            ])->post($this->kycEndpoint . '/register', $request->all());

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Registrasi KYC berhasil diajukan',
                ]);
            }

            Log::error('KYC registration failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan registrasi KYC',
                'error' => $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('KYC registration exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengajukan registrasi KYC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate(['organization_id' => 'required|string']);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
            ])->get($this->kycEndpoint . '/status', [
                'organization_id' => $request->organization_id,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Status KYC berhasil diperiksa',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status KYC',
                'error' => $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('KYC status check exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status KYC',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'organization_id' => 'required|string',
            'verification_code' => 'required|string',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getToken(),
            ])->post($this->kycEndpoint . '/verify', $request->all());

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json(),
                    'message' => 'Verifikasi organisasi berhasil',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi organisasi',
                'error' => $response->body(),
            ], $response->status());
        } catch (\Exception $e) {
            Log::error('KYC verification exception: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memverifikasi organisasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function getToken(): string
    {
        $authUrl = config('satusehat.auth_url');
        $clientId = config('satusehat.client_id');
        $clientSecret = config('satusehat.client_secret');

        $response = Http::asForm()->post($authUrl . '/accesstoken', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        return $response->json('access_token', '');
    }
}

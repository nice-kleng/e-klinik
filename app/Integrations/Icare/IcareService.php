<?php

namespace App\Integrations\Icare;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IcareService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $faskesCode;

    public function __construct()
    {
        $this->baseUrl = config('services.icare.base_url', '');
        $this->apiKey = config('services.icare.api_key', '');
        $this->faskesCode = config('services.icare.faskes_code', '');
    }

    public function getPatientHistory(string $nik): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'X-Faskes-Code' => $this->faskesCode,
            ])->get($this->baseUrl . '/patient/history', [
                'nik' => $nik,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('iCare patient history failed', [
                'nik' => $nik,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iCare patient history exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getMedicationHistory(string $nik): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'X-Faskes-Code' => $this->faskesCode,
            ])->get($this->baseUrl . '/patient/medication', [
                'nik' => $nik,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('iCare medication history failed', [
                'nik' => $nik,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iCare medication history exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getDiagnosisHistory(string $nik): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'X-Faskes-Code' => $this->faskesCode,
            ])->get($this->baseUrl . '/patient/diagnosis', [
                'nik' => $nik,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('iCare diagnosis history failed', [
                'nik' => $nik,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iCare diagnosis history exception: ' . $e->getMessage());
            return null;
        }
    }
}

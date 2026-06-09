<?php

namespace App\Services;

use App\Models\BpjsSep;
use App\Models\Patient;
use App\Models\Queue;
use App\Services\BPJS\VClaimService;
use Illuminate\Support\Facades\Log;

class BpjsSepService
{
    protected VClaimService $vClaimService;

    public function __construct(?VClaimService $vClaimService = null)
    {
        $this->vClaimService = $vClaimService ?? app(VClaimService::class);
    }

    public function createFromQueue(Queue $queue): ?BpjsSep
    {
        $patient = $queue->patient;

        if ($patient->insurance_type !== 'BPJS') {
            return null;
        }

        $bpjsPatient = $patient->bpjsPatient;
        if (!$bpjsPatient || !$bpjsPatient->no_kartu) {
            Log::warning('Gagal buat SEP: pasien BPJS tidak punya no kartu', [
                'queue_id' => $queue->id,
                'patient_id' => $patient->id,
            ]);
            return null;
        }

        $data = [
            'noKartu' => $bpjsPatient->no_kartu,
            'tglPelayanan' => $queue->queue_date->format('Y-m-d'),
            'kodePoli' => $queue->polyclinic->code,
            'kodeDokter' => $queue->doctor?->code ?? '',
            'diagnosa' => '',
            'catatan' => 'Dibuat otomatis saat pendaftaran antrean',
        ];

        try {
            $response = $this->vClaimService->insertSep($data);

            if ($response && isset($response['sep']['noSep'])) {
                $noSep = $response['sep']['noSep'];

                $sep = BpjsSep::create([
                    'patient_id' => $patient->id,
                    'queue_id' => $queue->id,
                    'no_sep' => $noSep,
                    'no_kartu' => $bpjsPatient->no_kartu,
                    'tgl_pelayanan' => $queue->queue_date,
                    'kode_poli' => $queue->polyclinic->code,
                    'kode_dokter' => $queue->doctor?->code ?? '',
                    'diagnosa' => '',
                    'catatan' => 'Dibuat otomatis',
                    'response_raw' => $response,
                    'created_by' => auth()->id(),
                ]);

                $queue->update(['bpjs_sep_id' => $noSep]);

                Log::info('SEP berhasil dibuat otomatis dari antrean', [
                    'queue_id' => $queue->id,
                    'no_sep' => $noSep,
                ]);

                return $sep;
            }

            Log::warning('Response SEP tidak mengandung noSep', [
                'queue_id' => $queue->id,
                'response' => $response,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Gagal membuat SEP dari antrean: ' . $e->getMessage(), [
                'queue_id' => $queue->id,
            ]);
            return null;
        }
    }

    public function updateSep(BpjsSep $sep, array $data): ?BpjsSep
    {
        try {
            $response = $this->vClaimService->updateSep(array_merge(
                ['noSep' => $sep->no_sep],
                $data
            ));

            $sep->update([
                'kode_poli' => $data['kodePoli'] ?? $sep->kode_poli,
                'kode_dokter' => $data['kodeDokter'] ?? $sep->kode_dokter,
                'diagnosa' => $data['diagnosa'] ?? $sep->diagnosa,
                'response_raw' => $response,
            ]);

            return $sep->fresh();
        } catch (\Exception $e) {
            Log::error('Gagal update SEP: ' . $e->getMessage(), [
                'no_sep' => $sep->no_sep,
            ]);
            throw $e;
        }
    }

    public function deleteSep(BpjsSep $sep): bool
    {
        try {
            $this->vClaimService->deleteSep($sep->no_sep);

            $sep->update([
                'status' => 'deleted',
                'response_raw' => array_merge($sep->response_raw ?? [], ['deleted_at' => now()->toDateTimeString()]),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Gagal hapus SEP: ' . $e->getMessage(), [
                'no_sep' => $sep->no_sep,
            ]);
            throw $e;
        }
    }

    public function getSep(string $noSep): ?array
    {
        try {
            return $this->vClaimService->getSep($noSep);
        } catch (\Exception $e) {
            Log::error('Gagal get SEP: ' . $e->getMessage(), [
                'no_sep' => $noSep,
            ]);
            return null;
        }
    }
}

<?php

namespace App\Services;

use App\Models\BpjsPatient;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Services\BPJS\VClaimService;
use App\Services\SatuSehat\PatientService as SatuSehatPatientService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientService
{
    protected VClaimService $vClaimService;
    protected SatuSehatPatientService $satuSehatPatientService;

    public function __construct(
        ?VClaimService $vClaimService = null,
        ?SatuSehatPatientService $satuSehatPatientService = null
    ) {
        $this->vClaimService = $vClaimService ?? app(VClaimService::class);
        $this->satuSehatPatientService = $satuSehatPatientService ?? app(SatuSehatPatientService::class);
    }

    public function generateRmNumber(): string
    {
        $datePrefix = 'RM-' . now()->format('Ymd') . '-';

        $lastPatient = Patient::where('no_rm', 'like', "{$datePrefix}%")
            ->orderBy('no_rm', 'desc')
            ->first();

        if ($lastPatient && preg_match('/-(\d{4})$/', $lastPatient->no_rm, $matches)) {
            $sequence = (int) $matches[1] + 1;
        } else {
            $sequence = 1;
        }

        return $datePrefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function register(array $data): Patient
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['no_rm'])) {
                $data['no_rm'] = $this->generateRmNumber();
            }

            $data['created_by'] = $data['created_by'] ?? auth()->id();

            if (isset($data['birth_date']) && is_string($data['birth_date'])) {
                $data['birth_date'] = Carbon::parse($data['birth_date'])->toDateString();
            }

            $patient = Patient::create($data);

            if ($patient->insurance_type === 'BPJS' && !empty($data['insurance_number'])) {
                BpjsPatient::create([
                    'patient_id' => $patient->id,
                    'no_kartu' => $data['insurance_number'],
                    'status' => 'active',
                ]);

                try {
                    $this->checkBpjsStatus($patient);
                } catch (\Exception $e) {
                    Log::warning('Failed to check BPJS status on patient registration', [
                        'patient_id' => $patient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            try {
                $this->satuSehatPatientService->syncPatient($patient);
            } catch (\Exception $e) {
                Log::warning('Failed to sync patient to Satu Sehat on registration', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $patient;
        });
    }

    public function findByNik(string $nik): ?Patient
    {
        return Patient::with(['bpjsPatient'])
            ->where('nik', $nik)
            ->first();
    }

    public function findByRmNumber(string $rmNumber): ?Patient
    {
        return Patient::with(['bpjsPatient'])
            ->where('no_rm', $rmNumber)
            ->first();
    }

    public function getVisitHistory(Patient $patient, int $limit = 10): Collection
    {
        return MedicalRecord::with(['doctor', 'polyclinic', 'diagnosisIcd10', 'prescriptions'])
            ->where('patient_id', $patient->id)
            ->orderBy('visit_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function search(string $keyword): LengthAwarePaginator
    {
        return Patient::where(function ($query) use ($keyword) {
            $query->where('name', 'like', "%{$keyword}%")
                ->orWhere('nik', 'like', "%{$keyword}%")
                ->orWhere('no_rm', 'like', "%{$keyword}%")
                ->orWhere('phone', 'like', "%{$keyword}%")
                ->orWhere('insurance_number', 'like', "%{$keyword}%");
        })
            ->orderBy('name', 'asc')
            ->paginate(15);
    }

    public function syncToSatusehat(Patient $patient): ?array
    {
        try {
            $result = $this->satuSehatPatientService->syncPatient($patient);

            Log::info('Patient synced to Satu Sehat', [
                'patient_id' => $patient->id,
                'result' => $result,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to sync patient to Satu Sehat', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function checkBpjsStatus(Patient $patient): ?array
    {
        if ($patient->insurance_type !== 'BPJS' || !$patient->insurance_number) {
            return null;
        }

        try {
            $tglPelayanan = now()->format('Y-m-d');
            $response = $this->vClaimService->getPeserta($patient->insurance_number, $tglPelayanan);

            if ($response && isset($response['peserta'])) {
                $peserta = $response['peserta'];

                $bpjsStatus = $peserta['statusPeserta']['keterangan'] ?? 'unknown';
                $patient->update(['bpjs_status' => $bpjsStatus]);

                $bpjsPatient = $patient->bpjsPatient;
                if ($bpjsPatient) {
                    $bpjsPatient->update([
                        'no_kartu' => $peserta['noKartu'] ?? $patient->insurance_number,
                        'nama' => $peserta['nama'] ?? $patient->name,
                        'hak_kelas' => $peserta['hakKelas']['keterangan'] ?? null,
                        'jenis_peserta' => $peserta['jenisPeserta']['keterangan'] ?? null,
                        'status' => $bpjsStatus,
                        'data_raw' => $response,
                        'checked_at' => now(),
                    ]);
                }

                Log::info('BPJS status updated', [
                    'patient_id' => $patient->id,
                    'status' => $bpjsStatus,
                ]);

                return $response;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to check BPJS status', [
                'patient_id' => $patient->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

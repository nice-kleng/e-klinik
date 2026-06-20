<?php

namespace App\Services;

use App\Models\InformedConsent;
use App\Models\MedicalRecordProcedure;
use App\Services\TteService;
use Illuminate\Support\Facades\DB;

class InformedConsentService
{
    protected TteService $tteService;

    public function __construct(TteService $tteService)
    {
        $this->tteService = $tteService;
    }

    public function createConsent(array $data): InformedConsent
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = 'draft';

            $consent = InformedConsent::create($data);

            if (!empty($data['procedure_ids'])) {
                MedicalRecordProcedure::whereIn('id', $data['procedure_ids'])
                    ->update(['informed_consent_id' => $consent->id]);
            }

            return $consent;
        });
    }

    public function updateConsent(InformedConsent $consent, array $data): InformedConsent
    {
        return DB::transaction(function () use ($consent, $data) {
            if ($consent->status === 'signed') {
                throw new \RuntimeException('Informed consent sudah ditandatangani, tidak bisa diubah');
            }

            $consent->update($data);

            if (isset($data['procedure_ids'])) {
                MedicalRecordProcedure::where('informed_consent_id', $consent->id)
                    ->update(['informed_consent_id' => null]);

                MedicalRecordProcedure::whereIn('id', $data['procedure_ids'])
                    ->update(['informed_consent_id' => $consent->id]);
            }

            return $consent->fresh();
        });
    }

    public function signPatient(InformedConsent $consent, string $patientName): InformedConsent
    {
        if ($consent->patient_signed_at) {
            throw new \RuntimeException('Pasien sudah menandatangani consent ini');
        }

        if ($consent->status === 'cancelled') {
            throw new \RuntimeException('Informed consent sudah dibatalkan, tidak bisa ditandatangani');
        }

        return DB::transaction(function () use ($consent, $patientName) {
            $consent->update([
                'patient_name' => $patientName,
                'patient_agreed' => true,
                'patient_signed_at' => now(),
            ]);

            $fresh = $consent->fresh();
            $fresh->patient_signature_hash = $this->tteService->generateHash($fresh);
            $fresh->save();

            return $fresh;
        });
    }

    public function signDoctor(InformedConsent $consent, \App\Models\User $user): InformedConsent
    {
        if ($consent->status === 'signed') {
            throw new \RuntimeException('Informed consent sudah ditandatangani dokter');
        }

        if (!$consent->patient_signed_at) {
            throw new \RuntimeException('Pasien harus menandatangani terlebih dahulu sebelum dokter');
        }

        return DB::transaction(function () use ($consent, $user) {
            $hash = $this->tteService->generateHash($consent);

            $consent->update([
                'signed_by' => $user->id,
                'signed_at' => now(),
                'signature_hash' => $hash,
                'status' => 'signed',
            ]);

            MedicalRecordProcedure::where('informed_consent_id', $consent->id)
                ->update(['informed_consent' => true]);

            return $consent->fresh();
        });
    }

    public function cancel(InformedConsent $consent): InformedConsent
    {
        if ($consent->status === 'signed') {
            throw new \RuntimeException('Informed consent sudah ditandatangani, tidak bisa dibatalkan');
        }

        return DB::transaction(function () use ($consent) {
            $consent->update(['status' => 'cancelled']);

            MedicalRecordProcedure::where('informed_consent_id', $consent->id)
                ->update(['informed_consent_id' => null, 'informed_consent' => false]);

            return $consent->fresh();
        });
    }

    public function verifyHash(string $hash): ?InformedConsent
    {
        return InformedConsent::where('signature_hash', $hash)
            ->with(['patient', 'medicalRecord.doctor', 'signer', 'procedureIcd9'])
            ->first();
    }

    public function getVerifyUrl(InformedConsent $consent): string
    {
        return route('informed-consents.verify', $consent->signature_hash);
    }
}

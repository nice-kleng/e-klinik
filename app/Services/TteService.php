<?php

namespace App\Services;

use App\Models\MedicalRecord;
use App\Models\User;
use RuntimeException;

class TteService
{
    public function sign(MedicalRecord $mr, User $user): array
    {
        if ($mr->signed_by) {
            throw new RuntimeException('RME sudah ditandatangani sebelumnya');
        }

        $hash = $this->generateHash($mr);

        $mr->update([
            'signed_by' => $user->id,
            'signed_at' => now(),
            'signature_hash' => $hash,
            'is_tte_verified' => true,
        ]);

        return [
            'signed_by' => $user->id,
            'signed_at' => $mr->signed_at,
            'signature_hash' => $hash,
        ];
    }

    public function verify(string $hash): ?MedicalRecord
    {
        return MedicalRecord::where('signature_hash', $hash)
            ->with(['patient', 'doctor', 'polyclinic', 'signer', 'diagnoses.icd10Diagnosis'])
            ->first();
    }

    public function verifyIntegrity(MedicalRecord $mr): bool
    {
        if (!$mr->signature_hash) {
            return false;
        }

        return hash_equals($mr->signature_hash, $this->generateHash($mr));
    }

    public function generateHash(MedicalRecord $mr): string
    {
        $data = [
            $mr->patient_id,
            $mr->visit_date?->format('Y-m-d'),
            $mr->subjective_complaint,
            $mr->anamnesis,
            $mr->objective_finding,
            $mr->physical_exam,
            $mr->assessment,
            $mr->differential_diagnosis,
            $mr->plan,
            $mr->diagnosis_primary_id,
            json_encode($mr->diagnosis_secondary_ids ?? []),
            json_encode($mr->diagnosis_differential_ids ?? []),
            json_encode($mr->procedure_ids ?? []),
            $mr->notes,
            $mr->created_at?->toIso8601String(),
        ];

        return hash('sha256', implode('|', $data));
    }

    public function getSignedUrl(MedicalRecord $mr): string
    {
        return route('medical-records.verify', $mr->signature_hash);
    }
}

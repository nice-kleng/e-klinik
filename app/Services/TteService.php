<?php

namespace App\Services;

use App\Models\InformedConsent;
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

    public function generateHash(MedicalRecord|InformedConsent $entity): string
    {
        if ($entity instanceof InformedConsent) {
            return $this->generateConsentHash($entity);
        }

        $data = [
            $entity->patient_id,
            $entity->visit_date?->format('Y-m-d'),
            $entity->subjective_complaint,
            $entity->anamnesis,
            $entity->objective_finding,
            $entity->physical_exam,
            $entity->assessment,
            $entity->differential_diagnosis,
            $entity->plan,
            $entity->diagnosis_primary_id,
            json_encode($entity->diagnosis_secondary_ids ?? []),
            json_encode($entity->diagnosis_differential_ids ?? []),
            json_encode($entity->procedure_ids ?? []),
            $entity->notes,
            $entity->created_at?->toIso8601String(),
        ];

        return hash('sha256', implode('|', $data));
    }

    protected function generateConsentHash(InformedConsent $consent): string
    {
        $data = [
            $consent->patient_id,
            $consent->consent_type,
            $consent->procedure_name ?? '',
            (string) $consent->procedure_icd9_id,
            $consent->diagnosis ?? '',
            $consent->purpose ?? '',
            $consent->risks ?? '',
            $consent->benefits ?? '',
            $consent->alternatives ?? '',
            $consent->doctor_recommendation ?? '',
            $consent->patient_name ?? '',
            $consent->patient_signed_at?->toIso8601String() ?? '',
            $consent->witness_name ?? '',
            $consent->created_at?->toIso8601String(),
        ];

        return hash('sha256', implode('|', $data));
    }

    public function getSignedUrl(MedicalRecord $mr): string
    {
        return route('medical-records.verify', $mr->signature_hash);
    }
}

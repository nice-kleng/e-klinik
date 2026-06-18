<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_date' => $this->visit_date?->format('Y-m-d'),
            'visit_type' => $this->visit_type,
            'visit_type_label' => match ($this->visit_type) {
                'Baru' => 'Kunjungan Baru',
                'Lama' => 'Kunjungan Lama',
                'Kontrol' => 'Kontrol',
                'Rujukan' => 'Rujukan',
                default => $this->visit_type,
            },
            'subjective_complaint' => $this->subjective_complaint,
            'objective_finding' => $this->objective_finding,
            'assessment' => $this->assessment,
            'plan' => $this->plan,
            'anamnesis' => $this->anamnesis,
            'physical_exam' => $this->physical_exam,
            'vital_signs' => $this->vital_signs,
            'diagnosis_primary' => $this->diagnosis_primary,
            'diagnosis_secondary' => $this->diagnosis_secondary,
            'notes' => $this->notes,
            'follow_up_date' => $this->follow_up_date?->format('Y-m-d'),
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'no_rm' => $this->patient->no_rm,
                    'nik' => $this->patient->nik,
                    'name' => $this->patient->name,
                    'birth_date' => $this->patient->birth_date?->format('Y-m-d'),
                    'gender' => $this->patient->gender,
                    'phone' => $this->patient->phone,
                    'bpjs_status' => $this->patient->bpjs_status,
                ];
            }),
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'code' => $this->doctor->code,
                    'name' => $this->doctor->name,
                    'specialist' => $this->doctor->specialist,
                ];
            }),
            'polyclinic' => $this->whenLoaded('polyclinic', function () {
                return [
                    'id' => $this->polyclinic->id,
                    'code' => $this->polyclinic->code,
                    'name' => $this->polyclinic->name,
                ];
            }),
            'queue' => $this->whenLoaded('queue', function () {
                return [
                    'id' => $this->queue->id,
                    'queue_number' => $this->queue->queue_number,
                    'status' => $this->queue->status,
                ];
            }),
            'prescriptions' => $this->whenLoaded('prescriptions', function () {
                return PrescriptionResource::collection($this->prescriptions);
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

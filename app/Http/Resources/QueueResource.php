<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $registration = $this->registration;

        return [
            'id' => $this->id,
            'queue_number' => $this->queue_number,
            'queue_sequence' => $this->queue_sequence,
            'queue_date' => $this->queue_date?->format('Y-m-d'),
            'source' => $this->source,
            'status' => $this->status,
            'status_label' => match ($this->status) {
                'waiting' => 'Menunggu',
                'called' => 'Dipanggil',
                'in_progress' => 'Dalam Pemeriksaan',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan',
                default => $this->status,
            },
            'check_in_at' => $this->check_in_at?->format('Y-m-d H:i:s'),
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'patient' => $this->when($registration, function () use ($registration) {
                $patient = $registration->patient;
                return $patient ? [
                    'id' => $patient->id,
                    'no_rm' => $patient->no_rm,
                    'nik' => $patient->nik,
                    'name' => $patient->name,
                    'gender' => $patient->gender,
                    'phone' => $patient->phone,
                ] : null;
            }),
            'polyclinic' => $this->whenLoaded('polyclinic', function () {
                return [
                    'id' => $this->polyclinic->id,
                    'code' => $this->polyclinic->code,
                    'name' => $this->polyclinic->name,
                ];
            }),
            'doctor' => $this->when($registration, function () use ($registration) {
                $doctor = $registration->doctor;
                return $doctor ? [
                    'id' => $doctor->id,
                    'code' => $doctor->code,
                    'name' => $doctor->name,
                    'specialist' => $doctor->specialist,
                ] : null;
            }),
            'registration' => $this->when($registration, function () use ($registration) {
                return [
                    'id' => $registration->id,
                    'registration_number' => $registration->registration_number,
                    'age_text' => $registration->age_text,
                    'service_status' => $registration->service_status,
                    'no_sep' => $registration->no_sep,
                ];
            }),
            'medical_record' => $this->whenLoaded('medicalRecord', function () {
                return [
                    'id' => $this->medicalRecord->id,
                    'visit_type' => $this->medicalRecord->visit_type,
                    'visit_date' => $this->medicalRecord->visit_date?->format('Y-m-d'),
                ];
            }),
            'bpjs_antrian_id' => $registration?->bpjs_antrian_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

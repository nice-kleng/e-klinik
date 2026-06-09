<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'queue_number' => $this->queue_number,
            'queue_date' => $this->queue_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => match ($this->status) {
                'waiting' => 'Menunggu',
                'called' => 'Dipanggil',
                'in_progress' => 'Dalam Pemeriksaan',
                'completed' => 'Selesai',
                'canceled' => 'Dibatalkan',
                default => $this->status,
            },
            'service_type' => $this->service_type,
            'estimated_wait_time' => $this->estimated_wait_time,
            'check_in_at' => $this->check_in_at?->format('Y-m-d H:i:s'),
            'called_at' => $this->called_at?->format('Y-m-d H:i:s'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'notes' => $this->notes,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'no_rm' => $this->patient->no_rm,
                    'nik' => $this->patient->nik,
                    'name' => $this->patient->name,
                    'gender' => $this->patient->gender,
                    'phone' => $this->patient->phone,
                ];
            }),
            'polyclinic' => $this->whenLoaded('polyclinic', function () {
                return [
                    'id' => $this->polyclinic->id,
                    'code' => $this->polyclinic->code,
                    'name' => $this->polyclinic->name,
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
            'medical_record' => $this->whenLoaded('medicalRecord', function () {
                return [
                    'id' => $this->medicalRecord->id,
                    'visit_type' => $this->medicalRecord->visit_type,
                    'visit_date' => $this->medicalRecord->visit_date?->format('Y-m-d'),
                ];
            }),
            'bpjs_antrian_id' => $this->bpjs_antrian_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'prescription_number' => $this->prescription_number,
            'prescription_date' => $this->prescription_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => match ($this->status) {
                'active' => 'Aktif',
                'inactive' => 'Tidak Aktif',
                'canceled' => 'Dibatalkan',
                default => $this->status,
            },
            'notes' => $this->notes,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'no_rm' => $this->patient->no_rm,
                    'nik' => $this->patient->nik,
                    'name' => $this->patient->name,
                ];
            }),
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'code' => $this->doctor->code,
                    'name' => $this->doctor->name,
                    'sip_number' => $this->doctor->sip_number,
                ];
            }),
            'medical_record' => $this->whenLoaded('medicalRecord', function () {
                return [
                    'id' => $this->medicalRecord->id,
                    'visit_date' => $this->medicalRecord->visit_date?->format('Y-m-d'),
                    'diagnosis_primary' => $this->medicalRecord->diagnosis_primary,
                ];
            }),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'medicine_id' => $item->medicine_id,
                        'medicine' => $item->whenLoaded('medicine', function () use ($item) {
                            return [
                                'id' => $item->medicine->id,
                                'code' => $item->medicine->code,
                                'name' => $item->medicine->name,
                                'generic_name' => $item->medicine->generic_name,
                                'unit' => $item->medicine->unit,
                            ];
                        }),
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'dosage' => $item->dosage,
                        'subtotal' => $item->subtotal,
                    ];
                });
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

<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_rm' => $this->no_rm,
            'nik' => $this->nik,
            'no_kk' => $this->no_kk,
            'name' => $this->name,
            'birth_place' => $this->birth_place,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'age' => $this->birth_date ? Carbon::parse($this->birth_date)->age : null,
            'gender' => $this->gender,
            'gender_label' => $this->gender === 'L' ? 'Laki-laki' : 'Perempuan',
            'blood_type' => $this->blood_type,
            'address' => $this->address,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'village' => $this->village,
            'district' => $this->district,
            'city' => $this->city,
            'province' => $this->province,
            'phone' => $this->phone,
            'email' => $this->email,
            'occupation' => $this->occupation,
            'marriage_status' => $this->marriage_status,
            'religion' => $this->religion,
            'insurance_type' => $this->insurance_type,
            'insurance_number' => $this->insurance_number,
            'bpjs_status' => $this->bpjs_status,
            'bpjs_patient' => $this->whenLoaded('bpjsPatient', function () {
                return $this->bpjsPatient;
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

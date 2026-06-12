<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'polyclinic_id' => 'required|integer|exists:polyclinics,id',
            'doctor_id' => 'nullable|integer|exists:doctors,id',
            'source' => 'nullable|in:walk_in,mjkn,rujukan',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pasien wajib dipilih',
            'patient_id.exists' => 'Pasien tidak ditemukan',
            'polyclinic_id.required' => 'Poli wajib dipilih',
            'polyclinic_id.exists' => 'Poli tidak ditemukan',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
        ];
    }
}

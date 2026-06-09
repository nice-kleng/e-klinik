<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicalRecordRequest extends FormRequest
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
            'doctor_id' => 'required|integer|exists:doctors,id',
            'queue_id' => 'nullable|integer|exists:queues,id',
            'visit_date' => 'nullable|date',
            'visit_type' => 'required|string|in:baru,lama,kontrol,rujukan',
            'subjective_complaint' => 'nullable|string',
            'objective_finding' => 'nullable|string',
            'assessment' => 'nullable|string',
            'plan' => 'nullable|string',
            'anamnesis' => 'nullable|string',
            'physical_exam' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.systolic' => 'nullable|numeric|min:50|max:300',
            'vital_signs.diastolic' => 'nullable|numeric|min:30|max:200',
            'vital_signs.heart_rate' => 'nullable|numeric|min:20|max:250',
            'vital_signs.respiratory_rate' => 'nullable|numeric|min:5|max:100',
            'vital_signs.temperature' => 'nullable|numeric|min:32|max:43',
            'vital_signs.oxygen_saturation' => 'nullable|numeric|min:50|max:100',
            'vital_signs.weight' => 'nullable|numeric|min:1|max:300',
            'vital_signs.height' => 'nullable|numeric|min:20|max:300',
            'vital_signs.gcs' => 'nullable|integer|min:3|max:15',
            'vital_signs.blood_glucose' => 'nullable|numeric|min:20|max:800',
            'vital_signs.notes' => 'nullable|string|max:500',
            'diagnosis_primary' => 'nullable|string|max:50',
            'diagnosis_secondary' => 'nullable|array',
            'diagnosis_secondary.*' => 'string|max:50',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Pasien wajib dipilih',
            'patient_id.exists' => 'Pasien tidak ditemukan',
            'polyclinic_id.required' => 'Poli wajib dipilih',
            'polyclinic_id.exists' => 'Poli tidak ditemukan',
            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
            'visit_type.required' => 'Jenis kunjungan wajib diisi',
            'visit_type.in' => 'Jenis kunjungan tidak valid',
            'vital_signs.array' => 'Data tanda vital harus berupa array',
            'vital_signs.systolic.numeric' => 'Tekanan darah sistolik harus berupa angka',
            'vital_signs.diastolic.numeric' => 'Tekanan darah diastolik harus berupa angka',
            'vital_signs.heart_rate.numeric' => 'Denyut jantung harus berupa angka',
            'vital_signs.temperature.numeric' => 'Suhu tubuh harus berupa angka',
            'vital_signs.oxygen_saturation.numeric' => 'Saturasi oksigen harus berupa angka',
            'vital_signs.weight.numeric' => 'Berat badan harus berupa angka',
            'vital_signs.height.numeric' => 'Tinggi badan harus berupa angka',
            'follow_up_date.after_or_equal' => 'Tanggal kontrol harus hari ini atau setelahnya',
        ];
    }
}

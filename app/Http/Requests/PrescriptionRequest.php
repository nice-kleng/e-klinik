<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'medical_record_id' => 'required|integer|exists:medical_records,id',
            'patient_id' => 'required|integer|exists:patients,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'prescription_number' => 'nullable|string|max:50|unique:prescriptions,prescription_number',
            'prescription_date' => 'nullable|date',
            'status' => 'nullable|string|in:active,inactive,canceled',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|integer|exists:medicines,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.dosage' => 'nullable|array',
            'items.*.dosage.amount' => 'nullable|numeric|min:0',
            'items.*.dosage.frequency' => 'nullable|string|max:100',
            'items.*.dosage.route' => 'nullable|string|max:100',
            'items.*.dosage.instructions' => 'nullable|string|max:500',
            'items.*.subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'medical_record_id.required' => 'Rekam medis wajib dipilih',
            'medical_record_id.exists' => 'Rekam medis tidak ditemukan',
            'patient_id.required' => 'Pasien wajib dipilih',
            'patient_id.exists' => 'Pasien tidak ditemukan',
            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan',
            'items.required' => 'Minimal satu item obat wajib ditambahkan',
            'items.array' => 'Format item obat tidak valid',
            'items.*.medicine_id.required' => 'Obat wajib dipilih',
            'items.*.medicine_id.exists' => 'Obat tidak ditemukan',
            'items.*.quantity.required' => 'Jumlah obat wajib diisi',
            'items.*.quantity.integer' => 'Jumlah obat harus berupa angka',
            'items.*.quantity.min' => 'Jumlah obat minimal 1',
        ];
    }
}

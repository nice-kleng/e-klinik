<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            'nik' => [
                'required',
                'string',
                'size:16',
                Rule::unique('patients', 'nik')->ignore($patientId),
            ],
            'no_kk' => 'nullable|string|size:16',
            'name' => 'required|string|max:255',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'required|date',
            'gender' => 'required|string|in:L,P',
            'blood_type' => 'nullable|string|in:A,B,AB,O',
            'address' => 'nullable|string|max:500',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'village' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'occupation' => 'nullable|string|max:100',
            'marriage_status' => 'nullable|string|in:single,married,divorced,widowed',
            'religion' => 'nullable|string|max:50',
            'insurance_type' => 'nullable|string|max:50',
            'insurance_number' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi',
            'nik.size' => 'NIK harus 16 digit',
            'nik.unique' => 'NIK sudah terdaftar',
            'name.required' => 'Nama pasien wajib diisi',
            'name.max' => 'Nama pasien maksimal 255 karakter',
            'birth_date.required' => 'Tanggal lahir wajib diisi',
            'birth_date.date' => 'Format tanggal lahir tidak valid',
            'gender.required' => 'Jenis kelamin wajib diisi',
            'gender.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)',
            'email.email' => 'Format email tidak valid',
            'phone.max' => 'Nomor telepon maksimal 20 karakter',
        ];
    }
}

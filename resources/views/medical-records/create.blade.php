@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Rekam Medis</h4>
        <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('medical-records.store') }}">
        @csrf
        @if($queueId)
            <input type="hidden" name="queue_id" value="{{ $queueId }}">
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Data Pasien</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pasien <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pasien --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ (old('patient_id', $selectedPatientId) == $patient->id) ? 'selected' : '' }}>
                                    [{{ $patient->no_rm }}] {{ $patient->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Dokter <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Dokter --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name }} ({{ $doctor->polyclinic->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Poliklinik <span class="text-danger">*</span></label>
                        <select name="polyclinic_id" class="form-select @error('polyclinic_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Poliklinik --</option>
                            @foreach($polyclinics as $poly)
                                <option value="{{ $poly->id }}" {{ old('polyclinic_id') == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                            @endforeach
                        </select>
                        @error('polyclinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" name="visit_date" class="form-control @error('visit_date') is-invalid @enderror" value="{{ old('visit_date', date('Y-m-d')) }}" required>
                        @error('visit_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Kunjungan</label>
                        <select name="visit_type" class="form-select @error('visit_type') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Baru" {{ old('visit_type') == 'Baru' ? 'selected' : '' }}>Baru</option>
                            <option value="Lama" {{ old('visit_type') == 'Lama' ? 'selected' : '' }}>Lama</option>
                            <option value="Kontrol" {{ old('visit_type') == 'Kontrol' ? 'selected' : '' }}>Kontrol</option>
                            <option value="Rujukan" {{ old('visit_type') == 'Rujukan' ? 'selected' : '' }}>Rujukan</option>
                        </select>
                        @error('visit_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Anamnesis & Pemeriksaan</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Anamnesis / Subjective</label>
                        <textarea name="anamnesis" class="form-control @error('anamnesis') is-invalid @enderror" rows="4">{{ old('anamnesis') }}</textarea>
                        @error('anamnesis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Keluhan Utama</label>
                        <textarea name="subjective_complaint" class="form-control @error('subjective_complaint') is-invalid @enderror" rows="2">{{ old('subjective_complaint') }}</textarea>
                        @error('subjective_complaint') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik / Objective</label>
                        <textarea name="objective_finding" class="form-control @error('objective_finding') is-invalid @enderror" rows="4">{{ old('objective_finding') }}</textarea>
                        @error('objective_finding') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pemeriksaan Fisik (Detail)</label>
                        <textarea name="physical_exam" class="form-control @error('physical_exam') is-invalid @enderror" rows="4">{{ old('physical_exam') }}</textarea>
                        @error('physical_exam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tanda-Tanda Vital</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah (mmHg)</label>
                        <input type="text" name="vital_signs[blood_pressure]" class="form-control" value="{{ old('vital_signs.blood_pressure') }}" placeholder="120/80">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nadi (x/menit)</label>
                        <input type="number" name="vital_signs[heart_rate]" class="form-control" value="{{ old('vital_signs.heart_rate') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Suhu (Â°C)</label>
                        <input type="text" name="vital_signs[temperature]" class="form-control" value="{{ old('vital_signs.temperature') }}" placeholder="36.5">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RR (x/menit)</label>
                        <input type="number" name="vital_signs[respiratory_rate]" class="form-control" value="{{ old('vital_signs.respiratory_rate') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="vital_signs[weight]" class="form-control" value="{{ old('vital_signs.weight') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="vital_signs[height]" class="form-control" value="{{ old('vital_signs.height') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SpOâ‚‚ (%)</label>
                        <input type="number" name="vital_signs[oxygen_saturation]" class="form-control" value="{{ old('vital_signs.oxygen_saturation') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Diagnosis & Tindak Lanjut</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Assessment</label>
                        <textarea name="assessment" class="form-control @error('assessment') is-invalid @enderror" rows="3">{{ old('assessment') }}</textarea>
                        @error('assessment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Plan / Rencana</label>
                        <textarea name="plan" class="form-control @error('plan') is-invalid @enderror" rows="3">{{ old('plan') }}</textarea>
                        @error('plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Diagnosis Utama (ICD-10)</label>
                        <input type="text" name="diagnosis_primary" class="form-control @error('diagnosis_primary') is-invalid @enderror" value="{{ old('diagnosis_primary') }}" placeholder="Kode ICD-10" list="icdList">
                        <datalist id="icdList"></datalist>
                        @error('diagnosis_primary') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Follow-up</label>
                        <input type="date" name="follow_up_date" class="form-control @error('follow_up_date') is-invalid @enderror" value="{{ old('follow_up_date') }}">
                        @error('follow_up_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

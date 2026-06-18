@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Asesmen Awal / Triage</h4>
        <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #17a2b8;">
        <div class="card-body">
            <h5 class="mb-0">{{ $registration->patient->name ?? '-' }}</h5>
            <div class="text-muted small">
                No. RM: {{ $registration->patient->no_rm ?? '-' }} &middot;
                Poli: {{ $registration->polyclinic->name ?? '-' }} &middot;
                Dokter: {{ $registration->doctor->name ?? '-' }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('triage.store') }}">
        @csrf
        <input type="hidden" name="registration_id" value="{{ $registration->id }}">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Tanda-Tanda Vital</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah Sistolik (mmHg)</label>
                        <input type="number" name="systolic" class="form-control @error('systolic') is-invalid @enderror" value="{{ old('systolic') }}" placeholder="120">
                        @error('systolic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah Diastolik (mmHg)</label>
                        <input type="number" name="diastolic" class="form-control @error('diastolic') is-invalid @enderror" value="{{ old('diastolic') }}" placeholder="80">
                        @error('diastolic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nadi (x/menit)</label>
                        <input type="number" name="heart_rate" class="form-control @error('heart_rate') is-invalid @enderror" value="{{ old('heart_rate') }}">
                        @error('heart_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RR (x/menit)</label>
                        <input type="number" name="respiratory_rate" class="form-control @error('respiratory_rate') is-invalid @enderror" value="{{ old('respiratory_rate') }}">
                        @error('respiratory_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Suhu (&deg;C)</label>
                        <input type="text" name="temperature" class="form-control @error('temperature') is-invalid @enderror" value="{{ old('temperature') }}" placeholder="36.5">
                        @error('temperature')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SpO&#8322; (%)</label>
                        <input type="number" name="oxygen_saturation" class="form-control @error('oxygen_saturation') is-invalid @enderror" value="{{ old('oxygen_saturation') }}">
                        @error('oxygen_saturation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="weight" class="form-control @error('weight') is-invalid @enderror" value="{{ old('weight') }}">
                        @error('weight')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="height" class="form-control @error('height') is-invalid @enderror" value="{{ old('height') }}">
                        @error('height')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GCS</label>
                        <input type="number" name="gcs" class="form-control @error('gcs') is-invalid @enderror" value="{{ old('gcs') }}" placeholder="3-15" min="3" max="15">
                        @error('gcs')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gula Darah (mg/dL)</label>
                        <input type="number" name="blood_glucose" class="form-control @error('blood_glucose') is-invalid @enderror" value="{{ old('blood_glucose') }}" placeholder="100">
                        @error('blood_glucose')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Keluhan & Screening</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Keluhan Utama</label>
                        <textarea name="chief_complaint" class="form-control @error('chief_complaint') is-invalid @enderror" rows="3">{{ old('chief_complaint') }}</textarea>
                        @error('chief_complaint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Skala Nyeri (0-10)</label>
                        <input type="number" name="pain_scale" class="form-control @error('pain_scale') is-invalid @enderror" value="{{ old('pain_scale') }}" min="0" max="10">
                        @error('pain_scale')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Riwayat Alergi</label>
                        <textarea name="allergy_notes" class="form-control @error('allergy_notes') is-invalid @enderror" rows="2">{{ old('allergy_notes') }}</textarea>
                        @error('allergy_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Risiko Jatuh</label>
                        <select name="fall_risk" class="form-select @error('fall_risk') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ old('fall_risk') == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('fall_risk') == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('fall_risk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Nutrisi</label>
                        <select name="nutrition_status" class="form-select @error('nutrition_status') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="baik" {{ old('nutrition_status') == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="kurang" {{ old('nutrition_status') == 'kurang' ? 'selected' : '' }}>Kurang</option>
                            <option value="lebih" {{ old('nutrition_status') == 'lebih' ? 'selected' : '' }}>Lebih</option>
                            <option value="buruk" {{ old('nutrition_status') == 'buruk' ? 'selected' : '' }}>Buruk</option>
                        </select>
                        @error('nutrition_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Merokok</label>
                        <select name="smoking_status" class="form-select @error('smoking_status') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="tidak_merokok" {{ old('smoking_status') == 'tidak_merokok' ? 'selected' : '' }}>Tidak Merokok</option>
                            <option value="perokok_aktif" {{ old('smoking_status') == 'perokok_aktif' ? 'selected' : '' }}>Perokok Aktif</option>
                            <option value="mantan_perokok" {{ old('smoking_status') == 'mantan_perokok' ? 'selected' : '' }}>Mantan Perokok</option>
                        </select>
                        @error('smoking_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Kehamilan</label>
                        <select name="pregnancy_status" class="form-select @error('pregnancy_status') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="tidak_hamil" {{ old('pregnancy_status') == 'tidak_hamil' ? 'selected' : '' }}>Tidak Hamil</option>
                            <option value="hamil" {{ old('pregnancy_status') == 'hamil' ? 'selected' : '' }}>Hamil</option>
                            <option value="nifas" {{ old('pregnancy_status') == 'nifas' ? 'selected' : '' }}>Nifas</option>
                            <option value="menyusui" {{ old('pregnancy_status') == 'menyusui' ? 'selected' : '' }}>Menyusui</option>
                        </select>
                        @error('pregnancy_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-info text-white">Simpan Triage</button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Triage</h4>
        <a href="{{ route('triage.show', $triage) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('triage.update', $triage) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Tanda-Tanda Vital</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah Sistolik (mmHg)</label>
                        <input type="number" name="systolic" class="form-control" value="{{ old('systolic', $triage->systolic) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tekanan Darah Diastolik (mmHg)</label>
                        <input type="number" name="diastolic" class="form-control" value="{{ old('diastolic', $triage->diastolic) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nadi (x/menit)</label>
                        <input type="number" name="heart_rate" class="form-control" value="{{ old('heart_rate', $triage->heart_rate) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RR (x/menit)</label>
                        <input type="number" name="respiratory_rate" class="form-control" value="{{ old('respiratory_rate', $triage->respiratory_rate) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Suhu (&deg;C)</label>
                        <input type="text" name="temperature" class="form-control" value="{{ old('temperature', $triage->temperature) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SpO&#8322; (%)</label>
                        <input type="number" name="oxygen_saturation" class="form-control" value="{{ old('oxygen_saturation', $triage->oxygen_saturation) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="weight" class="form-control" value="{{ old('weight', $triage->weight) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="height" class="form-control" value="{{ old('height', $triage->height) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">GCS</label>
                        <input type="number" name="gcs" class="form-control" value="{{ old('gcs', $triage->gcs) }}" min="3" max="15">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gula Darah (mg/dL)</label>
                        <input type="number" name="blood_glucose" class="form-control" value="{{ old('blood_glucose', $triage->blood_glucose) }}">
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
                        <textarea name="chief_complaint" class="form-control" rows="3">{{ old('chief_complaint', $triage->chief_complaint) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Skala Nyeri (0-10)</label>
                        <input type="number" name="pain_scale" class="form-control" value="{{ old('pain_scale', $triage->pain_scale) }}" min="0" max="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Riwayat Alergi</label>
                        <textarea name="allergy_notes" class="form-control" rows="2">{{ old('allergy_notes', $triage->allergy_notes) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Risiko Jatuh</label>
                        <select name="fall_risk" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="1" {{ old('fall_risk', $triage->fall_risk) == 1 ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('fall_risk', $triage->fall_risk) === 0 ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Nutrisi</label>
                        <select name="nutrition_status" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="baik" {{ old('nutrition_status', $triage->nutrition_status) == 'baik' ? 'selected' : '' }}>Baik</option>
                            <option value="kurang" {{ old('nutrition_status', $triage->nutrition_status) == 'kurang' ? 'selected' : '' }}>Kurang</option>
                            <option value="lebih" {{ old('nutrition_status', $triage->nutrition_status) == 'lebih' ? 'selected' : '' }}>Lebih</option>
                            <option value="buruk" {{ old('nutrition_status', $triage->nutrition_status) == 'buruk' ? 'selected' : '' }}>Buruk</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Merokok</label>
                        <select name="smoking_status" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="tidak_merokok" {{ old('smoking_status', $triage->smoking_status) == 'tidak_merokok' ? 'selected' : '' }}>Tidak Merokok</option>
                            <option value="perokok_aktif" {{ old('smoking_status', $triage->smoking_status) == 'perokok_aktif' ? 'selected' : '' }}>Perokok Aktif</option>
                            <option value="mantan_perokok" {{ old('smoking_status', $triage->smoking_status) == 'mantan_perokok' ? 'selected' : '' }}>Mantan Perokok</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status Kehamilan</label>
                        <select name="pregnancy_status" class="form-select">
                            <option value="">-- Pilih --</option>
                            <option value="tidak_hamil" {{ old('pregnancy_status', $triage->pregnancy_status) == 'tidak_hamil' ? 'selected' : '' }}>Tidak Hamil</option>
                            <option value="hamil" {{ old('pregnancy_status', $triage->pregnancy_status) == 'hamil' ? 'selected' : '' }}>Hamil</option>
                            <option value="nifas" {{ old('pregnancy_status', $triage->pregnancy_status) == 'nifas' ? 'selected' : '' }}>Nifas</option>
                            <option value="menyusui" {{ old('pregnancy_status', $triage->pregnancy_status) == 'menyusui' ? 'selected' : '' }}>Menyusui</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $triage->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('triage.show', $triage) }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

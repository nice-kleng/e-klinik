@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Edukasi Pasien</h4>
        <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-0">{{ $medicalRecord->patient->name ?? '-' }}</h5>
            <div class="text-muted small">
                No. RM: {{ $medicalRecord->patient->no_rm ?? '-' }} &middot;
                Tanggal: {{ $medicalRecord->visit_date?->format('d/m/Y') }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('education.update', $medicalRecord) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Edukasi</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Diagnosis yang Dijelaskan</label>
                        <textarea name="diagnosis_explained" class="form-control" rows="3">{{ old('diagnosis_explained', $education->diagnosis_explained) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instruksi Obat</label>
                        <textarea name="medication_instructions" class="form-control" rows="3">{{ old('medication_instructions', $education->medication_instructions) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instruksi Diet</label>
                        <textarea name="diet_instructions" class="form-control" rows="3">{{ old('diet_instructions', $education->diet_instructions) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Instruksi Aktivitas</label>
                        <textarea name="activity_instructions" class="form-control" rows="3">{{ old('activity_instructions', $education->activity_instructions) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rencana Kontrol</label>
                        <textarea name="follow_up_plan" class="form-control" rows="3">{{ old('follow_up_plan', $education->follow_up_plan) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Edukasi <span class="text-danger">*</span></label>
                        <input type="date" name="education_date" class="form-control" value="{{ old('education_date', $education->education_date?->format('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection

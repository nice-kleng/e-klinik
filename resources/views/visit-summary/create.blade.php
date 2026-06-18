@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Resume Kunjungan</h4>
        <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="mb-0">{{ $registration->patient->name ?? '-' }}</h5>
            <div class="text-muted small">
                No. RM: {{ $registration->patient->no_rm ?? '-' }} &middot;
                Poli: {{ $registration->polyclinic->name ?? '-' }} &middot;
                Dokter: {{ $registration->doctor->name ?? '-' }} &middot;
                Tgl Kunjungan: {{ $registration->registration_date?->format('d/m/Y') }}
            </div>
            @if($mr && $mr->diagnoses->isNotEmpty())
                <div class="mt-2">
                    @foreach($mr->diagnoses as $d)
                        <span class="badge bg-info me-1">{{ $d->icd10Diagnosis->code ?? '' }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('visit-summary.store', $registration) }}">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><h5 class="mb-0">Resume Kunjungan</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Diagnosis Akhir</label>
                        <textarea name="final_diagnosis" class="form-control" rows="3">{{ old('final_diagnosis') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Pulang <span class="text-danger">*</span></label>
                        <select name="discharge_status" class="form-select" required>
                            <option value="sembuh" {{ old('discharge_status') == 'sembuh' ? 'selected' : '' }}>Sembuh</option>
                            <option value="dirujuk" {{ old('discharge_status') == 'dirujuk' ? 'selected' : '' }}>Dirujuk</option>
                            <option value="pulang_paksa" {{ old('discharge_status') == 'pulang_paksa' ? 'selected' : '' }}>Pulang Paksa</option>
                            <option value="meninggal" {{ old('discharge_status') == 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                            <option value="lainnya" {{ old('discharge_status') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rencana Kontrol</label>
                        <textarea name="follow_up_plan" class="form-control" rows="2">{{ old('follow_up_plan') }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="col-12"><hr></div>
                    <h6 class="text-muted">Rujukan</h6>
                    <div class="col-md-6">
                        <label class="form-label">Rujuk ke</label>
                        <input type="text" name="referral_to" class="form-control" value="{{ old('referral_to') }}" placeholder="Nama faskes/tujuan">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Catatan Rujukan</label>
                        <textarea name="referral_notes" class="form-control" rows="2">{{ old('referral_notes') }}</textarea>
                    </div>

                    <div class="col-12"><hr></div>
                    <h6 class="text-muted">Surat Sakit</h6>
                    <div class="col-md-3">
                        <label class="form-label">Lama (hari)</label>
                        <input type="number" name="sick_leave_days" class="form-control" value="{{ old('sick_leave_days') }}" min="0" max="365">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="sick_leave_from" class="form-control" value="{{ old('sick_leave_from') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="sick_leave_to" class="form-control" value="{{ old('sick_leave_to') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Resume & Selesai</button>
        </div>
    </form>
</div>
@endsection

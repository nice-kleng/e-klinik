@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Edit Hasil Lab</h4>
        <a href="{{ route('lab-results.index') }}" class="btn btn-outline-secondary ms-auto">Kembali</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">
            {{ $labResult->patient->name ?? '-' }} ({{ $labResult->patient->no_rm ?? '-' }}) —
            {{ $labResult->labTest->name ?? '-' }}
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('lab-results.update', $labResult) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Hasil (Nilai)</label>
                        <input type="text" name="result_value" class="form-control" value="{{ old('result_value', $labResult->result_value) }}" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hasil (Teks)</label>
                        <input type="text" name="result_text" class="form-control" value="{{ old('result_text', $labResult->result_text) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $labResult->unit) }}" maxlength="30">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rujukan Rendah</label>
                        <input type="text" name="ref_range_low" class="form-control" value="{{ old('ref_range_low', $labResult->ref_range_low) }}" maxlength="50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rujukan Tinggi</label>
                        <input type="text" name="ref_range_high" class="form-control" value="{{ old('ref_range_high', $labResult->ref_range_high) }}" maxlength="50">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rujukan (Teks)</label>
                        <input type="text" name="ref_range_text" class="form-control" value="{{ old('ref_range_text', $labResult->ref_range_text) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Flag <span class="text-danger">*</span></label>
                        <select name="flag" class="form-select" required>
                            <option value="not_tested" @selected(old('flag', $labResult->flag) == 'not_tested')>Belum Diperiksa</option>
                            <option value="normal" @selected(old('flag', $labResult->flag) == 'normal')>Normal</option>
                            <option value="abnormal" @selected(old('flag', $labResult->flag) == 'abnormal')>Abnormal</option>
                            <option value="critical" @selected(old('flag', $labResult->flag) == 'critical')>Critical</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $labResult->notes) }}">
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('lab-results.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

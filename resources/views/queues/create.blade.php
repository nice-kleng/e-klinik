@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Antrean</h4>
        <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('queues.store') }}">
        @csrf
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Pasien <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pasien --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                    [{{ $patient->no_rm }}] {{ $patient->name }} ({{ $patient->nik }})
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poliklinik <span class="text-danger">*</span></label>
                        <select name="polyclinic_id" id="polyclinic_id" class="form-select @error('polyclinic_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Poliklinik --</option>
                            @foreach($polyclinics as $poly)
                                <option value="{{ $poly->id }}" {{ old('polyclinic_id') == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                            @endforeach
                        </select>
                        @error('polyclinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dokter</label>
                        <select name="doctor_id" id="doctor_id" class="form-select @error('doctor_id') is-invalid @enderror">
                            <option value="">-- Pilih Dokter --</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" data-polyclinic="{{ $doctor->polyclinic_id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->name }} ({{ $doctor->polyclinic->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Layanan <span class="text-danger">*</span></label>
                        <select name="service_type" class="form-select @error('service_type') is-invalid @enderror" required>
                            <option value="">-- Pilih Layanan --</option>
                            <option value="umum" {{ old('service_type') == 'umum' ? 'selected' : '' }}>Umum</option>
                            <option value="BPJS" {{ old('service_type') == 'BPJS' ? 'selected' : '' }}>BPJS</option>
                            <option value="Asuransi" {{ old('service_type') == 'Asuransi' ? 'selected' : '' }}>Asuransi</option>
                        </select>
                        @error('service_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}">
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('polyclinic_id')?.addEventListener('change', function() {
        const polyId = this.value;
        const doctorSelect = document.getElementById('doctor_id');
        for (const opt of doctorSelect.options) {
            if (opt.value === '') continue;
            opt.style.display = opt.dataset.polyclinic == polyId ? '' : 'none';
        }
        if (doctorSelect.selectedOptions[0]?.dataset?.polyclinic != polyId) {
            doctorSelect.value = '';
        }
    });
    document.getElementById('polyclinic_id')?.dispatchEvent(new Event('change'));
</script>
@endpush

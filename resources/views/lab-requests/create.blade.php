@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Permintaan Laboratorium Baru</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('lab-requests.store') }}" id="labRequestForm">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Rekam Medis <span class="text-danger">*</span></label>
                        <select name="medical_record_id" class="form-select @error('medical_record_id') is-invalid @enderror" required id="medicalRecordSelect">
                            <option value="">Pilih Rekam Medis</option>
                            @foreach($medicalRecords as $mr)
                                <option value="{{ $mr->id }}"
                                    data-patient-id="{{ $mr->patient_id }}"
                                    data-patient-name="{{ $mr->patient->name }}"
                                    @selected(old('medical_record_id', $selectedMedicalRecordId) == $mr->id)>
                                    {{ $mr->id }} - {{ $mr->patient->name ?? 'N/A' }} ({{ $mr->created_at->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('medical_record_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pasien <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select @error('patient_id') is-invalid @enderror" required id="patientSelect">
                            <option value="">Pilih Pasien</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatientId) == $patient->id)>
                                    {{ $patient->no_rm }} - {{ $patient->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('patient_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <h5 class="mb-3">Pilih Pemeriksaan</h5>
                <table class="table table-bordered" id="labTestsTable">
                    <thead>
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Kode</th>
                            <th>Nama Tes</th>
                            <th>Kategori</th>
                            <th>Spesimen</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($labTests as $test)
                            <tr>
                                <td>
                                    <input type="checkbox" name="lab_test_ids[]" value="{{ $test->id }}" class="lab-test-checkbox">
                                </td>
                                <td>{{ $test->code }}</td>
                                <td>{{ $test->name }}</td>
                                <td>{{ $test->category->name ?? '-' }}</td>
                                <td>{{ $test->specimen_type ?? '-' }}</td>
                                <td>Rp {{ number_format($test->price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @error('lab_test_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('lab-requests.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('medicalRecordSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value && option.dataset.patientId) {
        document.getElementById('patientSelect').value = option.dataset.patientId;
    }
});
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.lab-test-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
@endsection

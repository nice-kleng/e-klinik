@extends('layouts.app')

@section('header', 'Pendaftaran Pasien')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <form method="POST" action="{{ route('registration.store') }}" id="formPendaftaran">
        @csrf

        <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">

        {{-- Step 1: Cari Pasien --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Cari Pasien</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">NIK Pasien</label>
                        <div class="input-group">
                            <input type="text" id="search_nik" class="form-control" placeholder="Masukkan NIK pasien..." maxlength="16" autocomplete="off">
                            <button type="button" class="btn btn-primary" id="btnCari">Cari</button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="btnReset">Ubah</button>
                        </div>
                        <div id="searchStatus" class="mt-1 small"></div>
                    </div>
                </div>

                {{-- Hasil pencarian pasien ditemukan --}}
                <div id="patientFound" class="d-none mt-3">
                    <div class="alert alert-success d-flex justify-content-between align-items-center py-2 px-3 mb-0">
                        <div>
                            <strong id="patientName"></strong>
                            <span class="text-muted mx-2">|</span>
                            <span>RM: <strong id="patientRm"></strong></span>
                            <span class="text-muted mx-2">|</span>
                            <span>NIK: <strong id="patientNik"></strong></span>
                            <span class="text-muted mx-2">|</span>
                            <span id="patientInsurance" class="badge bg-info"></span>
                        </div>
                    </div>
                </div>

                {{-- Pasien tidak ditemukan --}}
                <div id="patientNotFound" class="d-none mt-3">
                    <div class="alert alert-warning py-2 px-3 mb-0">
                        Pasien tidak ditemukan. Silakan isi data pasien baru di bawah.
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Data Pasien Baru (hidden by default, shown when patient not found) --}}
        <div id="newPatientForm" class="d-none">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Data Pasien Baru</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nik" id="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" maxlength="16">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="birth_date" id="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}">
                            @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" value="L" {{ old('gender') == 'L' ? 'checked' : '' }} id="genderL">
                                    <label class="form-check-label" for="genderL">L</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" value="P" {{ old('gender') == 'P' ? 'checked' : '' }} id="genderP">
                                    <label class="form-check-label" for="genderP">P</label>
                                </div>
                            </div>
                            @error('gender') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address') }}">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Pendaftaran Antrean --}}
        <div class="card border-0 shadow-sm mb-4" id="queueFormCard">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pendaftaran Antrean</h5>
                <span id="selectedPatientBadge" class="badge bg-secondary d-none">Belum pilih pasien</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Jenis Pendaftaran <span class="text-danger">*</span></label>
                        <select name="insurance_type" id="insurance_type" class="form-select @error('insurance_type') is-invalid @enderror" required>
                            <option value="">-- Pilih --</option>
                            <option value="Umum" {{ old('insurance_type') == 'Umum' ? 'selected' : '' }}>Umum</option>
                            <option value="BPJS" {{ old('insurance_type') == 'BPJS' ? 'selected' : '' }}>BPJS</option>
                            <option value="Asuransi Lain" {{ old('insurance_type') == 'Asuransi Lain' ? 'selected' : '' }}>Asuransi Lain</option>
                        </select>
                        @error('insurance_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 d-none" id="insuranceNumberGroup">
                        <label class="form-label">No. Kartu BPJS <span class="text-danger">*</span></label>
                        <input type="text" name="insurance_number" class="form-control @error('insurance_number') is-invalid @enderror" value="{{ old('insurance_number') }}" placeholder="Nomor kartu BPJS">
                        @error('insurance_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
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
                                    {{ $doctor->name }}
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
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Daftarkan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('search_nik');
    const btnCari = document.getElementById('btnCari');
    const btnReset = document.getElementById('btnReset');
    const searchStatus = document.getElementById('searchStatus');
    const patientFound = document.getElementById('patientFound');
    const patientNotFound = document.getElementById('patientNotFound');
    const newPatientForm = document.getElementById('newPatientForm');
    const patientIdInput = document.getElementById('patient_id');
    const selectedPatientBadge = document.getElementById('selectedPatientBadge');
    const insuranceType = document.getElementById('insurance_type');
    const insuranceNumberGroup = document.getElementById('insuranceNumberGroup');
    const polyclinicSelect = document.getElementById('polyclinic_id');
    const doctorSelect = document.getElementById('doctor_id');

    const newPatientInputs = [
        'nik', 'name', 'birth_date',
        document.querySelector('input[name="phone"]'),
        document.querySelector('input[name="address"]'),
    ];
    const genderRadios = document.querySelectorAll('input[name="gender"]');

    function toggleNewPatientFields(enabled) {
        const inputs = newPatientInputs.filter(Boolean);
        inputs.forEach(el => { el.disabled = !enabled; });
        document.getElementById('nik').disabled = !enabled;
        document.getElementById('name').disabled = !enabled;
        document.getElementById('birth_date').disabled = !enabled;
        genderRadios.forEach(r => { r.disabled = !enabled; });
    }

    function resetSearch() {
        patientFound.classList.add('d-none');
        patientNotFound.classList.add('d-none');
        newPatientForm.classList.add('d-none');
        patientIdInput.value = '';
        selectedPatientBadge.classList.add('d-none');
        searchStatus.textContent = '';
        searchInput.disabled = false;
        searchInput.value = '';
        searchInput.focus();
        btnCari.classList.remove('d-none');
        btnReset.classList.add('d-none');
    }

    btnReset.addEventListener('click', resetSearch);

    function doSearch() {
        const nik = searchInput.value.trim();
        if (nik.length < 4) {
            searchStatus.textContent = 'Masukkan minimal 4 digit NIK';
            searchStatus.className = 'mt-1 small text-danger';
            return;
        }

        searchStatus.textContent = 'Mencari...';
        searchStatus.className = 'mt-1 small text-info';

        toggleNewPatientFields(false);

        fetch('{{ route("registration.search") }}?nik=' + encodeURIComponent(nik))
            .then(res => res.json())
            .then(data => {
                if (data.found) {
                    patientFound.classList.remove('d-none');
                    patientNotFound.classList.add('d-none');
                    newPatientForm.classList.add('d-none');
                    toggleNewPatientFields(false);

                    document.getElementById('patientName').textContent = data.data.name;
                    document.getElementById('patientRm').textContent = data.data.no_rm;
                    document.getElementById('patientNik').textContent = data.data.nik;

                    const badge = document.getElementById('patientInsurance');
                    badge.textContent = data.data.insurance_type;
                    badge.className = 'badge ' + (data.data.insurance_type === 'BPJS' ? 'bg-success' : 'bg-info');

                    patientIdInput.value = data.data.id;
                    selectedPatientBadge.textContent = data.data.name + ' (' + data.data.no_rm + ')';
                    selectedPatientBadge.classList.remove('d-none');
                    searchStatus.textContent = '';
                    searchInput.disabled = true;
                    btnCari.classList.add('d-none');
                    btnReset.classList.remove('d-none');

                    if (data.data.insurance_type === 'BPJS' && data.data.insurance_number) {
                        insuranceType.value = 'BPJS';
                        insuranceNumberGroup.classList.remove('d-none');
                        document.querySelector('input[name="insurance_number"]').value = data.data.insurance_number;
                    }
                } else {
                    toggleNewPatientFields(true);
                    patientFound.classList.add('d-none');
                    patientNotFound.classList.remove('d-none');
                    newPatientForm.classList.remove('d-none');
                    patientIdInput.value = '';
                    selectedPatientBadge.textContent = 'Pasien baru';
                    selectedPatientBadge.classList.remove('d-none');
                    searchStatus.textContent = '';
                    searchInput.disabled = true;
                    btnCari.classList.add('d-none');
                    btnReset.classList.remove('d-none');

                    document.getElementById('nik').value = nik;
                }
            })
            .catch(err => {
                searchStatus.textContent = 'Gagal mencari pasien';
                searchStatus.className = 'mt-1 small text-danger';
            });
    }

    btnCari.addEventListener('click', doSearch);
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            doSearch();
        }
    });

    insuranceType.addEventListener('change', function() {
        if (this.value === 'BPJS') {
            insuranceNumberGroup.classList.remove('d-none');
        } else {
            insuranceNumberGroup.classList.add('d-none');
        }
    });

    if (insuranceType.value === 'BPJS') {
        insuranceNumberGroup.classList.remove('d-none');
    }

    polyclinicSelect.addEventListener('change', function() {
        const polyId = this.value;
        for (const opt of doctorSelect.options) {
            if (opt.value === '') continue;
            opt.style.display = opt.dataset.polyclinic == polyId ? '' : 'none';
        }
        if (doctorSelect.selectedOptions[0]?.dataset?.polyclinic != polyId) {
            doctorSelect.value = '';
        }
    });
    polyclinicSelect.dispatchEvent(new Event('change'));
</script>
@endpush

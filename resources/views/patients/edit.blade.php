@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Pasien</h4>
        <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('patients.update', $patient) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Data Identitas</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $patient->nik) }}" maxlength="16" required>
                        @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. KK</label>
                        <input type="text" name="no_kk" class="form-control @error('no_kk') is-invalid @enderror" value="{{ old('no_kk', $patient->no_kk) }}">
                        @error('no_kk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $patient->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror" value="{{ old('birth_place', $patient->birth_place) }}">
                        @error('birth_place') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', $patient->birth_date?->format('Y-m-d')) }}" required>
                        @error('birth_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="L" {{ old('gender', $patient->gender) == 'L' ? 'checked' : '' }} id="genderL" required>
                                <label class="form-check-label" for="genderL">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="P" {{ old('gender', $patient->gender) == 'P' ? 'checked' : '' }} id="genderP">
                                <label class="form-check-label" for="genderP">Perempuan</label>
                            </div>
                        </div>
                        @error('gender') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Golongan Darah</label>
                        <select name="blood_type" class="form-select @error('blood_type') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="A" {{ old('blood_type', $patient->blood_type) == 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('blood_type', $patient->blood_type) == 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('blood_type', $patient->blood_type) == 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('blood_type', $patient->blood_type) == 'O' ? 'selected' : '' }}>O</option>
                        </select>
                        @error('blood_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Alamat</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $patient->address) }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RT</label>
                        <input type="text" name="rt" class="form-control @error('rt') is-invalid @enderror" value="{{ old('rt', $patient->rt) }}">
                        @error('rt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">RW</label>
                        <input type="text" name="rw" class="form-control @error('rw') is-invalid @enderror" value="{{ old('rw', $patient->rw) }}">
                        @error('rw') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelurahan</label>
                        <input type="text" name="village" class="form-control @error('village') is-invalid @enderror" value="{{ old('village', $patient->village) }}">
                        @error('village') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="district" class="form-control @error('district') is-invalid @enderror" value="{{ old('district', $patient->district) }}">
                        @error('district') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kota</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $patient->city) }}">
                        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Provinsi</label>
                        <input type="text" name="province" class="form-control @error('province') is-invalid @enderror" value="{{ old('province', $patient->province) }}">
                        @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Kontak & Lainnya</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Telepon</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $patient->phone) }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $patient->email) }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror" value="{{ old('occupation', $patient->occupation) }}">
                        @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status Pernikahan</label>
                        <select name="marriage_status" class="form-select @error('marriage_status') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Belum Kawin" {{ old('marriage_status', $patient->marriage_status) == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                            <option value="Kawin" {{ old('marriage_status', $patient->marriage_status) == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                            <option value="Cerai" {{ old('marriage_status', $patient->marriage_status) == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                        </select>
                        @error('marriage_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Agama</label>
                        <select name="religion" class="form-select @error('religion') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Islam" {{ old('religion', $patient->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('religion', $patient->religion) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('religion', $patient->religion) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('religion', $patient->religion) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('religion', $patient->religion) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('religion', $patient->religion) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                        @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jenis Asuransi</label>
                        <select name="insurance_type" class="form-select @error('insurance_type') is-invalid @enderror">
                            <option value="">-- Pilih --</option>
                            <option value="Umum" {{ old('insurance_type', $patient->insurance_type) == 'Umum' ? 'selected' : '' }}>Umum</option>
                            <option value="BPJS" {{ old('insurance_type', $patient->insurance_type) == 'BPJS' ? 'selected' : '' }}>BPJS</option>
                            <option value="Asuransi Lain" {{ old('insurance_type', $patient->insurance_type) == 'Asuransi Lain' ? 'selected' : '' }}>Asuransi Lain</option>
                        </select>
                        @error('insurance_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. Asuransi</label>
                        <input type="text" name="insurance_number" class="form-control @error('insurance_number') is-invalid @enderror" value="{{ old('insurance_number', $patient->insurance_number) }}">
                        @error('insurance_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('patients.show', $patient) }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection

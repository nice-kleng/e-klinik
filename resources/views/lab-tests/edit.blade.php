@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Edit Tes Laboratorium</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('lab-tests.update', $labTest) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $labTest->code) }}" required maxlength="20">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Tes <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $labTest->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $labTest->category_id) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Spesimen</label>
                        <input type="text" name="specimen_type" class="form-control" value="{{ old('specimen_type', $labTest->specimen_type) }}" maxlength="50">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="unit" class="form-control" value="{{ old('unit', $labTest->unit) }}" maxlength="30">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Semua</option>
                            <option value="L" @selected(old('gender', $labTest->gender) == 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender', $labTest->gender) == 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Usia Min</label>
                        <input type="number" name="age_min" class="form-control" value="{{ old('age_min', $labTest->age_min) }}" min="0">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Usia Max</label>
                        <input type="number" name="age_max" class="form-control" value="{{ old('age_max', $labTest->age_max) }}" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rujukan Rendah</label>
                        <input type="text" name="ref_range_low" class="form-control" value="{{ old('ref_range_low', $labTest->ref_range_low) }}" maxlength="50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rujukan Tinggi</label>
                        <input type="text" name="ref_range_high" class="form-control" value="{{ old('ref_range_high', $labTest->ref_range_high) }}" maxlength="50">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rujukan (Teks)</label>
                        <input type="text" name="ref_range_text" class="form-control" value="{{ old('ref_range_text', $labTest->ref_range_text) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price', $labTest->price) }}" min="0" step="0.01">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Kode LOINC</label>
                        <input type="text" name="loinc_code" class="form-control" value="{{ old('loinc_code', $labTest->loinc_code) }}" maxlength="20">
                    </div>
                    <div class="col-md-12">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" @checked($labTest->is_active)>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('lab-tests.show', $labTest) }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

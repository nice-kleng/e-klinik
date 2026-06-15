@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Tambah Obat</h4>
        <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('medicines.store') }}">
        @csrf
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Obat <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Obat <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Generik</label>
                        <input type="text" name="generic_name" class="form-control @error('generic_name') is-invalid @enderror" value="{{ old('generic_name') }}">
                        @error('generic_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Produsen</label>
                        <input type="text" name="manufacturer" class="form-control @error('manufacturer') is-invalid @enderror" value="{{ old('manufacturer') }}">
                        @error('manufacturer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                            <option value="">-- Pilih Satuan --</option>
                            <option value="Tablet" {{ old('unit') == 'Tablet' ? 'selected' : '' }}>Tablet</option>
                            <option value="Kapsul" {{ old('unit') == 'Kapsul' ? 'selected' : '' }}>Kapsul</option>
                            <option value="Botol" {{ old('unit') == 'Botol' ? 'selected' : '' }}>Botol</option>
                            <option value="Ampul" {{ old('unit') == 'Ampul' ? 'selected' : '' }}>Ampul</option>
                            <option value="Tube" {{ old('unit') == 'Tube' ? 'selected' : '' }}>Tube</option>
                            <option value="Strip" {{ old('unit') == 'Strip' ? 'selected' : '' }}>Strip</option>
                            <option value="Box" {{ old('unit') == 'Box' ? 'selected' : '' }}>Box</option>
                            <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>ml</option>
                            <option value="mg" {{ old('unit') == 'mg' ? 'selected' : '' }}>mg</option>
                            <option value="Pcs" {{ old('unit') == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kandungan</label>
                        <input type="text" name="content" class="form-control @error('content') is-invalid @enderror" value="{{ old('content') }}" placeholder="Contoh: 500mg">
                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="is_generic" value="1" id="is_generic" {{ old('is_generic') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_generic">Obat Generik</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch mt-4">
                            <input class="form-check-input" type="checkbox" name="requires_prescription" value="1" id="requires_prescription" {{ old('requires_prescription') ? 'checked' : '' }}>
                            <label class="form-check-label" for="requires_prescription">Perlu Resep</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-3">
        <h4 class="mb-0">Tambah Kategori Pemeriksaan Lab</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('lab-test-categories.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required maxlength="20">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('lab-test-categories.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

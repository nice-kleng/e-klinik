@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">ICD-10 Diagnosa</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Cari Diagnosis</label>
                    <input type="text" name="search" class="form-control" placeholder="Kode atau nama diagnosis..." value="{{ $keyword }}" autofocus>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('diagnoses.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if($keyword)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Hasil Pencarian: "{{ $keyword }}"</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="120">Kode</th>
                            <th>Nama Diagnosis</th>
                            <th>Kategori</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diagnoses as $diagnosis)
                            <tr>
                                <td><strong>{{ $diagnosis->code }}</strong></td>
                                <td>{{ $diagnosis->name }}</td>
                                <td>{{ $diagnosis->category ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Diagnosis tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $diagnoses->links() }}
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="text-muted">Silakan cari diagnosis berdasarkan kode atau nama</h5>
                <p class="text-muted">Contoh: A00, Diabetes, Hipertensi</p>
            </div>
        </div>
    @endif
</div>
@endsection

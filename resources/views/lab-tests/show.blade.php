@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $labTest->name }}</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('lab-tests.edit', $labTest) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('lab-tests.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Informasi Tes</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:140px">Kode</th><td>{{ $labTest->code }}</td></tr>
                        <tr><th>Nama Tes</th><td>{{ $labTest->name }}</td></tr>
                        <tr><th>Kategori</th><td>{{ $labTest->category->name ?? '-' }}</td></tr>
                        <tr><th>Spesimen</th><td>{{ $labTest->specimen_type ?? '-' }}</td></tr>
                        <tr><th>Satuan</th><td>{{ $labTest->unit ?? '-' }}</td></tr>
                        <tr><th>Gender</th><td>{{ $labTest->gender ? ($labTest->gender == 'L' ? 'Laki-laki' : 'Perempuan') : 'Semua' }}</td></tr>
                        <tr><th>Usia</th><td>{{ $labTest->age_min ?? '-' }} - {{ $labTest->age_max ?? '-' }} tahun</td></tr>
                        <tr><th>Harga</th><td>Rp {{ number_format($labTest->price, 0, ',', '.') }}</td></tr>
                        <tr><th>Kode LOINC</th><td>{{ $labTest->loinc_code ?? '-' }}</td></tr>
                        <tr><th>Status</th><td>{!! $labTest->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Nilai Rujukan</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:140px">Rendah</th><td>{{ $labTest->ref_range_low ?? '-' }}</td></tr>
                        <tr><th>Tinggi</th><td>{{ $labTest->ref_range_high ?? '-' }}</td></tr>
                        <tr><th>Teks Rujukan</th><td>{{ $labTest->ref_range_text ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

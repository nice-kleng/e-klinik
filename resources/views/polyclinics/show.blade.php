@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Poliklinik</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('polyclinics.edit', $polyclinic) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('polyclinics.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Poliklinik</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Kode</td>
                            <td><strong>{{ $polyclinic->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama</td>
                            <td>{{ $polyclinic->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Lokasi</td>
                            <td>{{ $polyclinic->location ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($polyclinic->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($polyclinic->description)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Deskripsi</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $polyclinic->description }}</p></div>
                </div>
            @endif
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Dokter</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Spesialis</th>
                                <th>SIP</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($polyclinic->doctors as $doctor)
                                <tr>
                                    <td>{{ $doctor->code }}</td>
                                    <td>{{ $doctor->name }}</td>
                                    <td>{{ $doctor->specialist ?? '-' }}</td>
                                    <td>{{ $doctor->sip_number ?? '-' }}</td>
                                    <td>
                                        @if($doctor->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada dokter</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

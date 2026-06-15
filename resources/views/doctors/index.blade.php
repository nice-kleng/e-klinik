@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Dokter</h4>
        <a href="{{ route('doctors.create') }}" class="btn btn-primary">+ Tambah Dokter</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Dokter</th>
                        <th>Poliklinik</th>
                        <th>Spesialis</th>
                        <th>SIP</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <td>{{ $doctor->code }}</td>
                            <td>{{ $doctor->name }}</td>
                            <td>{{ $doctor->polyclinic->name ?? '-' }}</td>
                            <td>{{ $doctor->specialist ?? '-' }}</td>
                            <td>{{ $doctor->sip_number ?? '-' }}</td>
                            <td>{{ $doctor->phone ?? '-' }}</td>
                            <td>
                                @if($doctor->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('doctors.show', $doctor) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data dokter</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $doctors->links() }}
        </div>
    </div>
</div>
@endsection

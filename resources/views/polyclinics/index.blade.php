@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Poliklinik</h4>
        <a href="{{ route('polyclinics.create') }}" class="btn btn-primary">+ Tambah Poliklinik</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Poliklinik</th>
                        <th>Lokasi</th>
                        <th>Jumlah Dokter</th>
                        <th>Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($polyclinics as $poly)
                        <tr>
                            <td><strong>{{ $poly->code }}</strong></td>
                            <td>{{ $poly->name }}</td>
                            <td>{{ $poly->location ?? '-' }}</td>
                            <td>{{ $poly->doctors_count }}</td>
                            <td>
                                @if($poly->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('polyclinics.show', $poly) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('polyclinics.edit', $poly) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data poliklinik</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $polyclinics->links() }}
        </div>
    </div>
</div>
@endsection

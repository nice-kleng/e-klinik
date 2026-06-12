@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Pasien</h4>
        <a href="{{ route('patients.create') }}" class="btn btn-primary">
            + Tambah Pasien
        </a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama, NIK, atau No. RM" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No. RM</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Jenis Kelamin</th>
                        <th>Telepon</th>
                        <th>BPJS</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td>{{ $patient->no_rm }}</td>
                            <td>{{ $patient->name }}</td>
                            <td>{{ $patient->nik }}</td>
                            <td>{{ $patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td>{{ $patient->phone ?? '-' }}</td>
                            <td>
                                @if($patient->bpjsPatient)
                                    <span class="badge bg-success">BPJS</span>
                                @else
                                    <span class="badge bg-secondary">Non BPJS</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-warning">Edit</a>
                                <a href="{{ route('patients.print-card', $patient) }}" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <form action="{{ route('patients.destroy', $patient) }}" method="POST" class="d-inline" data-confirm="Hapus pasien ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data pasien</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $patients->links() }}
        </div>
    </div>
</div>
@endsection

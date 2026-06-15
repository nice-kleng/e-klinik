@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Rekam Medis</h4>
        <a href="{{ route('medical-records.create') }}" class="btn btn-primary">+ Tambah Rekam Medis</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Pasien</label>
                    <select name="patient_id" class="form-select">
                        <option value="">Semua Pasien</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ request('patient_id') == $patient->id ? 'selected' : '' }}>
                                [{{ $patient->no_rm }}] {{ $patient->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                    <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary w-100 mt-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Poliklinik</th>
                        <th>Diagnosis Utama</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->visit_date?->format('d/m/Y') }}</td>
                            <td>{{ $record->patient->name ?? '-' }}<br><small class="text-muted">{{ $record->patient->no_rm ?? '' }}</small></td>
                            <td>{{ $record->doctor->name ?? '-' }}</td>
                            <td>{{ $record->polyclinic->name ?? '-' }}</td>
                            <td>{{ $record->diagnosis_primary ?? '-' }}</td>
                            <td>
                                <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data rekam medis</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection

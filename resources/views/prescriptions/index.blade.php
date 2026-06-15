@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Resep</h4>
        <a href="{{ route('prescriptions.create') }}" class="btn btn-primary">+ Tambah Resep</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                        <th>No. Resep</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr>
                            <td><strong>{{ $prescription->prescription_number }}</strong></td>
                            <td>{{ $prescription->patient->name ?? '-' }}<br><small class="text-muted">{{ $prescription->patient->no_rm ?? '' }}</small></td>
                            <td>{{ $prescription->doctor->name ?? '-' }}</td>
                            <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                            <td>
                                @php
                                    $badge = match($prescription->status) {
                                        'active' => 'bg-success', 'dispensed' => 'bg-info',
                                        'cancelled' => 'bg-secondary', default => 'bg-warning'
                                    };
                                    $label = match($prescription->status) {
                                        'active' => 'Aktif', 'dispensed' => 'Diberikan',
                                        'cancelled' => 'Dibatalkan', default => $prescription->status
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $label }}</span>
                            </td>
                            <td>
                                <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('prescriptions.print', $prescription) }}" class="btn btn-sm btn-secondary" target="_blank">Cetak</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data resep</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $prescriptions->links() }}
        </div>
    </div>
</div>
@endsection

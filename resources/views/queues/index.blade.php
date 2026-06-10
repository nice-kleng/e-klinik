@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Antrean</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('queues.display') }}" class="btn btn-info" target="_blank">Display Antrean</a>
            <a href="{{ route('queues.create') }}" class="btn btn-primary">+ Tambah Antrean</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Poliklinik</label>
                    <select name="polyclinic_id" class="form-select">
                        <option value="">Semua Poli</option>
                        @foreach($polyclinics as $poly)
                            <option value="{{ $poly->id }}" {{ $polyclinicId == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="waiting" {{ $status == 'waiting' ? 'selected' : '' }}>Menunggu</option>
                        <option value="called" {{ $status == 'called' ? 'selected' : '' }}>Dipanggil</option>
                        <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>Diproses</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No. Antrean</th>
                        <th>Pasien</th>
                        <th>Poliklinik</th>
                        <th>Dokter</th>
                        <th>Jenis Layanan</th>
                        <th>Status</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $queue)
                        <tr>
                            <td><strong>{{ $queue->queue_number }}</strong></td>
                            <td>{{ $queue->patient->name ?? '-' }}</td>
                            <td>{{ $queue->polyclinic->name ?? '-' }}</td>
                            <td>{{ $queue->doctor->name ?? '-' }}</td>
                            <td>{{ $queue->service_type ?? '-' }}</td>
                            <td>
                                @php
                                    $statusBadge = match($queue->status) {
                                        'waiting' => 'bg-warning',
                                        'called' => 'bg-info',
                                        'in_progress' => 'bg-primary',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($queue->status) {
                                        'waiting' => 'Menunggu',
                                        'called' => 'Dipanggil',
                                        'in_progress' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $queue->status
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if($queue->status == 'waiting')
                                    <form action="{{ route('queues.call', $queue) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-info">Panggil</button>
                                    </form>
                                @endif
                                @if(in_array($queue->status, ['waiting', 'called']))
                                    <form action="{{ route('queues.in-progress', $queue) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Proses</button>
                                    </form>
                                @endif
                                @if(in_array($queue->status, ['waiting', 'called', 'in_progress']))
                                    <form action="{{ route('queues.complete', $queue) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Selesai</button>
                                    </form>
                                @endif
                                @if(in_array($queue->status, ['waiting', 'called']))
                                    <form action="{{ route('queues.cancel', $queue) }}" method="POST" class="d-inline" data-confirm="Batalkan antrean ini?">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">Batal</button>
                                    </form>
                                @endif
                                <a href="{{ route('queues.show', $queue) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada antrean</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $queues->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Permintaan Laboratorium</h4>
        <a href="{{ route('lab-requests.create') }}" class="btn btn-primary">+ Permintaan Baru</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cari Pasien</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama atau No. RM..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="requested" @selected(request('status') == 'requested')>Diminta</option>
                        <option value="sampled" @selected(request('status') == 'sampled')>Sampel Diambil</option>
                        <option value="processing" @selected(request('status') == 'processing')>Diproses</option>
                        <option value="completed" @selected(request('status') == 'completed')>Selesai</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('lab-requests.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>No. RM</th>
                        <th>Jumlah Tes</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $req->patient->name ?? '-' }}</td>
                            <td>{{ $req->patient->no_rm ?? '-' }}</td>
                            <td>{{ $req->items->count() }}</td>
                            <td>
                                @php
                                    $statusClass = match($req->status) {
                                        'requested' => 'bg-secondary',
                                        'sampled' => 'bg-info',
                                        'processing' => 'bg-warning',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($req->status) {
                                        'requested' => 'Diminta',
                                        'sampled' => 'Sampel Diambil',
                                        'processing' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $req->status
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <a href="{{ route('lab-requests.show', $req) }}" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada permintaan laboratorium</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection

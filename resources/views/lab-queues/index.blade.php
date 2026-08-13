@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-flask me-1"></i>Antrian Laboratorium</h4>
        <form method="GET" class="d-inline-flex align-items-center gap-2">
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" onchange="this.form.submit()">
        </form>
    </div>

    @include('components.alert')

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-secondary">{{ $stats['waiting'] }}</div>
                    <div class="text-muted small">Menunggu</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-info">{{ $stats['called'] }}</div>
                    <div class="text-muted small">Dipanggil</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-warning">{{ $stats['in_progress'] }}</div>
                    <div class="text-muted small">Diproses</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-success">{{ $stats['completed'] }}</div>
                    <div class="text-muted small">Selesai</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="fs-3 fw-bold text-danger">{{ $stats['cancelled'] }}</div>
                    <div class="text-muted small">Dibatalkan</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-2 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#callModal">
                <i class="fas fa-phone me-1"></i>Panggil Berikutnya
            </button>
        </div>
    </div>

    @if($current)
    <div class="card border-0 shadow-sm mb-3 border-start border-4 border-primary">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <div class="text-muted small">SEDANG DIPANGGIL</div>
                <div class="fs-4 fw-bold">No. {{ $current->queue_number }}</div>
                <div class="fs-5">{{ $current->registration?->patient?->name ?? '-' }}</div>
                <div class="text-muted small">{{ $current->registration?->polyclinic?->name ?? '-' }}</div>
            </div>
            <form method="POST" action="{{ route('lab-queues.complete', $current) }}"
                  onsubmit="return confirm('Tandai antrian ini selesai?')">
                @csrf
                <button class="btn btn-success btn-lg"><i class="fas fa-check me-1"></i>Selesai</button>
            </form>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Daftar Antrian</h6>
            <span class="badge bg-primary">{{ $queues->count() }} pasien</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Pasien</th>
                        <th>No. RM</th>
                        <th>Poli</th>
                        <th>Status</th>
                        <th>Dipanggil Oleh</th>
                        <th width="130">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $q)
                        <tr class="{{ $q->status == 'completed' ? 'opacity-50' : '' }}">
                            <td class="fw-bold">{{ $q->queue_number }}</td>
                            <td>{{ $q->registration?->patient?->name ?? '-' }}</td>
                            <td>{{ $q->registration?->patient?->no_rm ?? '-' }}</td>
                            <td>{{ $q->registration?->polyclinic?->name ?? '-' }}</td>
                            <td>
                                @php
                                    $statusClass = match($q->status) {
                                        'waiting' => 'bg-secondary',
                                        'called' => 'bg-info',
                                        'in_progress' => 'bg-warning',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($q->status) {
                                        'waiting' => 'Menunggu',
                                        'called' => 'Dipanggil',
                                        'in_progress' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $q->status
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>{{ $q->calledBy?->name ?? '-' }}</td>
                            <td>
                                @if(in_array($q->status, ['called', 'in_progress']))
                                    <form method="POST" action="{{ route('lab-queues.complete', $q) }}" class="d-inline"
                                          onsubmit="return confirm('Tandai antrian ini selesai?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                    </form>
                                @endif
                                @if(in_array($q->status, ['waiting', 'called']))
                                    <form method="POST" action="{{ route('lab-queues.cancel', $q) }}" class="d-inline"
                                          onsubmit="return confirm('Batalkan antrian ini?')">
                                        @csrf
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada antrian laboratorium</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('lab-queues.call-next') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Panggil Pasien Berikutnya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($waiting->count() > 0)
                    Pasien berikutnya: <strong>No. {{ $waiting->first()->queue_number }}</strong> — {{ $waiting->first()->registration?->patient?->name ?? '-' }}
                @else
                    Tidak ada pasien yang menunggu.
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" {{ $waiting->count() > 0 ? '' : 'disabled' }}>Panggil</button>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Dokter</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('doctors.edit', $doctor) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('doctors.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Dokter</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Kode</td>
                            <td><strong>{{ $doctor->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama</td>
                            <td>{{ $doctor->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Poliklinik</td>
                            <td>{{ $doctor->polyclinic->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Spesialis</td>
                            <td>{{ $doctor->specialist ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. SIP</td>
                            <td>{{ $doctor->sip_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td>{{ $doctor->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($doctor->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Riwayat Antrean</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Antrean</th>
                                <th>Pasien</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($doctor->queues as $queue)
                                <tr>
                                    <td>{{ $queue->queue_date?->format('d/m/Y') }}</td>
                                    <td>{{ $queue->queue_number }}</td>
                                    <td>{{ $queue->registration?->patient?->name ?? '-' }}</td>
                                    <td>
                                        @php
                                            $badge = match($queue->status) {
                                                'waiting' => 'bg-warning', 'called' => 'bg-info',
                                                'in_progress' => 'bg-primary', 'completed' => 'bg-success',
                                                'cancelled' => 'bg-secondary', default => 'bg-secondary'
                                            };
                                            $label = match($queue->status) {
                                                'waiting' => 'Menunggu', 'called' => 'Dipanggil',
                                                'in_progress' => 'Diproses', 'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan', default => $queue->status
                                            };
                                        @endphp
                                        <span class="badge {{ $badge }}">{{ $label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Belum ada antrean</td>
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

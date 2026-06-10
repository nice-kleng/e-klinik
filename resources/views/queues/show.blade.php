@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Antrean</h4>
        <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Antrean</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:160px">No. Antrean</td>
                            <td><strong>{{ $queue->queue_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $queue->queue_date?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Poliklinik</td>
                            <td>{{ $queue->polyclinic->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dokter</td>
                            <td>{{ $queue->doctor->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Layanan</td>
                            <td>{{ $queue->service_type ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
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
                        @if($queue->check_in_at)
                            <tr><td class="text-muted">Check In</td><td>{{ $queue->check_in_at->format('d/m/Y H:i') }}</td></tr>
                        @endif
                        @if($queue->called_at)
                            <tr><td class="text-muted">Dipanggil</td><td>{{ $queue->called_at->format('d/m/Y H:i') }}</td></tr>
                        @endif
                        @if($queue->completed_at)
                            <tr><td class="text-muted">Selesai</td><td>{{ $queue->completed_at->format('d/m/Y H:i') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Pasien</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:160px">Nama</td>
                            <td>{{ $queue->patient->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. RM</td>
                            <td>{{ $queue->patient->no_rm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $queue->patient->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td>{{ $queue->patient->phone ?? '-' }}</td>
                        </tr>
                    </table>
                    <a href="{{ route('patients.show', $queue->patient) }}" class="btn btn-sm btn-outline-info mt-2">Detail Pasien</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Aksi</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($queue->status == 'waiting')
                            <form action="{{ route('queues.call', $queue) }}" method="POST">
                                @csrf
                                <button class="btn btn-info">Panggil</button>
                            </form>
                        @endif
                        @if(in_array($queue->status, ['waiting', 'called']))
                            <form action="{{ route('queues.in-progress', $queue) }}" method="POST">
                                @csrf
                                <button class="btn btn-primary">Proses</button>
                            </form>
                        @endif
                        @if(in_array($queue->status, ['waiting', 'called', 'in_progress']))
                            <form action="{{ route('queues.complete', $queue) }}" method="POST">
                                @csrf
                                <button class="btn btn-success">Selesai</button>
                            </form>
                        @endif
                        @if(in_array($queue->status, ['waiting', 'called']))
                            <form action="{{ route('queues.cancel', $queue) }}" method="POST" data-confirm="Batalkan antrean ini?">
                                @csrf
                                <button class="btn btn-danger">Batalkan</button>
                            </form>
                        @endif
                    </div>

                    @if($queue->medicalRecord)
                        <hr>
                        <h6>Rekam Medis</h6>
                        <p class="mb-1">Telah dibuat pada {{ $queue->medicalRecord->created_at->format('d/m/Y H:i') }}</p>
                        <a href="{{ route('medical-records.show', $queue->medicalRecord) }}" class="btn btn-sm btn-info">Lihat Rekam Medis</a>
                    @elseif(in_array($queue->status, ['in_progress', 'completed']))
                        <hr>
                        <a href="{{ route('medical-records.create', ['queue_id' => $queue->id, 'patient_id' => $queue->patient_id]) }}" class="btn btn-primary">Buat Rekam Medis</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

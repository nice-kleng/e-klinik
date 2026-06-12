@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Antrean</h4>
        <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    @php $reg = $queue->registration; @endphp

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Antrean</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted" width="150">No. Antrean</td>
                            <td><strong>{{ $queue->queue_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Registrasi</td>
                            <td>{{ $reg?->registration_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $queue->queue_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Poliklinik</td>
                            <td>{{ $queue->polyclinic?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dokter</td>
                            <td>{{ $reg?->doctor?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sumber</td>
                            <td><span class="badge bg-{{ $queue->source == 'mjkn' ? 'primary' : 'secondary' }}">{{ $queue->source }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php
                                    $badge = match($queue->status) {
                                        'waiting' => 'bg-warning',
                                        'called' => 'bg-info',
                                        'in_progress' => 'bg-primary',
                                        'completed' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $queue->status }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Check In</td>
                            <td>{{ $queue->check_in_at?->format('H:i:s') ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Pasien</h6>
                </div>
                <div class="card-body">
                    @if($reg?->patient)
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted" width="150">No. RM</td>
                            <td><strong>{{ $reg->patient->no_rm }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $reg->patient->nik }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama</td>
                            <td>{{ $reg->patient->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tgl Lahir</td>
                            <td>{{ $reg->patient->birth_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Umur</td>
                            <td>{{ $reg->age_text }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">JK</td>
                            <td>{{ $reg->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td>{{ $reg->patient->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Bayar</td>
                            <td>{{ $reg->patient->insurance_type ?? '-' }}</td>
                        </tr>
                    </table>
                    @else
                        <p class="text-muted">Data pasien tidak tersedia</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Riwayat Panggilan</h6>
                </div>
                <div class="card-body p-0">
                    @if($queue->queueCalls->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada panggilan</div>
                    @else
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Dipanggil Oleh</th>
                                    <th>Waktu Panggil</th>
                                    <th>Waktu Respon</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($queue->queueCalls as $call)
                                <tr>
                                    <td>{{ $call->call_sequence }}</td>
                                    <td>{{ $call->caller?->name ?? '-' }}</td>
                                    <td>{{ $call->called_at?->format('H:i:s') }}</td>
                                    <td>{{ $call->responded_at?->format('H:i:s') ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

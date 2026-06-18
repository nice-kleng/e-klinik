@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @php $reg = $queue->registration; $patient = $reg?->patient; @endphp

    {{-- Header Identitas Pasien --}}
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1">{{ $patient?->name ?? 'Pasien' }}</h3>
                    <div class="d-flex gap-3">
                        <span><strong>No. RM:</strong> {{ $patient?->no_rm ?? '-' }}</span>
                        <span><strong>Tgl Lahir:</strong> {{ $patient?->birth_date?->format('d/m/Y') ?? '-' }}</span>
                        <span><strong>Umur:</strong> {{ $reg?->age_text ?? $patient?->age ?? '-' }}</span>
                        <span><strong>JK:</strong> {{ $patient?->gender == 'L' ? 'Laki-laki' : ($patient?->gender == 'P' ? 'Perempuan' : '-') }}</span>
                        <span><strong>NIK:</strong> {{ $patient?->nik ?? '-' }}</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    @if($queue->medicalRecord)
                        <a href="{{ route('medical-records.show', $queue->medicalRecord) }}" class="btn btn-light">
                            <i class="fas fa-file-medical me-1"></i>Lihat RME
                        </a>
                    @elseif(in_array($queue->status, ['in_progress', 'completed']))
                        <a href="{{ route('medical-records.create', ['queue_id' => $queue->id]) }}" class="btn btn-light">
                            <i class="fas fa-plus-circle me-1"></i>Buat Rekam Medis
                        </a>
                    @endif
                    <a href="{{ route('queues.index') }}" class="btn btn-outline-light">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-3">
        {{-- Informasi Kunjungan --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-1"></i>Informasi Kunjungan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="150">No. Antrean</td>
                            <td><strong>{{ $queue->queue_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Registrasi</td>
                            <td>{{ $reg?->registration_number ?? '-' }}</td>
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
                            <td><span class="badge bg-{{ $queue->source == 'mjkn' ? 'primary' : 'secondary' }}">{{ $queue->source == 'mjkn' ? 'MJKN' : 'Walk-in' }}</span></td>
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
                                    $label = match($queue->status) {
                                        'waiting' => 'Menunggu',
                                        'called' => 'Dipanggil',
                                        'in_progress' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $queue->status
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Check In</td>
                            <td>{{ $queue->check_in_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Kunjungan</td>
                            <td>{{ $queue->queue_date->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Bayar</td>
                            <td>{{ $patient?->insurance_type ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Riwayat Panggilan --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-phone-alt me-1"></i>Riwayat Panggilan</h6>
                </div>
                <div class="card-body">
                    @if($queue->queueCalls->isEmpty())
                        <div class="text-center py-3 text-muted">Belum ada panggilan</div>
                    @else
                        <div class="table-responsive">
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
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Riwayat Pemeriksaan --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i>Riwayat Pemeriksaan</h6>
                    @if($patient)
                    <a href="{{ route('medical-records.index', ['patient_id' => $patient->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Semua Riwayat
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($previousRecords->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Belum ada riwayat pemeriksaan untuk pasien ini
                        </div>
                    @else
                        <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Registrasi</th>
                                    <th>Poliklinik</th>
                                    <th>Dokter</th>
                                    <th>Diagnosis Utama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previousRecords as $record)
                                <tr>
                                    <td>{{ $record->visit_date?->format('d/m/Y') }}</td>
                                    <td>{{ $record->registration?->registration_number ?? '-' }}</td>
                                    <td>{{ $record->polyclinic?->name ?? '-' }}</td>
                                    <td>{{ $record->doctor?->name ?? '-' }}</td>
                                    <td>
                                        @if($record->diagnosis_primary)
                                            <span class="badge bg-info me-1">{{ $record->diagnosis_primary }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

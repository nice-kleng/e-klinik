@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @php
        $reg = $queue->registration;
        $patient = $reg?->patient;
        $mr = $queue->medicalRecord;
        $initial = strtoupper(substr($patient?->name ?? 'P', 0, 1));
    @endphp

    @include('components.alert')

    {{-- Card Identitas Pasien --}}
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #28a745;">
        <div class="card-body">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="flex-shrink-0 rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                     style="width:60px;height:60px;font-size:1.5rem;font-weight:700;">
                    {{ $initial }}
                </div>
                <div class="flex-grow-1">
                    <h3 class="mb-0">{{ $patient?->name ?? 'Pasien' }}</h3>
                    <div class="d-flex gap-3 flex-wrap text-muted small mt-1">
                        <span><strong>No. RM:</strong> {{ $patient?->no_rm ?? '-' }}</span>
                        <span><strong>NIK:</strong> {{ $patient?->nik ?? '-' }}</span>
                        <span><strong>Tgl Lahir:</strong> {{ $patient?->birth_date?->format('d/m/Y') ?? '-' }}</span>
                        <span><strong>Umur:</strong> {{ $reg?->age_text ?? $patient?->age ?? '-' }}</span>
                        <span><strong>JK:</strong> {{ $patient?->gender == 'L' ? 'Laki-laki' : ($patient?->gender == 'P' ? 'Perempuan' : '-') }}</span>
                    </div>
                </div>
            </div>

            <hr class="my-3">

            {{-- Info Kunjungan --}}
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">Poliklinik</small>
                    <span class="fw-medium">{{ $queue->polyclinic?->name ?? $reg?->polyclinic?->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Dokter</small>
                    <span class="fw-medium">{{ $reg?->doctor?->name ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Tanggal Kunjungan</small>
                    <span class="fw-medium">{{ $queue->queue_date?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Jenis Kunjungan</small>
                    <span class="fw-medium">{{ $reg?->visit_type ?? '-' }}</span>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex gap-2 justify-content-end border-top pt-3">
                @if($mr)
                    <a href="{{ route('medical-records.show', $mr) }}" class="btn btn-success">
                        <i class="fas fa-file-medical me-1"></i>Lihat RME Kunjungan Ini
                    </a>
                @else
                    <a href="{{ route('medical-records.create', ['queue_id' => $queue->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i>Buat Rekam Medis
                    </a>
                @endif
                <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Tanda-Tanda Vital --}}
    @if($mr && $mr->vital_signs)
        @php $vs = $mr->vital_signs; @endphp
        @php
            $vitals = [
                ['label' => 'TD', 'unit' => 'mmHg', 'key' => 'blood_pressure', 'icon' => 'fa-heart-pulse'],
                ['label' => 'Nadi', 'unit' => '/menit', 'key' => 'heart_rate', 'icon' => 'fa-heart'],
                ['label' => 'Suhu', 'unit' => '°C', 'key' => 'temperature', 'icon' => 'fa-temperature-high'],
                ['label' => 'RR', 'unit' => '/menit', 'key' => 'respiratory_rate', 'icon' => 'fa-lungs'],
                ['label' => 'SpO₂', 'unit' => '%', 'key' => 'oxygen_saturation', 'icon' => 'fa-droplet'],
                ['label' => 'BB', 'unit' => 'kg', 'key' => 'weight', 'icon' => 'fa-weight-scale'],
                ['label' => 'TB', 'unit' => 'cm', 'key' => 'height', 'icon' => 'fa-ruler-vertical'],
                ['label' => 'GCS', 'unit' => '', 'key' => 'gcs', 'icon' => 'fa-brain'],
                ['label' => 'Gula Darah', 'unit' => 'mg/dL', 'key' => 'blood_glucose', 'icon' => 'fa-droplet'],
            ];
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0"><i class="fas fa-heartbeat me-1 text-danger"></i>Tanda-Tanda Vital</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($vitals as $v)
                        @php $val = $vs[$v['key']] ?? null; @endphp
                        <div class="col-4 col-md-3 col-lg-{{ $v['key'] === 'blood_glucose' ? '4' : '2' }}">
                            <div class="border rounded-3 p-2 text-center h-100 {{ $val ? 'bg-light' : 'bg-white' }}">
                                <div class="text-muted small mb-1">
                                    <i class="fas {{ $v['icon'] }} me-1"></i>{{ $v['label'] }}
                                </div>
                                <div class="fw-bold fs-5 {{ $val ? 'text-dark' : 'text-muted' }}">
                                    {{ $val ?? '-' }}
                                    @if($val && $v['unit'])
                                        <small class="fw-normal text-muted">{{ $v['unit'] }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @if(!empty($vs['notes']))
                    <div class="mt-2 p-2 bg-light rounded small">
                        <strong class="text-muted">Catatan:</strong> {{ $vs['notes'] }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Riwayat Pemeriksaan --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-history me-1"></i>Riwayat Pemeriksaan</h6>
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
                            <th>Poliklinik</th>
                            <th>Dokter</th>
                            <th>Jenis Kunjungan</th>
                            <th>Diagnosis</th>
                            <th width="80">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previousRecords as $record)
                            <tr>
                                <td>{{ $record->visit_date?->format('d/m/Y') }}</td>
                                <td>{{ $record->polyclinic?->name ?? '-' }}</td>
                                <td>{{ $record->doctor?->name ?? '-' }}</td>
                                <td>{{ $record->visit_type ?? '-' }}</td>
                                <td>
                                    @php
                                        $recDiag = $record->diagnoses()->where('type', 'primary')->with('icd10Diagnosis')->first();
                                    @endphp
                                    @if($recDiag)
                                        <span class="badge bg-info me-1">{{ $recDiag->icd10Diagnosis->code }}</span>
                                    @elseif($record->diagnosis_primary)
                                        <span class="badge bg-info me-1">{{ $record->diagnosis_primary }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-primary">
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
@endsection
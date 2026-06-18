@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Hasil Triage</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('triage.edit', $triage) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Data Pasien</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" style="width:120px">Nama</td><td><strong>{{ $triage->registration->patient->name ?? '-' }}</strong></td></tr>
                        <tr><td class="text-muted">No. RM</td><td>{{ $triage->registration->patient->no_rm ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Poli</td><td>{{ $triage->registration->polyclinic->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dilakukan oleh</td><td>{{ $triage->triageBy->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Waktu</td><td>{{ $triage->triage_at?->format('d/m/Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Tanda-Tanda Vital</h6></div>
                <div class="card-body">
                    @php
                        $vitals = [
                            ['label' => 'TD Sistolik', 'unit' => 'mmHg', 'val' => $triage->systolic],
                            ['label' => 'TD Diastolik', 'unit' => 'mmHg', 'val' => $triage->diastolic],
                            ['label' => 'Nadi', 'unit' => '/menit', 'val' => $triage->heart_rate],
                            ['label' => 'RR', 'unit' => '/menit', 'val' => $triage->respiratory_rate],
                            ['label' => 'Suhu', 'unit' => '°C', 'val' => $triage->temperature],
                            ['label' => 'SpO₂', 'unit' => '%', 'val' => $triage->oxygen_saturation],
                            ['label' => 'BB', 'unit' => 'kg', 'val' => $triage->weight],
                            ['label' => 'TB', 'unit' => 'cm', 'val' => $triage->height],
                            ['label' => 'GCS', 'unit' => '', 'val' => $triage->gcs],
                            ['label' => 'Gula Darah', 'unit' => 'mg/dL', 'val' => $triage->blood_glucose],
                        ];
                    @endphp
                    <div class="row g-2">
                        @foreach($vitals as $v)
                            <div class="col-4 col-md-3 col-lg-2">
                                <div class="border rounded-3 p-2 text-center h-100 {{ $v['val'] ? 'bg-light' : 'bg-white' }}">
                                    <div class="text-muted small">{{ $v['label'] }}</div>
                                    <div class="fw-bold fs-5">{{ $v['val'] ?? '-' }}
                                        @if($v['val'] && $v['unit'])<small class="fw-normal text-muted">{{ $v['unit'] }}</small>@endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Keluhan & Screening</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" style="width:140px">Keluhan Utama</td><td>{{ $triage->chief_complaint ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Skala Nyeri</td><td>{{ $triage->pain_scale ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Alergi</td><td>{{ $triage->allergy_notes ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Risiko Jatuh</td><td>{{ $triage->fall_risk ? 'Ya' : ($triage->fall_risk === 0 ? 'Tidak' : '-') }}</td></tr>
                        <tr><td class="text-muted">Status Nutrisi</td><td>{{ ucfirst($triage->nutrition_status ?? '-') }}</td></tr>
                        <tr><td class="text-muted">Status Merokok</td><td>{{ $triage->smoking_status ? str_replace('_', ' ', ucfirst($triage->smoking_status)) : '-' }}</td></tr>
                        <tr><td class="text-muted">Status Kehamilan</td><td>{{ $triage->pregnancy_status ? str_replace('_', ' ', ucfirst($triage->pregnancy_status)) : '-' }}</td></tr>
                        @if($triage->notes)
                            <tr><td class="text-muted">Catatan</td><td>{{ $triage->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

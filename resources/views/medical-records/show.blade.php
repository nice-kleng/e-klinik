@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Rekam Medis</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Pasien</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Nama</td>
                            <td><strong>{{ $medicalRecord->patient->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. RM</td>
                            <td>{{ $medicalRecord->patient->no_rm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $medicalRecord->patient->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Kelamin</td>
                            <td>{{ $medicalRecord->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Usia</td>
                            <td>{{ $medicalRecord->patient->age }} tahun</td>
                        </tr>
                    </table>
                    <a href="{{ route('patients.show', $medicalRecord->patient) }}" class="btn btn-sm btn-outline-info mt-2">Detail Pasien</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Kunjungan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Tanggal</td>
                            <td>{{ $medicalRecord->visit_date?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dokter</td>
                            <td>{{ $medicalRecord->doctor->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Poliklinik</td>
                            <td>{{ $medicalRecord->polyclinic->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jenis Kunjungan</td>
                            <td>{{ $medicalRecord->visit_type ?? '-' }}</td>
                        </tr>
                        @if($medicalRecord->follow_up_date)
                            <tr>
                                <td class="text-muted">Follow-up</td>
                                <td>{{ $medicalRecord->follow_up_date->format('d/m/Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Tanda-Tanda Vital</h6>
                </div>
                <div class="card-body p-0">
                    @php $vs = $medicalRecord->vital_signs ?? []; @endphp
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>TD (mmHg)</th>
                                <th>Nadi (x/mnt)</th>
                                <th>Suhu (°C)</th>
                                <th>RR (x/mnt)</th>
                                <th>BB (kg)</th>
                                <th>TB (cm)</th>
                                <th>SpO₂ (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $vs['blood_pressure'] ?? '-' }}</td>
                                <td>{{ $vs['heart_rate'] ?? '-' }}</td>
                                <td>{{ $vs['temperature'] ?? '-' }}</td>
                                <td>{{ $vs['respiratory_rate'] ?? '-' }}</td>
                                <td>{{ $vs['weight'] ?? '-' }}</td>
                                <td>{{ $vs['height'] ?? '-' }}</td>
                                <td>{{ $vs['oxygen_saturation'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Anamnesis & Pemeriksaan</h6>
                </div>
                <div class="card-body">
                    @if($medicalRecord->anamnesis)
                        <h6 class="text-muted small">Anamnesis / Subjective</h6>
                        <p>{{ $medicalRecord->anamnesis }}</p>
                    @endif
                    @if($medicalRecord->subjective_complaint)
                        <h6 class="text-muted small">Keluhan Utama</h6>
                        <p>{{ $medicalRecord->subjective_complaint }}</p>
                    @endif
                    @if($medicalRecord->objective_finding)
                        <h6 class="text-muted small">Pemeriksaan Fisik / Objective</h6>
                        <p>{{ $medicalRecord->objective_finding }}</p>
                    @endif
                    @if($medicalRecord->physical_exam)
                        <h6 class="text-muted small">Pemeriksaan Fisik Detail</h6>
                        <p>{{ $medicalRecord->physical_exam }}</p>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Diagnosis</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Diagnosis Utama</td>
                            <td><strong>{{ $medicalRecord->diagnosis_primary ?? '-' }}</strong></td>
                        </tr>
                        @if($medicalRecord->diagnosis_secondary)
                            <tr>
                                <td class="text-muted">Diagnosis Sekunder</td>
                                <td>
                                    @foreach((array)$medicalRecord->diagnosis_secondary as $diag)
                                        <span class="badge bg-secondary me-1">{{ $diag }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            @if($medicalRecord->assessment)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Assessment</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $medicalRecord->assessment }}</p></div>
                </div>
            @endif

            @if($medicalRecord->plan)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Plan / Rencana</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $medicalRecord->plan }}</p></div>
                </div>
            @endif

            @if($medicalRecord->notes)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Catatan</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $medicalRecord->notes }}</p></div>
                </div>
            @endif

            @if($medicalRecord->prescriptions->count())
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Resep</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>No. Resep</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medicalRecord->prescriptions as $prescription)
                                    <tr>
                                        <td>{{ $prescription->prescription_number }}</td>
                                        <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $pBadge = match($prescription->status) {
                                                    'active' => 'bg-success', 'dispensed' => 'bg-info',
                                                    'cancelled' => 'bg-secondary', default => 'bg-warning'
                                                };
                                                $pLabel = match($prescription->status) {
                                                    'active' => 'Aktif', 'dispensed' => 'Diberikan',
                                                    'cancelled' => 'Dibatalkan', default => $prescription->status
                                                };
                                            @endphp
                                            <span class="badge {{ $pBadge }}">{{ $pLabel }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('prescriptions.create', ['medical_record_id' => $medicalRecord->id]) }}" class="btn btn-primary">Buat Resep</a>
            </div>
        </div>
    </div>
</div>
@endsection

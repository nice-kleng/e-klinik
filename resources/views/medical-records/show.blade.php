@extends('layouts.volt')

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
                <div class="card-body">
                    @php $vs = $medicalRecord->vital_signs ?? []; @endphp
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

            @php
                $allDiags = $medicalRecord->diagnoses()->with('icd10Diagnosis')->orderBy('type')->orderBy('order')->get();
                $primaryDiag = $allDiags->where('type', 'primary')->first();
                $secondaryDiags = $allDiags->where('type', 'secondary');
                $procedures = $medicalRecord->procedures()->with('icd9CmDiagnosis')->orderBy('order')->get();
            @endphp

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Diagnosis</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Diagnosis Utama</td>
                            <td>
                                @if($primaryDiag)
                                    <strong>
                                        <span class="badge bg-info me-1">{{ $primaryDiag->icd10Diagnosis->code }}</span>
                                        {{ $primaryDiag->icd10Diagnosis->name }}
                                    </strong>
                                @elseif($medicalRecord->diagnosis_primary)
                                    <strong>{{ $medicalRecord->diagnosis_primary }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @if($secondaryDiags->isNotEmpty())
                            <tr>
                                <td class="text-muted">Diagnosis Sekunder</td>
                                <td>
                                    @foreach($secondaryDiags as $sd)
                                        <span class="badge bg-secondary me-1" title="{{ $sd->icd10Diagnosis->name ?? '' }}">
                                            {{ $sd->icd10Diagnosis->code ?? '#' . $sd->id }}
                                        </span>
                                    @endforeach
                                    @if($medicalRecord->diagnosis_secondary)
                                        @foreach((array)$medicalRecord->diagnosis_secondary as $code)
                                            @if(is_string($code))
                                                <span class="badge bg-light text-dark me-1">{{ $code }}</span>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @elseif($medicalRecord->diagnosis_secondary)
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

            @if($procedures->isNotEmpty())
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Prosedur / Tindakan</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Tindakan</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($procedures as $proc)
                                    <tr>
                                        <td><span class="badge bg-warning text-dark">{{ $proc->icd9CmDiagnosis->code }}</span></td>
                                        <td>{{ $proc->icd9CmDiagnosis->name }}</td>
                                        <td class="text-muted">{{ $proc->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

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

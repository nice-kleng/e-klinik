@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @php
        $reg = $queue->registration;
        $patient = $reg?->patient;
        $mr = $queue->medicalRecord;
        $triage = $reg?->triage;
        $education = $mr?->education;
        $summary = $reg?->summary;
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

            {{-- Service Status Badge --}}
            <div class="d-flex gap-2 mb-3">
                @php
                    $statusBadge = match($reg?->service_status) {
                        'registered' => 'bg-secondary',
                        'triage' => 'bg-info',
                        'in_consultation' => 'bg-primary',
                        'lab' => 'bg-warning',
                        'pharmacy' => 'bg-warning',
                        'education' => 'bg-info',
                        'resume' => 'bg-primary',
                        'completed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                @endphp
                <span class="badge {{ $statusBadge }} fs-6">
                    @switch($reg?->service_status)
                        @case('registered') Terdaftar @break
                        @case('triage') Triage @break
                        @case('in_consultation') Konsultasi @break
                        @case('lab') Laboratorium @break
                        @case('pharmacy') Farmasi @break
                        @case('education') Edukasi @break
                        @case('resume') Resume @break
                        @case('completed') Selesai @break
                        @case('cancelled') Dibatalkan @break
                        @default {{ $reg?->service_status ?? '-' }}
                    @endswitch
                </span>
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex gap-2 justify-content-end border-top pt-3">
                @if($mr)
                    <a href="{{ route('medical-records.show', $mr) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-medical me-1"></i>Lihat RME
                    </a>
                @else
                    <a href="{{ route('medical-records.create', ['queue_id' => $queue->id]) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus-circle me-1"></i>Buat Rekam Medis
                    </a>
                @endif
                @if($mr)
                    <a href="{{ route('prescriptions.create', ['medical_record_id' => $mr->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-prescription me-1"></i>Resep
                    </a>
                @endif
                @if($reg)
                    <div class="dropdown d-inline">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-export me-1"></i>Surat
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('letters.sick-leave', $reg) }}" target="_blank">Surat Sakit</a></li>
                            <li><a class="dropdown-item" href="{{ route('letters.health-certificate', $reg) }}" target="_blank">Surat Sehat</a></li>
                            <li><a class="dropdown-item" href="{{ route('letters.referral', $reg) }}" target="_blank">Surat Rujukan</a></li>
                            <li><a class="dropdown-item" href="{{ route('letters.medical-certificate', $reg) }}" target="_blank">Keterangan Medis</a></li>
                        </ul>
                    </div>
                @endif
                <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-3" id="workspaceTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="soap-tab" data-bs-toggle="tab" data-bs-target="#soap" type="button" role="tab">
                <i class="fas fa-notes-medical me-1"></i>SOAP
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="triage-tab" data-bs-toggle="tab" data-bs-target="#triage" type="button" role="tab">
                <i class="fas fa-stethoscope me-1"></i>Triage
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab" type="button" role="tab">
                <i class="fas fa-flask me-1"></i>Lab
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resep-tab" data-bs-toggle="tab" data-bs-target="#resep" type="button" role="tab">
                <i class="fas fa-prescription me-1"></i>Resep
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tindakan-tab" data-bs-toggle="tab" data-bs-target="#tindakan" type="button" role="tab">
                <i class="fas fa-syringe me-1"></i>Tindakan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="edukasi-tab" data-bs-toggle="tab" data-bs-target="#edukasi" type="button" role="tab">
                <i class="fas fa-book me-1"></i>Edukasi
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="resume-tab" data-bs-toggle="tab" data-bs-target="#resume" type="button" role="tab">
                <i class="fas fa-file-alt me-1"></i>Resume
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="berkas-tab" data-bs-toggle="tab" data-bs-target="#berkas" type="button" role="tab">
                <i class="fas fa-paperclip me-1"></i>Berkas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab">
                <i class="fas fa-history me-1"></i>Riwayat
            </button>
        </li>
    </ul>

    <div class="tab-content" id="workspaceTabContent">

        {{-- === Tab SOAP === --}}
        <div class="tab-pane fade show active" id="soap" role="tabpanel">
            @if($mr)
                @php $vs = $mr->vital_signs ?? []; @endphp
                {{-- S --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">S — Subjective</h6></div>
                    <div class="card-body">
                        @if($mr->subjective_complaint)<h6 class="text-muted small">Keluhan Utama</h6><p>{{ $mr->subjective_complaint }}</p>@endif
                        @if($mr->anamnesis)<h6 class="text-muted small">Anamnesis</h6><p>{{ $mr->anamnesis }}</p>@endif
                        @if($mr->past_history)<h6 class="text-muted small">Riwayat Penyakit Dahulu</h6><p>{{ $mr->past_history }}</p>@endif
                        @if($mr->medication_history)<h6 class="text-muted small">Riwayat Pengobatan</h6><p>{{ $mr->medication_history }}</p>@endif
                        @if(!$mr->subjective_complaint && !$mr->anamnesis && !$mr->past_history && !$mr->medication_history)
                            <p class="text-muted mb-0">Belum ada data SOAP</p>
                        @endif
                    </div>
                </div>
                {{-- O --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">O — Objective</h6></div>
                    <div class="card-body">
                        @if($vs)
                            <h6 class="text-muted small">Tanda Vital</h6>
                            <div class="row g-1 mb-3">
                                @foreach([['label'=>'TD','val'=>$vs['blood_pressure']??null],['label'=>'Nadi','val'=>$vs['heart_rate']??null],['label'=>'Suhu','val'=>$vs['temperature']??null],['label'=>'RR','val'=>$vs['respiratory_rate']??null],['label'=>'SpO₂','val'=>$vs['oxygen_saturation']??null],['label'=>'GDS','val'=>$vs['blood_glucose']??null]] as $v)
                                    <div class="col-4 col-md-2">
                                        <div class="border rounded p-1 text-center small">
                                            <div class="text-muted">{{ $v['label'] }}</div>
                                            <strong>{{ $v['val'] ?? '-' }}</strong>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if($mr->objective_finding)<h6 class="text-muted small">Pemeriksaan Fisik Umum</h6><p>{{ $mr->objective_finding }}</p>@endif
                        @if($mr->physical_exam)<h6 class="text-muted small">Pemeriksaan Fisik Detail</h6><p>{{ $mr->physical_exam }}</p>@endif
                    </div>
                </div>
                {{-- A --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">A — Assessment</h6></div>
                    <div class="card-body">
                        @if($mr->assessment)<h6 class="text-muted small">Assessment</h6><p>{{ $mr->assessment }}</p>@endif
                        @if($mr->differential_diagnosis)<h6 class="text-muted small">Diagnosis Banding</h6><p>{{ $mr->differential_diagnosis }}</p>@endif
                        @php
                            $allDiags = $mr->diagnoses()->with('icd10Diagnosis')->orderBy('type')->orderBy('order')->get();
                            $primaryDiag = $allDiags->where('type', 'primary')->first();
                            $secondaryDiags = $allDiags->where('type', 'secondary');
                        @endphp
                        @if($primaryDiag)
                            <h6 class="text-muted small">Diagnosis Utama</h6>
                            <p><span class="badge bg-info me-1">{{ $primaryDiag->icd10Diagnosis->code }}</span> <strong>{{ $primaryDiag->icd10Diagnosis->name }}</strong></p>
                        @endif
                        @if($secondaryDiags->isNotEmpty())
                            <h6 class="text-muted small">Diagnosis Sekunder</h6>
                            <p>@foreach($secondaryDiags as $sd) <span class="badge bg-secondary me-1">{{ $sd->icd10Diagnosis->code }}</span> @endforeach</p>
                        @endif
                    </div>
                </div>
                {{-- P --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">P — Plan</h6></div>
                    <div class="card-body">
                        @if($mr->plan)<p>{{ $mr->plan }}</p>@else<p class="text-muted mb-0">-</p>@endif
                        @if($mr->follow_up_date)<div class="small text-muted"><strong>Follow-up:</strong> {{ $mr->follow_up_date->format('d/m/Y') }}</div>@endif
                    </div>
                </div>
                @if($mr->notes)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Catatan</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $mr->notes }}</p></div>
                </div>
                @endif
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-medical fa-3x mb-3 d-block"></i>
                    <p>Belum ada Rekam Medis untuk kunjungan ini.</p>
                    <a href="{{ route('medical-records.create', ['queue_id' => $queue->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i>Buat Rekam Medis
                    </a>
                </div>
            @endif
        </div>

        {{-- === Tab Triage === --}}
        <div class="tab-pane fade" id="triage" role="tabpanel">
            @if($triage)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-stethoscope me-1 text-primary"></i>Asesmen Triage</h6>
                        <a href="{{ route('triage.show', $triage) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            @php
                                $tvitals = [
                                    ['label'=>'TD','val'=>$triage->systolic ? "{$triage->systolic}/{$triage->diastolic}" : null,'unit'=>'mmHg'],
                                    ['label'=>'Nadi','val'=>$triage->heart_rate,'unit'=>'/menit'],
                                    ['label'=>'Suhu','val'=>$triage->temperature,'unit'=>'°C'],
                                    ['label'=>'RR','val'=>$triage->respiratory_rate,'unit'=>'/menit'],
                                    ['label'=>'SpO₂','val'=>$triage->oxygen_saturation,'unit'=>'%'],
                                    ['label'=>'BB','val'=>$triage->weight,'unit'=>'kg'],
                                    ['label'=>'TB','val'=>$triage->height,'unit'=>'cm'],
                                    ['label'=>'GCS','val'=>$triage->gcs,'unit'=>''],
                                    ['label'=>'GDS','val'=>$triage->blood_glucose,'unit'=>'mg/dL'],
                                ];
                            @endphp
                            @foreach($tvitals as $tv)
                                <div class="col-4 col-md-3 col-lg-2">
                                    <div class="border rounded p-2 text-center h-100 {{ $tv['val'] ? 'bg-light' : 'bg-white' }}">
                                        <div class="text-muted small">{{ $tv['label'] }}</div>
                                        <div class="fw-bold">{{ $tv['val'] ?? '-' }}
                                            @if($tv['val'] && $tv['unit'])<small class="fw-normal text-muted"> {{ $tv['unit'] }}</small>@endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($triage->chief_complaint)<div class="mb-2"><strong>Keluhan:</strong> {{ $triage->chief_complaint }}</div>@endif
                        @if($triage->pain_scale)<div class="mb-2"><strong>Skala Nyeri:</strong> {{ $triage->pain_scale }}/10</div>@endif
                        @if($triage->allergy_notes)<div class="mb-2"><strong>Alergi:</strong> {{ $triage->allergy_notes }}</div>@endif
                        @if($triage->fall_risk)<div class="mb-2"><span class="badge bg-danger">Risiko Jatuh</span></div>@endif
                        <div class="text-muted small">
                            <i class="fas fa-user-nurse me-1"></i>{{ $triage->triageBy?->name ?? '-' }}
                            &middot; {{ $triage->triage_at?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-stethoscope fa-3x mb-3 d-block"></i>
                    <p>Belum ada asesmen triage untuk pasien ini.</p>
                    @if(auth()->user()->hasAnyRole(['admin', 'receptionist', 'nurse']))
                        <a href="{{ route('triage.create', $reg) }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i>Buat Triage
                        </a>
                    @else
                        <span class="text-muted small">Hanya perawat/resepsionis yang dapat membuat triage</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- === Tab Lab === --}}
        <div class="tab-pane fade" id="lab" role="tabpanel">
            @php $labRequests = $mr?->labRequests ?? collect(); @endphp
            @if($labRequests->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Permintaan Laboratorium</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>No. Permintaan</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($labRequests as $lr)
                                    <tr>
                                        <td>{{ $lr->request_number ?? '#' . $lr->id }}</td>
                                        <td>{{ $lr->created_at?->format('d/m/Y') }}</td>
                                        <td>
                                            @php $lBadge = match($lr->status) { 'requested'=>'bg-warning','in_progress'=>'bg-info','completed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-secondary' }; @endphp
                                            <span class="badge {{ $lBadge }}">{{ $lr->status }}</span>
                                        </td>
                                        <td><a href="{{ route('lab-requests.show', $lr) }}" class="btn btn-sm btn-outline-info">Detail</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-flask fa-3x mb-3 d-block"></i>
                    <p>Belum ada permintaan laboratorium.</p>
                    <a href="{{ route('lab-requests.create', ['registration_id' => $reg?->id, 'medical_record_id' => $mr?->id]) }}" class="btn btn-primary">Request Lab</a>
                </div>
            @endif
        </div>

        {{-- === Tab Resep === --}}
        <div class="tab-pane fade" id="resep" role="tabpanel">
            @if($prescriptions->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Resep</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>No. Resep</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($prescriptions as $prescription)
                                    <tr>
                                        <td>{{ $prescription->prescription_number }}</td>
                                        <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $pBadge2 = match($prescription->status) { 'active'=>'bg-success','dispensed'=>'bg-info','cancelled'=>'bg-secondary', default=>'bg-warning' };
                                                $pLabel2 = match($prescription->status) { 'active'=>'Aktif','dispensed'=>'Diberikan','cancelled'=>'Dibatalkan', default=>$prescription->status };
                                            @endphp
                                            <span class="badge {{ $pBadge2 }}">{{ $pLabel2 }}</span>
                                        </td>
                                        <td><a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-prescription fa-3x mb-3 d-block"></i>
                    <p>Belum ada resep untuk kunjungan ini.</p>
                    @if($mr)
                    <a href="{{ route('prescriptions.create', ['medical_record_id' => $mr->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-1"></i>Buat Resep
                    </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- === Tab Tindakan === --}}
        <div class="tab-pane fade" id="tindakan" role="tabpanel">
            @php $procedures = $mr?->procedures()->with('icd9CmDiagnosis')->orderBy('order')->get() ?? collect(); @endphp
            @if($procedures->isNotEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Prosedur / Tindakan</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Kode</th><th>Nama Tindakan</th><th>Operator</th><th>Catatan</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                @foreach($procedures as $proc)
                                    <tr>
                                        <td><span class="badge bg-warning text-dark">{{ $proc->icd9CmDiagnosis->code }}</span></td>
                                        <td>{{ $proc->icd9CmDiagnosis->name }}</td>
                                        <td>{{ $proc->operator?->name ?? '-' }}</td>
                                        <td class="text-muted">{{ $proc->notes ?? '-' }}</td>
                                        <td>
                                            @php $ps = match($proc->status) { 'ordered'=>'bg-info','in_progress'=>'bg-warning','completed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-secondary' }; @endphp
                                            <span class="badge {{ $ps }}">{{ $proc->status ?? 'ordered' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-syringe fa-3x mb-3 d-block"></i>
                    <p>Belum ada tindakan yang dicatat.</p>
                </div>
            @endif
        </div>

        {{-- === Tab Edukasi === --}}
        <div class="tab-pane fade" id="edukasi" role="tabpanel">
            @if($education)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0">Edukasi Pasien</h6></div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            @if($education->diagnosis_explained)<tr><td class="text-muted" style="width:140px">Diagnosis</td><td>{{ $education->diagnosis_explained }}</td></tr>@endif
                            @if($education->medication_instructions)<tr><td class="text-muted">Obat</td><td>{{ $education->medication_instructions }}</td></tr>@endif
                            @if($education->diet_instructions)<tr><td class="text-muted">Diet</td><td>{{ $education->diet_instructions }}</td></tr>@endif
                            @if($education->activity_instructions)<tr><td class="text-muted">Aktivitas</td><td>{{ $education->activity_instructions }}</td></tr>@endif
                            @if($education->follow_up_plan)<tr><td class="text-muted">Kontrol</td><td>{{ $education->follow_up_plan }}</td></tr>@endif
                        </table>
                        <div class="text-muted small mt-2">{{ $education->educator?->name ?? '-' }} &middot; {{ $education->education_date?->format('d/m/Y') }}</div>
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-book fa-3x mb-3 d-block"></i>
                    <p>Belum ada edukasi pasien.</p>
                    @if($mr)
                        <a href="{{ route('education.create', $mr) }}" class="btn btn-warning">
                            <i class="fas fa-plus-circle me-1"></i>Buat Edukasi
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- === Tab Resume === --}}
        <div class="tab-pane fade" id="resume" role="tabpanel">
            @if($summary)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Resume Kunjungan</h6>
                        <a href="{{ route('visit-summary.show', $summary) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            @if($summary->final_diagnosis)<tr><td class="text-muted" style="width:140px">Diagnosis Akhir</td><td><strong>{{ $summary->final_diagnosis }}</strong></td></tr>@endif
                            @if($summary->discharge_status)<tr><td class="text-muted">Status Pulang</td><td>{{ $summary->discharge_status }}</td></tr>@endif
                            @if($summary->follow_up_plan)<tr><td class="text-muted">Kontrol</td><td>{{ $summary->follow_up_plan }}</td></tr>@endif
                            @if($summary->sick_leave_days)<tr><td class="text-muted">Cuti Sakit</td><td>{{ $summary->sick_leave_days }} hari ({{ $summary->sick_leave_from?->format('d/m/Y') }} — {{ $summary->sick_leave_to?->format('d/m/Y') }})</td></tr>@endif
                            @if($summary->referral_to)<tr><td class="text-muted">Rujukan</td><td>{{ $summary->referral_to }}</td></tr>@endif
                        </table>
                        @if($reg)
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('letters.sick-leave', $reg) }}" target="_blank" class="btn btn-sm btn-outline-danger">Surat Sakit</a>
                            <a href="{{ route('letters.health-certificate', $reg) }}" target="_blank" class="btn btn-sm btn-outline-success">Surat Sehat</a>
                            <a href="{{ route('letters.referral', $reg) }}" target="_blank" class="btn btn-sm btn-outline-primary">Surat Rujukan</a>
                            <a href="{{ route('letters.medical-certificate', $reg) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Ket. Medis</a>
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-file-alt fa-3x mb-3 d-block"></i>
                    <p>Belum ada resume kunjungan.</p>
                    @if($reg)
                        <a href="{{ route('visit-summary.create', $reg) }}" class="btn btn-success">
                            <i class="fas fa-plus-circle me-1"></i>Buat Resume
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- === Tab Riwayat === --}}
        <div class="tab-pane fade" id="riwayat" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Riwayat Pemeriksaan</h6></div>
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
                                    <th>Jenis</th>
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

        {{-- === Tab Berkas === --}}
        <div class="tab-pane fade" id="berkas" role="tabpanel">
            @if($mr)
                @include('attachments.upload-modal', ['medicalRecord' => $mr, 'hideUpload' => false])
            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-paperclip fa-3x mb-3 d-block"></i>
                    <p>Buat rekam medis terlebih dahulu sebelum mengunggah berkas</p>
                    <a href="{{ route('medical-records.create', ['queue_id' => $queue->id]) }}" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i>Buat Rekam Medis
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
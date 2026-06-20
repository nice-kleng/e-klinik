@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @php
        $reg = $medicalRecord->registration;
        $triage = $reg?->triage;
        $mr = $medicalRecord;
        $education = $mr->education;
        $summary = $reg?->summary;
        $consentTypes = ['general' => 'Persetujuan Umum', 'procedure' => 'Tindakan Medis', 'surgery' => 'Operasi', 'anesthesia' => 'Anestesi', 'transfusion' => 'Transfusi Darah', 'other' => 'Lainnya'];
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Rekam Medis</h4>
        <div class="d-flex gap-2">
            @if($reg)
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
            <a href="{{ route('medical-records.edit', $mr) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        {{-- Left Column: Patient Info & Triage TTV --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Pasien</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" style="width:120px">Nama</td><td><strong>{{ $mr->patient->name ?? '-' }}</strong></td></tr>
                        <tr><td class="text-muted">No. RM</td><td>{{ $mr->patient->no_rm ?? '-' }}</td></tr>
                        <tr><td class="text-muted">NIK</td><td>{{ $mr->patient->nik ?? '-' }}</td></tr>
                        <tr><td class="text-muted">JK</td><td>{{ $mr->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                        <tr><td class="text-muted">Usia</td><td>{{ $mr->patient->age }} tahun</td></tr>
                    </table>
                    <a href="{{ route('patients.show', $mr->patient) }}" class="btn btn-sm btn-outline-info mt-2">Detail Pasien</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Kunjungan</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" style="width:120px">Tanggal</td><td>{{ $mr->visit_date?->format('d/m/Y') }}</td></tr>
                        <tr><td class="text-muted">Dokter</td><td>{{ $mr->doctor->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Poliklinik</td><td>{{ $mr->polyclinic->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Jenis</td><td>{{ $mr->visit_type ?? '-' }}</td></tr>
                        <tr><td class="text-muted">No. Registrasi</td><td>{{ $reg?->registration_number ?? '-' }}</td></tr>
                        @if($mr->follow_up_date)
                            <tr><td class="text-muted">Follow-up</td><td>{{ $mr->follow_up_date->format('d/m/Y') }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Triage TTV Card --}}
            @if($triage)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0"><i class="fas fa-stethoscope me-1 text-primary"></i>Triage (Asesmen Perawat)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @php
                            $triageVitals = [
                                ['label' => 'TD', 'unit' => 'mmHg', 'val' => $triage->systolic ? "{$triage->systolic}/{$triage->diastolic}" : null, 'icon' => 'fa-heart-pulse'],
                                ['label' => 'Nadi', 'unit' => '/menit', 'val' => $triage->heart_rate, 'icon' => 'fa-heart'],
                                ['label' => 'Suhu', 'unit' => '°C', 'val' => $triage->temperature, 'icon' => 'fa-temperature-high'],
                                ['label' => 'RR', 'unit' => '/menit', 'val' => $triage->respiratory_rate, 'icon' => 'fa-lungs'],
                                ['label' => 'SpO₂', 'unit' => '%', 'val' => $triage->oxygen_saturation, 'icon' => 'fa-droplet'],
                                ['label' => 'BB', 'unit' => 'kg', 'val' => $triage->weight, 'icon' => 'fa-weight-scale'],
                                ['label' => 'TB', 'unit' => 'cm', 'val' => $triage->height, 'icon' => 'fa-ruler-vertical'],
                                ['label' => 'GCS', 'unit' => '', 'val' => $triage->gcs, 'icon' => 'fa-brain'],
                                ['label' => 'Gula Darah', 'unit' => 'mg/dL', 'val' => $triage->blood_glucose, 'icon' => 'fa-droplet'],
                            ];
                        @endphp
                        @foreach($triageVitals as $tv)
                            <div class="col-4">
                                <div class="border rounded p-2 text-center h-100 {{ $tv['val'] ? 'bg-light' : 'bg-white' }}">
                                    <div class="text-muted small"><i class="fas {{ $tv['icon'] }} me-1"></i>{{ $tv['label'] }}</div>
                                    <div class="fw-bold fs-6">{{ $tv['val'] ?? '-' }}
                                        @if($tv['val'] && $tv['unit'])<small class="fw-normal text-muted">{{ $tv['unit'] }}</small>@endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($triage->chief_complaint)
                        <div class="mt-2 p-2 bg-light rounded small">
                            <strong>Keluhan:</strong> {{ $triage->chief_complaint }}
                        </div>
                    @endif
                    @if($triage->pain_scale)
                        <div class="mt-1 small"><strong>Skala Nyeri:</strong> {{ $triage->pain_scale }}/10</div>
                    @endif
                    @if($triage->allergy_notes)
                        <div class="mt-1 small"><strong>Alergi:</strong> {{ $triage->allergy_notes }}</div>
                    @endif
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-user-nurse me-1"></i>{{ $triage->triageBy?->name ?? '-' }}
                        &middot; {{ $triage->triage_at?->format('d/m/Y H:i') }}
                        <a href="{{ route('triage.show', $triage) }}" class="ms-2">Detail</a>
                    </div>
                </div>
            </div>
            @endif

            {{-- TTE Card --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body text-center">
                    @if($mr->signed_by)
                        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                        <h6 class="text-success mb-1">✓ Ditandatangani</h6>
                        <small class="text-muted">
                            {{ $mr->signer?->name }}<br>
                            {{ $mr->signed_at?->format('d/m/Y H:i') }}
                        </small>
                        <div class="mt-2">
                            <a href="{{ route('medical-records.pdf', $mr) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-file-pdf me-1"></i>Download PDF TTE
                            </a>
                        </div>
                    @else
                        <i class="fas fa-file-signature fa-2x text-muted mb-2"></i>
                        <h6 class="mb-1">Belum Ditandatangani</h6>
                        <button class="btn btn-primary btn-sm" onclick="signRme({{ $mr->id }})">
                            <i class="fas fa-pen me-1"></i>Tanda Tangani Sekarang
                        </button>
                    @endif
                </div>
            </div>

            {{-- Tombol Aksi Cepat --}}
            <div class="d-flex flex-column gap-2 mt-3">
                @if($reg)
                    @if(!$summary)
                        <a href="{{ route('visit-summary.create', $reg) }}" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-check-circle me-1"></i>Buat Resume Kunjungan
                        </a>
                    @else
                        <a href="{{ route('visit-summary.show', $summary) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-file-alt me-1"></i>Lihat Resume Kunjungan
                        </a>
                    @endif
                @endif
                @if(!$education)
                    <a href="{{ route('education.create', $mr) }}" class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-book me-1"></i>Edukasi Pasien
                    </a>
                @endif
                <a href="{{ route('prescriptions.create', ['medical_record_id' => $mr->id]) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-prescription me-1"></i>Buat Resep
                </a>
                @if($reg)
                    <a href="{{ route('queues.history', $reg) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-history me-1"></i>Riwayat Kunjungan
                    </a>
                @endif
            </div>
        </div>

        {{-- Right Column: SOAP --}}
        <div class="col-md-8">
            {{-- S: Subjective --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">S — Subjective</h6></div>
                <div class="card-body">
                    @if($mr->subjective_complaint)
                        <h6 class="text-muted small">Keluhan Utama / RPS</h6>
                        <p>{{ $mr->subjective_complaint }}</p>
                    @endif
                    @if($mr->anamnesis)
                        <h6 class="text-muted small">Anamnesis</h6>
                        <p>{{ $mr->anamnesis }}</p>
                    @endif
                    @if($mr->past_history)
                        <h6 class="text-muted small">Riwayat Penyakit Dahulu</h6>
                        <p>{{ $mr->past_history }}</p>
                    @endif
                    @if($mr->medication_history)
                        <h6 class="text-muted small">Riwayat Pengobatan</h6>
                        <p>{{ $mr->medication_history }}</p>
                    @endif
                    @if(!$mr->subjective_complaint && !$mr->anamnesis && !$mr->past_history && !$mr->medication_history)
                        <p class="text-muted mb-0">-</p>
                    @endif
                </div>
            </div>

            {{-- O: Objective --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">O — Objective</h6></div>
                <div class="card-body">
                    @php $vs = $mr->vital_signs ?? []; @endphp
                    @if($vs)
                        <h6 class="text-muted small">Tanda Vital (Dokter)</h6>
                        <div class="row g-1 mb-3">
                            @foreach([['label'=>'TD','val'=>$vs['blood_pressure']??null,'unit'=>'mmHg'],['label'=>'Nadi','val'=>$vs['heart_rate']??null,'unit'=>'/menit'],['label'=>'Suhu','val'=>$vs['temperature']??null,'unit'=>'°C'],['label'=>'RR','val'=>$vs['respiratory_rate']??null,'unit'=>'/menit'],['label'=>'SpO₂','val'=>$vs['oxygen_saturation']??null,'unit'=>'%'],['label'=>'GDS','val'=>$vs['blood_glucose']??null,'unit'=>'mg/dL']] as $v)
                                <div class="col-4 col-md-2">
                                    <div class="border rounded p-1 text-center small">
                                        <div class="text-muted">{{ $v['label'] }}</div>
                                        <strong>{{ $v['val'] ?? '-' }}</strong>
                                        @if($v['val'])<small class="text-muted">{{ $v['unit'] }}</small>@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if(!empty($vs['notes']))
                            <div class="small text-muted mb-2"><strong>Catatan TTV:</strong> {{ $vs['notes'] }}</div>
                        @endif
                    @endif
                    @if($mr->objective_finding)
                        <h6 class="text-muted small">Pemeriksaan Fisik Umum</h6>
                        <p>{{ $mr->objective_finding }}</p>
                    @endif
                    @if($mr->physical_exam)
                        <h6 class="text-muted small">Pemeriksaan Fisik Detail</h6>
                        <p>{{ $mr->physical_exam }}</p>
                    @endif
                    @if(!$mr->objective_finding && !$mr->physical_exam)
                        <p class="text-muted mb-0">-</p>
                    @endif
                </div>
            </div>

            {{-- A: Assessment --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">A — Assessment</h6></div>
                <div class="card-body">
                    @if($mr->assessment)
                        <h6 class="text-muted small">Assessment</h6>
                        <p>{{ $mr->assessment }}</p>
                    @endif
                    @php
                        $allDiags = $mr->diagnoses()->with('icd10Diagnosis')->orderBy('type')->orderBy('order')->get();
                        $primaryDiag = $allDiags->where('type', 'primary')->first();
                        $secondaryDiags = $allDiags->where('type', 'secondary');
                        $differentialDiags = $allDiags->where('type', 'differential');
                        $procedures = $mr->procedures()->with('icd9CmDiagnosis')->orderBy('order')->get();
                    @endphp

                    @if($primaryDiag || $mr->diagnosis_primary)
                        <h6 class="text-muted small">Diagnosis Utama</h6>
                        <p>
                            @if($primaryDiag)
                                <span class="badge bg-info me-1">{{ $primaryDiag->icd10Diagnosis->code }}</span>
                                <strong>{{ $primaryDiag->icd10Diagnosis->name }}</strong>
                            @else
                                <strong>{{ $mr->diagnosis_primary }}</strong>
                            @endif
                        </p>
                    @endif

                    @if($secondaryDiags->isNotEmpty())
                        <h6 class="text-muted small">Diagnosis Sekunder</h6>
                        <p>
                            @foreach($secondaryDiags as $sd)
                                <span class="badge bg-secondary me-1">{{ $sd->icd10Diagnosis->code ?? '#' . $sd->id }}</span>
                            @endforeach
                        </p>
                    @endif

                    @if($differentialDiags->isNotEmpty())
                        <h6 class="text-muted small">Diagnosis Banding</h6>
                        <p>
                            @foreach($differentialDiags as $dd)
                                <span class="badge bg-warning text-dark me-1">{{ $dd->icd10Diagnosis->code }}</span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>

            {{-- Specialist Data --}}
            @if(isset($specialistPartial) && $specialistPartial && $mr->specialist_data)
                @php
                    $sd = $mr->specialist_data;
                    $code = $mr->polyclinic?->code;
                @endphp
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-microscope me-1 text-primary"></i>Pemeriksaan Spesialis ({{ $mr->polyclinic?->name }})</h6></div>
                    <div class="card-body">
                        @if($code === 'PDL')
                            @php
                                $organLabels = [
                                    'kardiovaskular'=>'Kardiovaskular','respirasi'=>'Respirasi','gastrointestinal'=>'Gastrointestinal',
                                    'hepatobilier'=>'Hepatobilier','urogenital'=>'Urogenital','muskuloskeletal'=>'Muskuloskeletal',
                                    'neurologi'=>'Neurologi','endokrin'=>'Endokrin & Metabolik','hematologi'=>'Hematologi',
                                    'integumen'=>'Integumen','psikiatri'=>'Psikiatri',
                                ];
                            @endphp
                            @if(!empty($sd['sistem_organ']))
                                <h6 class="text-muted small">Anamnesis Sistem Organ</h6>
                                @foreach($organLabels as $key => $label)
                                    @if(!empty($sd['sistem_organ'][$key]['checked']) && !empty($sd['sistem_organ'][$key]['notes']))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['sistem_organ'][$key]['notes'] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['fisik']))
                                <h6 class="text-muted small">Pemeriksaan Fisik</h6>
                                @php $fisikLabels = ['kesadaran'=>'Kesadaran','td'=>'TD','nadi'=>'Nadi','suhu'=>'Suhu','spo2'=>'SpO₂','status_gizi'=>'Status Gizi']; @endphp
                                @foreach($fisikLabels as $key => $label)
                                    @if(!empty($sd['fisik'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['fisik'][$key] }}</p>
                                    @endif
                                @endforeach
                                @php
                                    $thoraxParts = ['thorax_inspeksi'=>'Inspeksi','thorax_palpasi'=>'Palpasi','thorax_perkusi'=>'Perkusi','thorax_auskultasi'=>'Auskultasi'];
                                    $abdomenParts = ['abdomen_inspeksi'=>'Inspeksi','abdomen_palpasi'=>'Palpasi','abdomen_perkusi'=>'Perkusi','abdomen_auskultasi'=>'Auskultasi'];
                                @endphp
                                @if(array_intersect_key(array_flip(array_keys($thoraxParts)), $sd['fisik']))
                                    <p class="mb-1"><strong>Thorax:</strong></p>
                                    @foreach($thoraxParts as $key => $label)
                                        @if(!empty($sd['fisik'][$key]))
                                            <p class="ms-3 mb-1"><em>{{ $label }}:</em> {{ $sd['fisik'][$key] }}</p>
                                        @endif
                                    @endforeach
                                @endif
                                @if(array_intersect_key(array_flip(array_keys($abdomenParts)), $sd['fisik']))
                                    <p class="mb-1"><strong>Abdomen:</strong></p>
                                    @foreach($abdomenParts as $key => $label)
                                        @if(!empty($sd['fisik'][$key]))
                                            <p class="ms-3 mb-1"><em>{{ $label }}:</em> {{ $sd['fisik'][$key] }}</p>
                                        @endif
                                    @endforeach
                                @endif
                                @if(!empty($sd['fisik']['ekstremitas']))
                                    <p><strong>Ekstremitas:</strong> {{ $sd['fisik']['ekstremitas'] }}</p>
                                @endif
                            @endif

                        @elseif($code === 'ANAK')
                            @if(!empty($sd['perinatal']))
                                <h6 class="text-muted small">Riwayat Perinatal</h6>
                                @foreach(['usia_kehamilan'=>'Usia Kehamilan','jenis_persalinan'=>'Cara Persalinan','bb_lahir'=>'BB Lahir','pb_lahir'=>'PB Lahir','asi_eksklusif'=>'ASI Eksklusif'] as $key => $label)
                                    @if(!empty($sd['perinatal'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['perinatal'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['imunisasi']))
                                <h6 class="text-muted small">Imunisasi</h6>
                                @php $vaksin = []; @endphp
                                @foreach($sd['imunisasi'] as $key => $val)
                                    @if(is_array($val) && !empty($val['checked']))
                                        @php $vaksin[] = ucfirst(str_replace('-', ' ', $key)); @endphp
                                    @elseif(is_string($val) || is_numeric($val))
                                        @php $vaksin[] = ucfirst(str_replace('-', ' ', $key)); @endphp
                                    @endif
                                @endforeach
                                @if($vaksin)
                                    <p>{{ implode(', ', $vaksin) }}</p>
                                @else
                                    <p class="text-muted">-</p>
                                @endif
                            @endif
                            @if(!empty($sd['tumbuh_kembang']))
                                <h6 class="text-muted small">Tumbuh Kembang</h6>
                                @foreach(['motorik_kasar'=>'Motorik Kasar','motorik_halus'=>'Motorik Halus','bicara'=>'Bicara / Bahasa','sosial'=>'Sosial & Kemandirian'] as $key => $label)
                                    @if(!empty($sd['tumbuh_kembang'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['tumbuh_kembang'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['antropometri']))
                                <h6 class="text-muted small">Antropometri</h6>
                                @foreach(['bb'=>'BB','tb'=>'TB','lk'=>'LK','lila'=>'LILA','status_gizi'=>'Status Gizi'] as $key => $label)
                                    @if(!empty($sd['antropometri'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['antropometri'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif

                        @elseif($code === 'SARAF')
                            @php
                                $ncMap = ['nc1'=>'N.I — Olfactorius','nc2'=>'N.II — Opticus','nc3'=>'N.III — Oculomotorius','nc4'=>'N.IV — Trochlearis','nc5'=>'N.V — Trigeminus','nc6'=>'N.VI — Abducens','nc7'=>'N.VII — Facialis','nc8'=>'N.VIII — Vestibulocochlearis','nc9'=>'N.IX — Glossopharyngeus','nc10'=>'N.X — Vagus','nc11'=>'N.XI — Accessorius','nc12'=>'N.XII — Hypoglossus'];
                                $motorikLabels = ['atas_kanan'=>'Ekstremitas Atas Kanan','atas_kiri'=>'Ekstremitas Atas Kiri','bawah_kanan'=>'Ekstremitas Bawah Kanan','bawah_kiri'=>'Ekstremitas Bawah Kiri'];
                            @endphp
                            @if(!empty($sd['nc']))
                                <h6 class="text-muted small">Nervus Cranialis</h6>
                                @foreach($ncMap as $key => $label)
                                    @if(!empty($sd['nc'][$key]['status']))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['nc'][$key]['status'] }}
                                        @if(!empty($sd['nc'][$key]['notes']))
                                            <span class="text-muted">— {{ $sd['nc'][$key]['notes'] }}</span>
                                        @endif
                                        </p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['motorik']))
                                <h6 class="text-muted small">MRC Scale (Motorik)</h6>
                                @foreach($motorikLabels as $key => $label)
                                    @if(!empty($sd['motorik'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['motorik'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['sensorik']))
                                <h6 class="text-muted small">Pemeriksaan Sensorik</h6>
                                @foreach(['raba'=>'Raba (taktil)','nyeri'=>'Nyeri (tusuk)','suhu'=>'Suhu (panas/dingin)','vibrasi'=>'Vibrasi (garpu tala)','propriosepsi'=>'Propriosepsi (posisi sendi)'] as $key => $label)
                                    @if(!empty($sd['sensorik'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['sensorik'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['refleks']))
                                <h6 class="text-muted small">Refleks</h6>
                                @foreach(['fisiologis'=>'Fisiologis','patologis'=>'Patologis','kloni'=>'Kloni'] as $key => $label)
                                    @if(!empty($sd['refleks'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['refleks'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['koordinasi']))
                                <h6 class="text-muted small">Koordinasi & Keseimbangan</h6>
                                @foreach(['finger_nose'=>'Finger-to-Nose','heel_shin'=>'Heel-to-Shin','romberg'=>'Romberg','gait'=>'Cara Jalan'] as $key => $label)
                                    @if(!empty($sd['koordinasi'][$key]))
                                        <p><strong>{{ $label }}:</strong> {{ $sd['koordinasi'][$key] }}</p>
                                    @endif
                                @endforeach
                            @endif

                        @elseif($code === 'RAD')
                            @if(!empty($sd['indikasi']))
                                <h6 class="text-muted small">Indikasi</h6>
                                <p>{{ $sd['indikasi'] }}</p>
                            @endif
                            @if(!empty($sd['riwayat']))
                                <h6 class="text-muted small">Riwayat</h6>
                                <p>{{ $sd['riwayat'] }}</p>
                            @endif
                            @if(!empty($sd['jenis']))
                                <h6 class="text-muted small">Jenis Pemeriksaan</h6>
                                <p>
                                    @if(!empty($sd['jenis']['modalitas']))<strong>Modalitas:</strong> {{ $sd['jenis']['modalitas'] }}<br>@endif
                                    @if(!empty($sd['jenis']['region']))<strong>Region:</strong> {{ $sd['jenis']['region'] }}<br>@endif
                                    @if(!empty($sd['jenis']['proyeksi']))<strong>Proyeksi:</strong> {{ $sd['jenis']['proyeksi'] }}<br>@endif
                                    @if(!empty($sd['jenis']['kontras']))<strong>Kontras:</strong> {{ $sd['jenis']['kontras'] }}<br>@endif
                                </p>
                            @endif
                            @if(!empty($sd['temuan']))
                                <h6 class="text-muted small">Temuan</h6>
                                @if(!empty($sd['temuan']['deskripsi']))<p>{{ $sd['temuan']['deskripsi'] }}</p>@endif
                                @if(!empty($sd['temuan']['kesan']))<p><strong>Kesan:</strong> {{ $sd['temuan']['kesan'] }}</p>@endif
                                @if(!empty($sd['temuan']['kategori']))<p><strong>Kategori:</strong> {{ $sd['temuan']['kategori'] }}</p>@endif
                                @if(!empty($sd['temuan']['kesimpulan']))<p><strong>Kesimpulan:</strong> {{ $sd['temuan']['kesimpulan'] }}</p>@endif
                                @if(!empty($sd['temuan']['rekomendasi']))<p><strong>Rekomendasi:</strong> {{ $sd['temuan']['rekomendasi'] }}</p>@endif
                            @endif

                        @elseif($code === 'GIG')
                            @if(!empty($sd['odontogram']))
                                <h6 class="text-muted small">Odontogram</h6>
                                @php
                                    $odo = is_string($sd['odontogram']) ? json_decode($sd['odontogram'], true) : $sd['odontogram'];
                                    $statusLabels = ['utuh'=>'Utuh','karies'=>'Karies','tambalan'=>'Tambalan','ekstraksi'=>'Ekstraksi','mahkota'=>'Mahkota'];
                                    $grouped = [];
                                    if(is_array($odo)) {
                                        foreach($odo as $num => $st) {
                                            $grouped[$st][] = $num;
                                        }
                                    }
                                @endphp
                                @foreach($statusLabels as $st => $label)
                                    @if(!empty($grouped[$st]))
                                        <p><strong>{{ $label }}:</strong> {{ implode(', ', $grouped[$st]) }}</p>
                                    @endif
                                @endforeach
                            @endif
                            @if(!empty($sd['periodontal']))
                                <h6 class="text-muted small">Periodontal</h6>
                                @if(!empty($sd['periodontal']['gingiva']))<p><strong>Gingiva:</strong> {{ $sd['periodontal']['gingiva'] }}</p>@endif
                                @if(!empty($sd['periodontal']['poket']))<p><strong>Kedalaman Poket:</strong> {{ $sd['periodontal']['poket'] }}</p>@endif
                                @if(!empty($sd['periodontal']['kalkulus']))<p><strong>Kalkulus:</strong> {{ $sd['periodontal']['kalkulus'] }}</p>@endif
                                @if(!empty($sd['periodontal']['mobilitas']))<p><strong>Mobilitas:</strong> {{ $sd['periodontal']['mobilitas'] }}</p>@endif
                            @endif
                            @if(!empty($sd['mukosa']))
                                <h6 class="text-muted small">Mukosa & Jaringan Lunak</h6>
                                @if(!empty($sd['mukosa']['oral']))<p><strong>Mukosa Oral:</strong> {{ $sd['mukosa']['oral'] }}</p>@endif
                                @if(!empty($sd['mukosa']['lidah']))<p><strong>Lidah:</strong> {{ $sd['mukosa']['lidah'] }}</p>@endif
                                @if(!empty($sd['mukosa']['tonsil']))<p><strong>Tonsil & Faring:</strong> {{ $sd['mukosa']['tonsil'] }}</p>@endif
                            @endif
                            @if(!empty($sd['kebersihan']))
                                <h6 class="text-muted small">Kebersihan Mulut</h6>
                                @if(!empty($sd['kebersihan']['ohis']))<p><strong>OHI-S:</strong> {{ $sd['kebersihan']['ohis'] }}</p>@endif
                                @if(!empty($sd['kebersihan']['dmft']))<p><strong>DMFT:</strong> {{ $sd['kebersihan']['dmft'] }}</p>@endif
                            @endif
                            @if(!empty($sd['tindakan']))
                                <h6 class="text-muted small">Tindakan</h6>
                                @if(!empty($sd['tindakan']['rencana']))<p><strong>Rencana:</strong> {{ $sd['tindakan']['rencana'] }}</p>@endif
                                @if(!empty($sd['tindakan']['gigi']))<p><strong>Gigi:</strong> {{ $sd['tindakan']['gigi'] }}</p>@endif
                                @if(!empty($sd['tindakan']['anestesi']))<p><strong>Anestesi:</strong> {{ $sd['tindakan']['anestesi'] }}</p>@endif
                            @endif
                        @endif
                    </div>
                </div>
            @endif

            {{-- Tindakan --}}
            @if($procedures->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Prosedur / Tindakan</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Kode</th><th>Nama Tindakan</th><th>Catatan</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($procedures as $proc)
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ $proc->icd9CmDiagnosis->code }}</span></td>
                                    <td>{{ $proc->icd9CmDiagnosis->name }}</td>
                                    <td class="text-muted">{{ $proc->notes ?? '-' }}</td>
                                    <td>
                                        @php
                                            $pStatus = match($proc->status) { 'ordered'=>'bg-info','in_progress'=>'bg-warning','completed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-secondary' };
                                        @endphp
                                        <span class="badge {{ $pStatus }}">{{ $proc->status ?? 'planned' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- P: Plan --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">P — Plan</h6></div>
                <div class="card-body">
                    @if($mr->plan)
                        <p>{{ $mr->plan }}</p>
                    @else
                        <p class="text-muted mb-0">-</p>
                    @endif
                    @if($mr->follow_up_date)
                        <div class="small text-muted"><strong>Follow-up:</strong> {{ $mr->follow_up_date->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>

            @if($mr->notes)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Catatan</h6></div>
                <div class="card-body"><p class="mb-0">{{ $mr->notes }}</p></div>
            </div>
            @endif

            {{-- Resep --}}
            @if($mr->prescriptions->count())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Resep</h6></div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead><tr><th>No. Resep</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach($mr->prescriptions as $prescription)
                                <tr>
                                    <td>{{ $prescription->prescription_number }}</td>
                                    <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                                    <td>
                                        @php
                                            $pBadge = match($prescription->status) { 'active'=>'bg-success', 'dispensed'=>'bg-info', 'cancelled'=>'bg-secondary', default=>'bg-warning' };
                                            $pLabel = match($prescription->status) { 'active'=>'Aktif', 'dispensed'=>'Diberikan', 'cancelled'=>'Dibatalkan', default=>$prescription->status };
                                        @endphp
                                        <span class="badge {{ $pBadge }}">{{ $pLabel }}</span>
                                    </td>
                                    <td><a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Edukasi --}}
            @if($education)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Edukasi Pasien</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        @if($education->diagnosis_explained)<tr><td class="text-muted">Diagnosis</td><td>{{ $education->diagnosis_explained }}</td></tr>@endif
                        @if($education->medication_instructions)<tr><td class="text-muted">Obat</td><td>{{ $education->medication_instructions }}</td></tr>@endif
                        @if($education->diet_instructions)<tr><td class="text-muted">Diet</td><td>{{ $education->diet_instructions }}</td></tr>@endif
                        @if($education->activity_instructions)<tr><td class="text-muted">Aktivitas</td><td>{{ $education->activity_instructions }}</td></tr>@endif
                        @if($education->follow_up_plan)<tr><td class="text-muted">Kontrol</td><td>{{ $education->follow_up_plan }}</td></tr>@endif
                    </table>
                    <div class="text-muted small mt-2">
                        <i class="fas fa-user-md me-1"></i>{{ $education->educator?->name ?? '-' }}
                        &middot; {{ $education->education_date?->format('d/m/Y') }}
                    </div>
                </div>
            </div>
            @endif

            {{-- Resume --}}
            @if($summary)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Resume Kunjungan</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        @if($summary->final_diagnosis)<tr><td class="text-muted" style="width:140px">Diagnosis Akhir</td><td><strong>{{ $summary->final_diagnosis }}</strong></td></tr>@endif
                        @if($summary->discharge_status)<tr><td class="text-muted">Status Pulang</td><td>{{ $summary->discharge_status }}</td></tr>@endif
                        @if($summary->follow_up_plan)<tr><td class="text-muted">Rencana Kontrol</td><td>{{ $summary->follow_up_plan }}</td></tr>@endif
                        @if($summary->sick_leave_days)<tr><td class="text-muted">Cuti Sakit</td><td>{{ $summary->sick_leave_days }} hari ({{ $summary->sick_leave_from?->format('d/m/Y') }} — {{ $summary->sick_leave_to?->format('d/m/Y') }})</td></tr>@endif
                        @if($summary->referral_to)<tr><td class="text-muted">Rujukan</td><td>{{ $summary->referral_to }} — {{ $summary->referral_notes }}</td></tr>@endif
                    </table>
                    <div class="mt-2 d-flex gap-1">
                        <a href="{{ route('visit-summary.show', $summary) }}" class="btn btn-sm btn-outline-info">Detail</a>
                        <a href="{{ route('letters.sick-leave', $reg) }}" target="_blank" class="btn btn-sm btn-outline-danger">Surat Sakit</a>
                        <a href="{{ route('letters.health-certificate', $reg) }}" target="_blank" class="btn btn-sm btn-outline-success">Surat Sehat</a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Informed Consent --}}
            @php $consents = $mr->informedConsents; @endphp
            @if($consents->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-signature me-1 text-primary"></i>Informed Consent</h6>
                    <a href="{{ route('informed-consents.create', ['medical_record_id' => $mr->id, 'patient_id' => $mr->patient_id, 'registration_id' => $reg?->id]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i>Baru
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Tipe</th><th>Tindakan</th><th>Status</th><th>TTD Pasien</th><th>TTD Dokter</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($consents as $ic)
                                    <tr>
                                        <td class="small">{{ $consentTypes[$ic->consent_type] ?? $ic->consent_type }}</td>
                                        <td class="small">{{ $ic->procedure_name ?? ($ic->procedureIcd9?->name ?? '-') }}</td>
                                        <td>
                                            @php $icBadge = match($ic->status) { 'draft'=>'bg-secondary','signed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-warning' }; @endphp
                                            <span class="badge {{ $icBadge }}">{{ $ic->status }}</span>
                                        </td>
                                        <td class="small">{{ $ic->patient_signed_at ? $ic->patient_signed_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="small">{{ $ic->signed_at ? $ic->signed_at->format('d/m/Y H:i') : '-' }}</td>
                                        <td>
                                            <a href="{{ route('informed-consents.show', $ic) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Audit Trail --}}
            @php $audits = $mr->audits()->with('user')->limit(50)->get(); @endphp
            @if($audits->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-clipboard-list me-1 text-muted"></i>Audit Trail</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Field</th><th>Lama</th><th>Baru</th></tr>
                            </thead>
                            <tbody>
                                @foreach($audits as $audit)
                                    <tr>
                                        <td class="small text-muted">{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="small">{{ $audit->user?->name ?? '-' }}</td>
                                        <td>
                                            @php $aBadge = match($audit->action) { 'created'=>'bg-success','updated'=>'bg-info','deleted'=>'bg-danger','restored'=>'bg-warning', default=>'bg-secondary' }; @endphp
                                            <span class="badge {{ $aBadge }}">{{ $audit->action }}</span>
                                        </td>
                                        <td class="small">{{ $audit->field_name ?? '-' }}</td>
                                        <td class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($audit->old_value ?? '-', 40) }}</td>
                                        <td class="small text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($audit->new_value ?? '-', 40) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Berkas --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-paperclip me-1 text-primary"></i>Berkas</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                </div>
                <div class="card-body p-0">
                    @php $files = $mr->attachments ?? collect(); @endphp
                    @if($files->count())
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama File</th>
                                        <th>Kategori</th>
                                        <th>Ukuran</th>
                                        <th>Tgl</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($files as $file)
                                        <tr>
                                            <td><i class="fas {{ match($file->file_type) { 'image/jpeg','image/png','image/gif' => 'fa-image text-success', 'application/pdf' => 'fa-file-pdf text-danger', default => 'fa-file text-muted' } }} me-1"></i>{{ $file->file_name }}</td>
                                            <td>
                                                @php
                                                    $catBadge = match($file->category) { 'lab_result'=>'bg-info', 'xray'=>'bg-warning', 'photo'=>'bg-success', default=>'bg-secondary' };
                                                    $catLabel = match($file->category) { 'lab_result'=>'Lab', 'xray'=>'Radiologi', 'photo'=>'Foto', default=>'Lain' };
                                                @endphp
                                                <span class="badge {{ $catBadge }}">{{ $catLabel }}</span>
                                            </td>
                                            <td class="small">{{ $file->file_size > 1048576 ? round($file->file_size/1048576,1).' MB' : ($file->file_size > 1024 ? round($file->file_size/1024,1).' KB' : ($file->file_size ?? '-').' B') }}</td>
                                            <td class="small text-muted">{{ $file->created_at?->format('d/m/Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('attachments.download', $file) }}" class="btn btn-sm btn-outline-info" title="Download"><i class="fas fa-download"></i></a>
                                                    <form method="POST" action="{{ route('attachments.destroy', $file) }}" onsubmit="return confirm('Hapus berkas ini?')">@csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-paperclip fa-2x mb-2 d-block"></i>
                            Belum ada berkas diunggah
                        </div>
                    @endif
                </div>
            </div>

            {{-- Modal Upload --}}
            <div class="modal fade" id="uploadModal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('attachments.store', $mr) }}" enctype="multipart/form-data" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Upload Berkas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">File <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" required>
                                <small class="text-muted">Format: JPG, PNG, GIF, PDF, DOC, DOCX. Maks 10 MB.</small>
                                @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-select">
                                    <option value="other">Lainnya</option>
                                    <option value="lab_result">Hasil Lab</option>
                                    <option value="photo">Foto</option>
                                    <option value="xray">Radiologi</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function signRme(id) {
    if (!confirm('Tanda tangani rekam medis ini? TTE tidak dapat dibatalkan setelah ditandatangani.')) return;

    fetch('{{ route('medical-records.sign', $mr) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert(res.message || 'Gagal menandatangani');
        }
    })
    .catch(() => alert('Terjadi kesalahan saat menandatangani'));
}
</script>
@endpush

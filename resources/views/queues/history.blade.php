@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Pendaftaran</h4>
        <a href="{{ route('registration.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Registrasi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">No. Registrasi</td>
                            <td class="fw-bold">{{ $registration->registration_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $registration->registration_date?->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sumber</td>
                            <td><span class="badge bg-{{ $registration->source == 'mjkn' ? 'primary' : 'secondary' }}">{{ $registration->source }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Layanan</td>
                            <td>
                                @php $sm = ['registered'=>'warning','in_consultation'=>'info','lab'=>'secondary','pharmacy'=>'secondary','cashier'=>'secondary','completed'=>'success','cancelled'=>'danger']; @endphp
                                <span class="badge bg-{{ $sm[$registration->service_status] ?? 'secondary' }}">{{ str_replace('_',' ',$registration->service_status) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. SEP</td>
                            <td>{{ $registration->no_sep ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Poliklinik</td>
                            <td>{{ $registration->polyclinic?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dokter</td>
                            <td>{{ $registration->doctor?->name ?? '-' }}</td>
                        </tr>
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
                            <td class="text-muted">Nama</td>
                            <td class="fw-bold">{{ $registration->patient?->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $registration->patient?->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. RM</td>
                            <td>{{ $registration->patient?->no_rm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Usia</td>
                            <td>{{ $registration->age_text ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Telepon</td>
                            <td>{{ $registration->patient?->phone ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($registration->queue)
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Antrean</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Nomor Antrean</td>
                            <td class="fw-bold fs-5">{{ $registration->queue->queue_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php $qm = ['waiting'=>'warning','called'=>'info','in_progress'=>'primary','completed'=>'success','cancelled'=>'secondary']; @endphp
                                <span class="badge bg-{{ $qm[$registration->queue->status] ?? 'secondary' }}">{{ $registration->queue->status }}</span>
                            </td>
                        </tr>
                    </table>

                    @if($registration->queue->queueMilestones->isNotEmpty())
                    <hr>
                    <h6 class="small text-muted mb-2">Milestone BPJS</h6>
                    <div class="timeline small">
                        @foreach($registration->queue->queueMilestones as $mile)
                        <div class="d-flex gap-2 mb-1">
                            <span class="text-muted">Task {{ $mile->task_id }}:</span>
                            <span>{{ $mile->status }}</span>
                            <span class="text-muted ms-auto">{{ $mile->created_at?->format('H:i') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($registration->queue->queueCalls->isNotEmpty())
                    <hr>
                    <h6 class="small text-muted mb-2">Riwayat Panggilan</h6>
                    <div class="timeline small">
                        @foreach($registration->queue->queueCalls as $call)
                        <div class="d-flex gap-2 mb-1">
                            <span class="badge bg-info">#{{ $call->call_sequence }}</span>
                            <span>Dipanggil</span>
                            <span class="text-muted ms-auto">{{ $call->created_at?->format('H:i') }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-7">
            @if($registration->medicalRecords->isNotEmpty())
                @foreach($registration->medicalRecords as $record)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Rekam Medis — {{ $record->visit_date?->format('d/m/Y') }}</h6>
                        <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Detail
                        </a>
                    </div>
                    <div class="card-body">
                        @if($record->subjective_complaint)
                        <div class="mb-2">
                            <small class="text-muted d-block">Subjective:</small>
                            <p class="mb-0">{{ $record->subjective_complaint }}</p>
                        </div>
                        @endif
                        @if($record->diagnosis_primary)
                        <div>
                            <small class="text-muted d-block">Diagnosis Utama:</small>
                            <span class="badge bg-danger">{{ $record->diagnosis_primary }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-file-text fs-1 d-block mb-2"></i>
                        Belum ada rekam medis untuk kunjungan ini
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
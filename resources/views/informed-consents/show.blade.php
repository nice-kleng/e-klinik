@inject('carbon', 'Illuminate\Support\Carbon')

@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Informed Consent #{{ $informedConsent->id }}</h4>
        <div class="d-flex gap-2">
            @if($informedConsent->status === 'signed' && $informedConsent->signature_hash)
            <a href="{{ route('informed-consents.pdf', $informedConsent) }}" class="btn btn-outline-danger" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
            @endif
            <a href="{{ route('informed-consents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            {{-- Informasi Pasien & Tindakan --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Informasi Tindakan</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted" style="width:160px">Pasien</td><td><strong>{{ $informedConsent->patient->name ?? '-' }}</strong> ({{ $informedConsent->patient->no_rm ?? '-' }})</td></tr>
                        <tr><td class="text-muted">Tipe</td><td>{{ $consentTypes[$informedConsent->consent_type] ?? $informedConsent->consent_type }}</td></tr>
                        <tr><td class="text-muted">Prosedur</td><td>{{ $informedConsent->procedure_name ?? ($informedConsent->procedureIcd9?->code . ' — ' . $informedConsent->procedureIcd9?->name ?? '-') }}</td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge {{ match($informedConsent->status) { 'draft'=>'bg-secondary','signed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-warning' } }}">{{ $informedConsent->status }}</span></td></tr>
                        <tr><td class="text-muted">Dibuat oleh</td><td>{{ $informedConsent->creator?->name ?? '-' }} · {{ $informedConsent->created_at?->format('d/m/Y H:i') ?? '-' }}</td></tr>
                        @if($informedConsent->medicalRecord)<tr><td class="text-muted">RME Terkait</td><td><a href="{{ route('medical-records.show', $informedConsent->medicalRecord) }}">RME #{{ $informedConsent->medicalRecord->id }}</a></td></tr>@endif
                    </table>
                </div>
            </div>

            {{-- Detail Medis --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Detail Medis</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Diagnosis</h6>
                            <p>{{ $informedConsent->diagnosis ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Tujuan Tindakan</h6>
                            <p>{{ $informedConsent->purpose ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Risiko</h6>
                            <p>{{ $informedConsent->risks ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Manfaat</h6>
                            <p>{{ $informedConsent->benefits ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Alternatif Tindakan</h6>
                            <p>{{ $informedConsent->alternatives ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted small">Rekomendasi Dokter</h6>
                            <p>{{ $informedConsent->doctor_recommendation ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Prosedur Terkait --}}
            @if($informedConsent->procedures->isNotEmpty())
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Prosedur Terkait</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>Kode</th><th>Nama Tindakan</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach($informedConsent->procedures as $proc)
                                <tr>
                                    <td><span class="badge bg-warning text-dark">{{ $proc->icd9CmDiagnosis?->code ?? '-' }}</span></td>
                                    <td>{{ $proc->icd9CmDiagnosis?->name ?? '-' }}</td>
                                    <td><span class="badge {{ match($proc->status) { 'ordered'=>'bg-info','in_progress'=>'bg-warning','completed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-secondary' } }}">{{ $proc->status ?? 'ordered' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            {{-- Signature Status --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Status Tanda Tangan</h6></div>
                <div class="card-body">
                    {{-- Pasien --}}
                    <div class="mb-4">
                        <h6 class="text-muted small">Persetujuan Pasien</h6>
                        @if($informedConsent->patient_signed_at)
                            <div class="alert alert-success py-2 mb-0">
                                <i class="fas fa-check-circle me-1"></i> Disetujui oleh <strong>{{ $informedConsent->patient_name }}</strong>
                                <div class="small text-muted">{{ $informedConsent->patient_signed_at->format('d/m/Y H:i:s') }}</div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="fas fa-clock me-1"></i> Menunggu persetujuan pasien
                            </div>
                            @if($informedConsent->status === 'draft')
                            <form action="{{ route('informed-consents.sign-patient', $informedConsent) }}" method="POST" class="mt-2">
                                @csrf
                                <input type="text" name="patient_name" class="form-control form-control-sm mb-2" placeholder="Nama pasien" value="{{ $informedConsent->patient?->name }}" required>
                                <button type="submit" class="btn btn-success btn-sm w-100">
                                    <i class="fas fa-pen me-1"></i>Setujui & Tanda Tangan
                                </button>
                            </form>
                            @endif
                        @endif
                    </div>

                    {{-- Dokter --}}
                    <div class="mb-3">
                        <h6 class="text-muted small">Tanda Tangan Dokter</h6>
                        @if($informedConsent->signed_at)
                            <div class="alert alert-success py-2 mb-0">
                                <i class="fas fa-check-circle me-1"></i> Ditandatangani oleh <strong>{{ $informedConsent->signer?->name ?? '-' }}</strong>
                                <div class="small text-muted">{{ $informedConsent->signed_at->format('d/m/Y H:i:s') }}</div>
                                @if($informedConsent->signature_hash)
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-hashtag me-1"></i>Hash: <code class="small">{{ substr($informedConsent->signature_hash, 0, 16) }}...</code>
                                </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-warning py-2 mb-0">
                                <i class="fas fa-clock me-1"></i>
                                @if($informedConsent->patient_signed_at)
                                    Menunggu tanda tangan dokter
                                @else
                                    Pasien harus menyetujui terlebih dahulu
                                @endif
                            </div>
                            @if($informedConsent->status === 'draft' && $informedConsent->patient_signed_at)
                            <form action="{{ route('informed-consents.sign-doctor', $informedConsent) }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-signature me-1"></i>Tanda Tangan Dokter
                                </button>
                            </form>
                            @endif
                        @endif
                    </div>

                    {{-- Saksi --}}
                    @if($informedConsent->witness_name)
                    <div class="small text-muted">
                        <strong>Saksi:</strong> {{ $informedConsent->witness_name }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            @if($informedConsent->status === 'draft')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('informed-consents.edit', $informedConsent) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <form action="{{ route('informed-consents.destroy', $informedConsent) }}" method="POST" onsubmit="return confirm('Batalkan informed consent ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-times me-1"></i>Batalkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

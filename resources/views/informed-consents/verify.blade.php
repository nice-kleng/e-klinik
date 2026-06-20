@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card border-0 shadow">
                <div class="card-body text-center py-5">
                    @if($consent)
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                        </div>
                        <h4 class="mb-1">Informed Consent Terverifikasi</h4>
                        <p class="text-muted">Dokumen ini sah dan telah ditandatangani secara elektronik</p>
                        <hr>
                        <table class="table table-sm text-start mb-0">
                            <tr><td class="text-muted" style="width:140px">Pasien</td><td><strong>{{ $consent->patient->name ?? '-' }}</strong></td></tr>
                            <tr><td class="text-muted">Tipe</td><td>{{ $consent->consent_type }}</td></tr>
                            <tr><td class="text-muted">Prosedur</td><td>{{ $consent->procedure_name ?? ($consent->procedureIcd9?->name ?? '-') }}</td></tr>
                            <tr><td class="text-muted">TTD Pasien</td><td>{{ $consent->patient_signed_at?->format('d/m/Y H:i:s') ?? '-' }}</td></tr>
                            <tr><td class="text-muted">TTD Dokter</td><td>{{ $consent->signed_at?->format('d/m/Y H:i:s') ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Dokter</td><td>{{ $consent->signer?->name ?? '-' }}</td></tr>
                            <tr><td class="text-muted">Hash</td><td><code class="small">{{ $consent->signature_hash }}</code></td></tr>
                        </table>
                    @else
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h4 class="mb-1">Informed Consent Tidak Ditemukan</h4>
                        <p class="text-muted">Hash yang discan tidak ditemukan atau dokumen telah diubah</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

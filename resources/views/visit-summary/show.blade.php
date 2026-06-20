@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Resume Kunjungan</h4>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('visit-summary.edit', $visitSummary) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
            <a href="{{ route('letters.sick-leave', $visitSummary->registration) }}" class="btn btn-outline-info" target="_blank">Surat Sakit</a>
            <a href="{{ route('letters.health-certificate', $visitSummary->registration) }}" class="btn btn-outline-info" target="_blank">Surat Sehat</a>
            @if($visitSummary->referral_to)
                <a href="{{ route('letters.referral', $visitSummary->registration) }}" class="btn btn-outline-info" target="_blank">Surat Rujukan</a>
            @endif
            <a href="{{ route('letters.medical-certificate', $visitSummary->registration) }}" class="btn btn-outline-info" target="_blank">Keterangan Medis</a>
            <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Detail Resume</h6></div>
        <div class="card-body">
            <table class="table table-sm mb-0">
                <tr><td class="text-muted" style="width:160px">Pasien</td><td><strong>{{ $visitSummary->registration->patient->name ?? '-' }}</strong></td></tr>
                <tr><td class="text-muted">No. RM</td><td>{{ $visitSummary->registration->patient->no_rm ?? '-' }}</td></tr>
                <tr><td class="text-muted">Poli</td><td>{{ $visitSummary->registration->polyclinic->name ?? '-' }}</td></tr>
                <tr><td class="text-muted">Dokter</td><td>{{ $visitSummary->registration->doctor->name ?? '-' }}</td></tr>
                <tr><td class="text-muted">Tanggal Kunjungan</td><td>{{ $visitSummary->registration->registration_date?->format('d/m/Y') }}</td></tr>
                <tr><td class="text-muted">Diagnosis Akhir</td><td>{{ $visitSummary->final_diagnosis ?? '-' }}</td></tr>
                <tr><td class="text-muted">Status Pulang</td><td>{{ ucfirst($visitSummary->discharge_status) }}</td></tr>
                <tr><td class="text-muted">Rencana Kontrol</td><td>{{ $visitSummary->follow_up_plan ?? '-' }}</td></tr>
                @if($visitSummary->referral_to)
                    <tr><td class="text-muted">Rujuk ke</td><td>{{ $visitSummary->referral_to }}</td></tr>
                    <tr><td class="text-muted">Catatan Rujukan</td><td>{{ $visitSummary->referral_notes ?? '-' }}</td></tr>
                @endif
                @if($visitSummary->sick_leave_days)
                    <tr><td class="text-muted">Cuti Sakit</td><td>{{ $visitSummary->sick_leave_days }} hari ({{ $visitSummary->sick_leave_from?->format('d/m/Y') }} - {{ $visitSummary->sick_leave_to?->format('d/m/Y') }})</td></tr>
                @endif
                <tr><td class="text-muted">Dibuat oleh</td><td>{{ $visitSummary->creator->name ?? '-' }}</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection

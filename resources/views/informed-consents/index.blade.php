@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    @include('components.alert')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Informed Consent</h4>
        <a href="{{ route('informed-consents.create') }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i>Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Pasien</th>
                            <th>Tipe</th>
                            <th>Tindakan</th>
                            <th>Status</th>
                            <th>TTD Pasien</th>
                            <th>TTD Dokter</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consents as $ic)
                            <tr>
                                <td class="small">{{ $ic->id }}</td>
                                <td>
                                    <strong>{{ $ic->patient->name ?? '-' }}</strong>
                                    <div class="small text-muted">{{ $ic->patient->no_rm ?? '' }}</div>
                                </td>
                                <td class="small">{{ $consentTypes[$ic->consent_type] ?? $ic->consent_type }}</td>
                                <td class="small">{{ $ic->procedure_name ?? ($ic->procedureIcd9?->name ?? '-') }}</td>
                                <td>
                                    @php
                                        $badge = match($ic->status) { 'draft'=>'bg-secondary','signed'=>'bg-success','cancelled'=>'bg-danger', default=>'bg-warning' };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $ic->status }}</span>
                                </td>
                                <td class="small">{{ $ic->patient_signed_at ? $ic->patient_signed_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="small">{{ $ic->signed_at ? $ic->signed_at->format('d/m/Y H:i') : ($ic->signer?->name ?? '-') }}</td>
                                <td>
                                    <a href="{{ route('informed-consents.show', $ic) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($ic->status === 'draft')
                                    <a href="{{ route('informed-consents.edit', $ic) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-file-signature fa-2x mb-2 d-block"></i>
                                    Belum ada informed consent
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $consents->links() }}
    </div>
</div>
@endsection

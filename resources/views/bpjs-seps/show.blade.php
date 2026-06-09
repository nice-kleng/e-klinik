@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail SEP</h4>
        <div class="d-flex gap-2">
            @if($bpjsSep->status == 'active')
                <form action="{{ route('bpjs-seps.destroy', $bpjsSep) }}" method="POST" onsubmit="return confirm('Nonaktifkan SEP {{ $bpjsSep->no_sep }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">Nonaktifkan SEP</button>
                </form>
            @endif
            <a href="{{ route('bpjs-seps.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi SEP</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:160px">No. SEP</td>
                            <td><strong class="text-primary">{{ $bpjsSep->no_sep }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @if($bpjsSep->status == 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Kartu</td>
                            <td>{{ $bpjsSep->no_kartu }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Pelayanan</td>
                            <td>{{ $bpjsSep->tgl_pelayanan?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kode Poli</td>
                            <td>{{ $bpjsSep->kode_poli }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kode Dokter</td>
                            <td>{{ $bpjsSep->kode_dokter ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Diagnosa</td>
                            <td>{{ $bpjsSep->diagnosa ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Rujukan</td>
                            <td>{{ $bpjsSep->no_rujukan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Catatan</td>
                            <td>{{ $bpjsSep->catatan ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat Oleh</td>
                            <td>{{ $bpjsSep->creator?->name ?? '-' }}, {{ $bpjsSep->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Data Pasien</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:160px">Nama</td>
                            <td>{{ $bpjsSep->patient->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. RM</td>
                            <td>{{ $bpjsSep->patient->no_rm ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>{{ $bpjsSep->patient->nik ?? '-' }}</td>
                        </tr>
                    </table>
                    @if($bpjsSep->patient)
                        <a href="{{ route('patients.show', $bpjsSep->patient) }}" class="btn btn-sm btn-outline-info mt-2">Detail Pasien</a>
                    @endif
                </div>
            </div>

            @if($bpjsSep->queue)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Antrean Terkait</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted" style="width:160px">No. Antrean</td>
                                <td>{{ $bpjsSep->queue->queue_number }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Poli</td>
                                <td>{{ $bpjsSep->queue->polyclinic?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Dokter</td>
                                <td>{{ $bpjsSep->queue->doctor?->name ?? '-' }}</td>
                            </tr>
                        </table>
                        <a href="{{ route('queues.show', $bpjsSep->queue) }}" class="btn btn-sm btn-outline-info mt-2">Detail Antrean</a>
                    </div>
                </div>
            @endif

            @if($responseDetail)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Response BPJS</h6>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0" style="max-height:300px;overflow:auto;font-size:12px;">{{ json_encode($responseDetail, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

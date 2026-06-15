@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Permintaan Lab #{{ $labRequest->id }}</h4>
        <div class="d-flex gap-2">
            @if($labRequest->status !== 'completed' && $labRequest->status !== 'cancelled')
                @hasanyrole('admin|laborant')
                <form method="POST" action="{{ route('lab-requests.update-status', $labRequest) }}" class="d-inline">
                    @csrf
                    @method('PATCH')
                    @php
                        $nextStatus = match($labRequest->status) {
                            'requested' => 'sampled',
                            'sampled' => 'processing',
                            'processing' => 'completed',
                            default => null
                        };
                        $nextLabel = match($labRequest->status) {
                            'requested' => 'Ambil Sampel',
                            'sampled' => 'Proses',
                            'processing' => 'Selesai',
                            default => null
                        };
                    @endphp
                    @if($nextStatus)
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button type="submit" class="btn btn-primary">{{ $nextLabel }}</button>
                    @endif
                </form>
                @endcan
            @endif
            <a href="{{ route('lab-requests.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Informasi Permintaan</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:140px">Tanggal</th><td>{{ $labRequest->created_at->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Status</th>
                            <td>
                                @php
                                    $sc = match($labRequest->status) {
                                        'requested' => 'bg-secondary', 'sampled' => 'bg-info',
                                        'processing' => 'bg-warning', 'completed' => 'bg-success',
                                        'cancelled' => 'bg-danger', default => 'bg-secondary'
                                    };
                                    $sl = match($labRequest->status) {
                                        'requested' => 'Diminta', 'sampled' => 'Sampel Diambil',
                                        'processing' => 'Diproses', 'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan', default => $labRequest->status
                                    };
                                @endphp
                                <span class="badge {{ $sc }}">{{ $sl }}</span>
                            </td>
                        </tr>
                        <tr><th>Dokter</th><td>{{ $labRequest->doctor->name ?? $labRequest->doctor->user->name ?? '-' }}</td></tr>
                        <tr><th>Catatan</th><td>{{ $labRequest->notes ?? '-' }}</td></tr>
                        <tr><th>Dibuat oleh</th><td>{{ $labRequest->creator->name ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Data Pasien</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:140px">No. RM</th><td>{{ $labRequest->patient->no_rm ?? '-' }}</td></tr>
                        <tr><th>Nama</th><td>{{ $labRequest->patient->name ?? '-' }}</td></tr>
                        <tr><th>Tanggal Lahir</th><td>{{ $labRequest->patient->birth_date ? $labRequest->patient->birth_date->format('d/m/Y') : '-' }}</td></tr>
                        <tr><th>Jenis Kelamin</th><td>{{ $labRequest->patient->gender ?? '-' }}</td></tr>
                        <tr><th>No. Telepon</th><td>{{ $labRequest->patient->phone ?? '-' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white fw-semibold">Daftar Pemeriksaan</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Tes</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Hasil</th>
                        <th>Flag</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labRequest->items as $item)
                        <tr>
                            <td>{{ $item->labTest->code ?? '-' }}</td>
                            <td>{{ $item->labTest->name ?? '-' }}</td>
                            <td>{{ $item->labTest->category->name ?? '-' }}</td>
                            <td>
                                @php
                                    $isc = $item->status == 'completed' ? 'bg-success' : ($item->status == 'cancelled' ? 'bg-danger' : 'bg-warning');
                                @endphp
                                <span class="badge {{ $isc }}">
                                    {{ $item->status == 'completed' ? 'Selesai' : ($item->status == 'cancelled' ? 'Batal' : 'Menunggu') }}
                                </span>
                            </td>
                            <td>{{ $item->result->result_value ?? $item->result->result_text ?? '-' }}</td>
                            <td>
                                @if($item->result)
                                    @php
                                        $fc = match($item->result->flag) {
                                            'normal' => 'bg-success', 'abnormal' => 'bg-warning',
                                            'critical' => 'bg-danger', default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $fc }}">{{ ucfirst($item->result->flag) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada item pemeriksaan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($labRequest->status !== 'completed' && $labRequest->status !== 'cancelled')
        <a href="{{ route('lab-results.input', $labRequest) }}" class="btn btn-success">Input Hasil Lab</a>
    @endif
</div>
@endsection

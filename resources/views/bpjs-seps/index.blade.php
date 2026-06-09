@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">SEP (Surat Eligibilitas Peserta)</h4>
        <a href="{{ route('bpjs-seps.create') }}" class="btn btn-primary">Buat SEP Baru</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="no_sep" class="form-control" placeholder="Cari No. SEP" value="{{ request('no_sep') }}">
                </div>
                <div class="col-md-3">
                    <select name="patient_id" class="form-control">
                        <option value="">Semua Pasien</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>{{ $p->no_rm }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="tgl_awal" class="form-control" value="{{ request('tgl_awal') }}" placeholder="Tgl Awal">
                </div>
                <div class="col-md-2">
                    <input type="date" name="tgl_akhir" class="form-control" value="{{ request('tgl_akhir') }}" placeholder="Tgl Akhir">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>No. SEP</th>
                        <th>Pasien</th>
                        <th>No. Kartu</th>
                        <th>Tgl Pelayanan</th>
                        <th>Poli</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seps as $sep)
                        <tr>
                            <td><strong>{{ $sep->no_sep }}</strong></td>
                            <td>
                                {{ $sep->patient->name ?? '-' }}
                                <small class="text-muted d-block">{{ $sep->patient->no_rm ?? '' }}</small>
                            </td>
                            <td>{{ $sep->no_kartu }}</td>
                            <td>{{ $sep->tgl_pelayanan?->format('d/m/Y') }}</td>
                            <td>{{ $sep->queue?->polyclinic?->name ?? $sep->kode_poli }}</td>
                            <td>
                                @if($sep->status == 'active')
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('bpjs-seps.show', $sep) }}" class="btn btn-sm btn-info">Detail</a>
                                @if($sep->status == 'active')
                                    <form action="{{ route('bpjs-seps.destroy', $sep) }}" method="POST" class="d-inline" onsubmit="return confirm('Nonaktifkan SEP {{ $sep->no_sep }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Nonaktifkan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada SEP</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($seps->hasPages())
            <div class="card-footer">
                {{ $seps->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

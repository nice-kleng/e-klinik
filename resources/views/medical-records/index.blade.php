@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Rekam Medis</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                    <div class="col-md-4">
                    <label class="form-label">Poliklinik</label>
                    <select name="polyclinic_id" class="form-select">
                        <option value="">Semua Poli</option>
                        @foreach($polyclinics as $poly)
                            <option value="{{ $poly->id }}" {{ $polyclinicId == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-4">
                    <button class="btn btn-primary" type="submit">Filter</button>
                    <a href="{{ route('medical-records.index') }}" class="btn btn-outline-secondary mt-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Pasien</th>
                        <th>No. RM</th>
                        <th>Poliklinik</th>
                        <th>Dokter</th>
                        <th>Tgl Kunjungan</th>
                        <th>Diagnosis Utama</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $queue)
                        @php $reg = $queue->registration; $mr = $queue->medicalRecord; @endphp
                        <tr>
                            <td>{{ $reg?->patient?->name ?? '-' }}</td>
                            <td>{{ $reg?->patient?->no_rm ?? '-' }}</td>
                            <td>{{ $queue->polyclinic?->name ?? '-' }}</td>
                            <td>{{ $reg?->doctor?->name ?? '-' }}</td>
                            <td>{{ $queue->queue_date?->format('d/m/Y') }}</td>
                            <td>
                                @if($mr && $mr->primaryDiagnosis)
                                    @foreach($mr->primaryDiagnosis as $diag)
                                        <span class="badge bg-info">{{ $diag->icd10Diagnosis->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('medical-records.workspace', $queue) }}" class="btn btn-sm btn-warning" title="Buka RME">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada pasien untuk tanggal ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $queues->links() }}
        </div>
    </div>
</div>
@endsection

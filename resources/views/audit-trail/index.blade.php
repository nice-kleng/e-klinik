@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Audit Trail</h4>
    </div>

    @include('components.alert')

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Aksi</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">User</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Cari Pasien</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama atau No. RM..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Cari</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Pasien</th>
                            <th>Aksi</th>
                            <th>Field</th>
                            <th>Nilai Lama</th>
                            <th>Nilai Baru</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            <tr>
                                <td class="small text-muted">{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="small">{{ $audit->user?->name ?? '-' }}</td>
                                <td class="small">
                                    @if($audit->medicalRecord?->patient)
                                        <a href="{{ route('medical-records.show', $audit->medicalRecord->id) }}" class="text-decoration-none">
                                            {{ $audit->medicalRecord->patient->name }}
                                            <small class="text-muted">({{ $audit->medicalRecord->patient->no_rm }})</small>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $aBadge = match($audit->action) { 'created'=>'bg-success','updated'=>'bg-info','deleted'=>'bg-danger','restored'=>'bg-warning', default=>'bg-secondary' };
                                    @endphp
                                    <span class="badge {{ $aBadge }}">{{ $audit->action }}</span>
                                </td>
                                <td class="small">{{ $audit->field_name ?? '-' }}</td>
                                <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                    title="{{ $audit->old_value ?? '' }}">{{ Str::limit($audit->old_value ?? '-', 50) }}</td>
                                <td class="small text-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                    title="{{ $audit->new_value ?? '' }}">{{ Str::limit($audit->new_value ?? '-', 50) }}</td>
                                <td class="small text-muted">{{ $audit->ip_address ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                    Tidak ada data audit trail
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($audits->hasPages())
            <div class="card-footer bg-white">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

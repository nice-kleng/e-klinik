@extends('layouts.volt')

@section('title', 'Satu Sehat - Dashboard')

@section('header', 'Dashboard Satu Sehat')

@section('content')
<div class="container-fluid">
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-user text-primary fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $stats['patients']['synced'] }}/{{ $stats['patients']['total'] }}</h5>
                    <small class="text-muted">Pasien</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-hospital text-info fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $stats['locations']['synced'] }}/{{ $stats['locations']['total'] }}</h5>
                    <small class="text-muted">Lokasi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-user-md text-warning fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $stats['practitioners']['synced'] }}/{{ $stats['practitioners']['total'] }}</h5>
                    <small class="text-muted">Praktisi</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-door-open text-success fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $stats['encounters']['synced'] }}/{{ $stats['encounters']['total'] }}</h5>
                    <small class="text-muted">Kunjungan</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-stethoscope text-danger fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $stats['conditions']['synced'] }}/{{ $stats['conditions']['total'] }}</h5>
                    <small class="text-muted">Diagnosa</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-circle d-inline-flex mb-2">
                        <i class="fas fa-plug text-secondary fs-4"></i>
                    </div>
                    <h5 class="mb-0">{{ $token ? 'Terhubung' : 'Putus' }}</h5>
                    <small class="text-muted">API SS</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pb-0">
                    <h6 class="mb-0">Konfigurasi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr><td>Organization ID</td><td><code>{{ $orgId }}</code></td></tr>
                        <tr><td>Base URL</td><td><code>{{ config('satusehat.base_url') }}</code></td></tr>
                        <tr><td>Auth URL</td><td><code>{{ config('satusehat.auth_url') }}</code></td></tr>
                        <tr>
                            <td>Token</td>
                            <td>
                                @if ($token)
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Valid</span>
                                    <small class="text-muted d-block">({{ substr($token, 0, 20) }}...)</small>
                                @else
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> Tidak valid</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom pb-0">
                    <h6 class="mb-0">Aktivitas Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($logs as $log)
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <div>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }} me-1">{{ $log->action }}</span>
                                    <strong>{{ $log->resource_type }}</strong>
                                    <small class="text-muted ms-1">{{ Str::limit($log->error_message ?? '', 40) }}</small>
                                </div>
                                <small class="text-muted">{{ $log->created_at?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <div class="list-group-item text-center py-3 text-muted">Belum ada aktivitas</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <a href="{{ route('satusehat.locations') }}" class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-hospital me-1"></i> Kelola Lokasi
                    </a>
                    <a href="{{ route('satusehat.practitioners') }}" class="btn btn-sm btn-outline-warning me-2">
                        <i class="fas fa-user-md me-1"></i> Kelola Praktisi
                    </a>
                    <a href="{{ route('satusehat.medicines') }}" class="btn btn-sm btn-outline-info me-2">
                        <i class="fas fa-pills me-1"></i> Kelola Obat & Alkes
                    </a>
                    <a href="{{ route('satusehat.sync-all') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-sync me-1"></i> Sync Semua
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

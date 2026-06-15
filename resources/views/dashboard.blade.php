@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard</h4>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="form-label mb-0 text-muted small">Tanggal</label>
            <input type="date" name="date" class="form-control form-control-sm" style="width:160px" value="{{ $date ?? date('Y-m-d') }}">
            <button class="btn btn-sm btn-primary" type="submit">Tampilkan</button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Total Pasien</small>
                            <h3 class="mb-0 mt-1">{{ $totalPatientsToday ?? 0 }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <span class="text-primary fs-4">ðŸ‘¤</span>
                        </div>
                    </div>
                    <small class="text-muted">Hari ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Antrean</small>
                            <h3 class="mb-0 mt-1">{{ $queueStats['waiting'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <span class="text-warning fs-4">â³</span>
                        </div>
                    </div>
                    <small class="text-muted">Menunggu</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Selesai</small>
                            <h3 class="mb-0 mt-1">{{ $queueStats['completed'] ?? 0 }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <span class="text-success fs-4">âœ…</span>
                        </div>
                    </div>
                    <small class="text-muted">Hari ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase">Dokter</small>
                            <h3 class="mb-0 mt-1">{{ \App\Models\Doctor::where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <span class="text-info fs-4">ðŸ‘¨â€âš•ï¸</span>
                        </div>
                    </div>
                    <small class="text-muted">Aktif</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Antrean per Poliklinik</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Poliklinik</th>
                        <th class="text-center">Menunggu</th>
                        <th class="text-center">Dipanggil</th>
                        <th class="text-center">Diproses</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queueByPolyclinic as $item)
                        <tr>
                            <td>{{ $item['polyclinic_name'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-warning">{{ $item['waiting'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $item['called'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $item['in_progress'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $item['completed'] ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <strong>{{ ($item['waiting'] ?? 0) + ($item['called'] ?? 0) + ($item['in_progress'] ?? 0) + ($item['completed'] ?? 0) }}</strong>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data antrean untuk hari ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

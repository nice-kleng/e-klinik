@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Hasil Laboratorium</h4>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Cari Pasien</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama atau No. RM..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tes</label>
                    <select name="lab_test_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach($labTests as $test)
                            <option value="{{ $test->id }}" @selected(request('lab_test_id') == $test->id)>{{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Flag</label>
                    <select name="flag" class="form-select">
                        <option value="">Semua</option>
                        <option value="normal" @selected(request('flag') == 'normal')>Normal</option>
                        <option value="abnormal" @selected(request('flag') == 'abnormal')>Abnormal</option>
                        <option value="critical" @selected(request('flag') == 'critical')>Critical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Cari</button>
                    <a href="{{ route('lab-results.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>No. RM</th>
                        <th>Pemeriksaan</th>
                        <th>Hasil</th>
                        <th>Nilai Rujukan</th>
                        <th>Flag</th>
                        <th>Pemeriksa</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr>
                            <td>{{ $result->examined_at ? $result->examined_at->format('d/m/Y') : '-' }}</td>
                            <td>{{ $result->patient->name ?? '-' }}</td>
                            <td>{{ $result->patient->no_rm ?? '-' }}</td>
                            <td>{{ $result->labTest->name ?? '-' }}</td>
                            <td>{{ $result->result_value ?? $result->result_text ?? '-' }}</td>
                            <td>{{ $result->ref_range_text ?? ($result->ref_range_low . ' - ' . $result->ref_range_high) ?? '-' }}</td>
                            <td>
                                @php
                                    $fc = match($result->flag) {
                                        'normal' => 'bg-success',
                                        'abnormal' => 'bg-warning text-dark',
                                        'critical' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $fc }}">{{ ucfirst($result->flag) }}</span>
                            </td>
                            <td>{{ $result->examiner->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('lab-results.edit', $result) }}" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada hasil laboratorium</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $results->links() }}
        </div>
    </div>
</div>
@endsection

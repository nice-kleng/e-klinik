@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Resep Masuk — Farmasi</h4>
        <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Semua Resep</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('prescriptions.pending') }}" class="btn btn-outline-secondary w-100">Reset</a>
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
                        <th>No. Resep</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Tanggal</th>
                        <th>Item</th>
                        <th width="200">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr>
                            <td><strong>{{ $prescription->prescription_number }}</strong></td>
                            <td>{{ $prescription->patient->name ?? '-' }}<br><small class="text-muted">{{ $prescription->patient->no_rm ?? '' }}</small></td>
                            <td>{{ $prescription->doctor->name ?? '-' }}</td>
                            <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                            <td><span class="badge bg-info">{{ $prescription->items_count ?? $prescription->items()->count() }}</span></td>
                            <td>
                                <a href="{{ route('prescriptions.show', $prescription) }}" class="btn btn-sm btn-info">Detail</a>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#dispenseModal{{ $prescription->id }}">
                                    <i class="fas fa-check me-1"></i>Proses
                                </button>
                            </td>
                        </tr>

                        <div class="modal fade" id="dispenseModal{{ $prescription->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('prescriptions.dispense', $prescription) }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Proses Resep</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Yakin akan memproses resep <strong>{{ $prescription->prescription_number }}</strong> untuk <strong>{{ $prescription->patient->name ?? '' }}</strong>?</p>
                                            <p class="text-muted small">Stok obat akan otomatis berkurang sesuai FIFO.</p>
                                            <div class="small text-muted mb-2">
                                                @php
                                                    $items = $prescription->items()->with(['medicine', 'ingredients.medicine'])->get();
                                                @endphp
                                                @foreach($items as $item)
                                                    <div class="mb-1">
                                                        @if($item->is_compound)
                                                            <strong>{{ $item->compound_name ?: 'Racikan' }}</strong>
                                                            @foreach($item->ingredients as $ing)
                                                                <br><span class="ms-2">- {{ $ing->medicine->name ?? '-' }}: {{ $ing->calculated_qty ?? '?' }} tablet</span>
                                                            @endforeach
                                                        @else
                                                            {{ $item->medicine->name ?? '-' }}: {{ $item->quantity }} {{ $item->unit }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Catatan (opsional)</label>
                                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan penyerahan obat"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Ya, Proses & Serahkan</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-2x mb-2 d-block"></i>
                                Tidak ada resep yang perlu diproses
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $prescriptions->links() }}
        </div>
    </div>
</div>
@endsection

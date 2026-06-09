@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Resep</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('prescriptions.print', $prescription) }}" class="btn btn-secondary" target="_blank">Cetak</a>
            <a href="{{ route('prescriptions.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Resep</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">No. Resep</td>
                            <td><strong>{{ $prescription->prescription_number }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>{{ $prescription->prescription_date?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>
                                @php
                                    $badge = match($prescription->status) {
                                        'active' => 'bg-success', 'dispensed' => 'bg-info',
                                        'cancelled' => 'bg-secondary', default => 'bg-warning'
                                    };
                                    $label = match($prescription->status) {
                                        'active' => 'Aktif', 'dispensed' => 'Diberikan',
                                        'cancelled' => 'Dibatalkan', default => $prescription->status
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">{{ $label }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pasien</td>
                            <td>{{ $prescription->patient->name ?? '-' }}<br><small class="text-muted">{{ $prescription->patient->no_rm ?? '' }}</small></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dokter</td>
                            <td>{{ $prescription->doctor->name ?? '-' }}</td>
                        </tr>
                        @if($prescription->notes)
                            <tr>
                                <td class="text-muted">Catatan</td>
                                <td>{{ $prescription->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <a href="{{ route('patients.show', $prescription->patient) }}" class="btn btn-sm btn-outline-info mt-2">Detail Pasien</a>
            <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" class="btn btn-sm btn-outline-info mt-2">Rekam Medis</a>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Item Resep</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Obat</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Aturan Pakai</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prescription->items as $item)
                                <tr>
                                    <td>{{ $item->medicine->name ?? '-' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $item->unit ?? $item->medicine->unit ?? '-' }}</td>
                                    <td>{{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage ?? '-' }}</td>
                                    <td>{{ $item->subtotal ? 'Rp '.number_format($item->subtotal, 0, ',', '.') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Tidak ada item</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

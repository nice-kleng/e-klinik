@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Resep</h4>
        <div class="d-flex gap-2 flex-wrap">
            @if($prescription->status === 'active')
                @php $user = Auth::user(); @endphp
                @if($user->hasRole('admin|doctor'))
                    <a href="{{ route('prescriptions.edit', $prescription) }}" class="btn btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
                @endif
                @if($user->hasRole('admin|pharmacist'))
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#dispenseModal"><i class="fas fa-check me-1"></i>Proses</button>
                @endif
                @if($user->hasRole('admin|doctor'))
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="fas fa-times me-1"></i>Batalkan</button>
                @endif
            @endif
            <a href="{{ route('prescriptions.print', $prescription) }}" class="btn btn-secondary" target="_blank"><i class="fas fa-print me-1"></i>Cetak</a>
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
                        @if($prescription->dispenser)
                        <tr>
                            <td class="text-muted">Diberikan oleh</td>
                            <td>{{ $prescription->dispenser->name ?? '-' }}<br><small class="text-muted">{{ $prescription->dispensed_at?->format('d/m/Y H:i') }}</small></td>
                        </tr>
                        @endif
                        @if($prescription->cancellation_reason)
                        <tr>
                            <td class="text-muted">Alasan batal</td>
                            <td><em>{{ $prescription->cancellation_reason }}</em></td>
                        </tr>
                        @endif
                        @if($prescription->notes)
                            <tr>
                                <td class="text-muted">Catatan</td>
                                <td>{{ $prescription->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2 mt-2 flex-wrap">
                <a href="{{ route('patients.show', $prescription->patient) }}" class="btn btn-sm btn-outline-info">Detail Pasien</a>
                <a href="{{ route('medical-records.show', $prescription->medicalRecord) }}" class="btn btn-sm btn-outline-info">Rekam Medis</a>
            </div>
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
                                <th>Aturan Pakai</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prescription->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->medicine->name ?? '-' }}
                                        @if($item->is_compound)
                                            <br><small class="text-info"><i class="fas fa-flask me-1"></i>Racikan {{ $item->compound_name ?? 'Puyer' }}</small>
                                            @if($item->ingredients->isNotEmpty())
                                                <div class="small text-muted mt-1">
                                                    @foreach($item->ingredients as $ing)
                                                        <div class="ms-2">{{ $ing->medicine->name ?? '-' }} {{ $ing->qty_per_packet }}{{ $ing->unit }}</div>
                                                    @endforeach
                                                    @if($item->instruction)
                                                        <div class="ms-2 text-info">{{ $item->instruction }}</div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $item->quantity }} {{ $item->unit ?? $item->medicine->unit ?? 'pcs' }}</td>
                                    <td>{{ is_array($item->dosage) ? json_encode($item->dosage) : $item->dosage ?? '-' }}</td>
                                    <td>
                                        @if($item->is_compound)
                                            <small class="text-muted d-block">Bahan + tuslah + embalase</small>
                                        @endif
                                        {{ $item->subtotal ? 'Rp '.number_format($item->subtotal, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">Tidak ada item</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Dispense Modal --}}
<div class="modal fade" id="dispenseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('prescriptions.dispense', $prescription) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Proses Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin akan memproses resep <strong>{{ $prescription->prescription_number }}</strong>?</p>
                    <p class="text-muted small">Stok obat akan otomatis berkurang. Pastikan obat tersedia.</p>
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

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('prescriptions.cancel', $prescription) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Batalkan Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Yakin akan membatalkan resep <strong>{{ $prescription->prescription_number }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Alasan pembatalan</label>
                        <textarea name="cancellation_reason" class="form-control" rows="2" placeholder="Alasan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Ya, Batalkan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

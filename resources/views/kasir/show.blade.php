@extends('layouts.volt')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-file-invoice me-1"></i>Invoice: {{ $invoice->invoice_number }}</h4>
        <div class="d-flex gap-2">
            @if($invoice->status === 'paid')
            <a href="{{ route('kasir.print', $invoice) }}" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print me-1"></i>Cetak
            </a>
            @endif
            <a href="{{ route('kasir.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
    @include('components.alert')
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Informasi Invoice</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">No. Invoice</td><td><strong>{{ $invoice->invoice_number }}</strong></td></tr>
                        <tr><td class="text-muted">Tanggal</td><td>{{ $invoice->created_at->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-muted">Status</td>
                            <td>
                                @if($invoice->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($invoice->status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-secondary">Batal</span>
                                @endif
                            </td>
                        </tr>
                        <tr><td class="text-muted">Pasien</td><td>{{ $invoice->patient->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Poli</td><td>{{ $invoice->polyclinic->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Dokter</td><td>{{ $invoice->doctor->name ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Kasir</td><td>{{ $invoice->user->name ?? '-' }}</td></tr>
                        @if($invoice->paid_at)
                        <tr><td class="text-muted">Dibayar</td><td>{{ $invoice->paid_at->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-muted">Metode</td><td><span class="badge bg-info">{{ strtoupper($invoice->payment_method) }}</span></td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Item Tagihan</h6>
                    <span class="badge bg-primary fs-6">Total: Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr><th>Item</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</th>
                            </tr>
                            @if($invoice->status === 'paid')
                            <tr class="table-success">
                                <th colspan="3" class="text-end">Dibayar</th>
                                <th class="text-end">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</th>
                            </tr>
                            @if($invoice->change_amount > 0)
                            <tr class="table-info">
                                <th colspan="3" class="text-end">Kembalian</th>
                                <th class="text-end">Rp {{ number_format($invoice->change_amount, 0, ',', '.') }}</th>
                            </tr>
                            @endif
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>

            @if($invoice->status === 'pending')
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white"><h6 class="mb-0">Pembayaran</h6></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kasir.pay', $invoice) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="cash">Tunai</option>
                                    <option value="debit">Debit</option>
                                    <option value="credit">Kredit</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="bpjs">BPJS</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jumlah Dibayar <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="paid_amount" class="form-control" min="0" step="100"
                                        value="{{ old('paid_amount', $invoice->total_amount) }}" required
                                        oninput="document.getElementById('kembalian').textContent = 'Rp ' + (Math.max(0, parseInt(this.value) - {{ $invoice->total_amount }})).toLocaleString('id-ID')">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    Kembalian: <strong id="kembalian">Rp 0</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check-circle me-1"></i>Konfirmasi Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-3">
                <form method="POST" action="{{ route('kasir.destroy', $invoice) }}" onsubmit="return confirm('Batalkan invoice ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-times me-1"></i>Batalkan Invoice
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
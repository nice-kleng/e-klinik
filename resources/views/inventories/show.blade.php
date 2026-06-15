@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Stok Obat</h4>
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Stok</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Obat</td>
                            <td><strong>{{ $inventory->medicine->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kode Obat</td>
                            <td>{{ $inventory->medicine->code ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kategori</td>
                            <td>{{ $inventory->medicine->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No. Batch</td>
                            <td>{{ $inventory->batch_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Supplier</td>
                            <td>{{ $inventory->supplier->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jumlah Stok</td>
                            <td><strong>{{ $inventory->quantity }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Harga Beli</td>
                            <td>Rp {{ number_format($inventory->unit_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Harga Jual</td>
                            <td>Rp {{ number_format($inventory->selling_price, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Produksi</td>
                            <td>{{ $inventory->production_date?->format('d/m/Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal Kedaluwarsa</td>
                            <td>
                                {{ $inventory->expired_date?->format('d/m/Y') ?? '-' }}
                                @if($inventory->is_expired)
                                    <span class="badge bg-danger">Expired</span>
                                @endif
                            </td>
                        </tr>
                        @if($inventory->notes)
                            <tr>
                                <td class="text-muted">Catatan</td>
                                <td>{{ $inventory->notes }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Riwayat Transaksi</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Jumlah</th>
                                <th>Referensi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory->transactions as $tx)
                                <tr>
                                    <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($tx->type == 'in')
                                            <span class="badge bg-success">Masuk</span>
                                        @else
                                            <span class="badge bg-danger">Keluar</span>
                                        @endif
                                    </td>
                                    <td>{{ $tx->quantity }}</td>
                                    <td>{{ $tx->reference_type }} #{{ $tx->reference_id ?? '-' }}</td>
                                    <td>{{ $tx->notes ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Belum ada transaksi</td>
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

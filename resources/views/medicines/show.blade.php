@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Detail Obat</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('inventories.create', ['medicine_id' => $medicine->id]) }}" class="btn btn-primary">+ Tambah Stok</a>
            <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Informasi Obat</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width:140px">Kode</td>
                            <td><strong>{{ $medicine->code }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Obat</td>
                            <td>{{ $medicine->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nama Generik</td>
                            <td>{{ $medicine->generic_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kategori</td>
                            <td>{{ $medicine->category->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Produsen</td>
                            <td>{{ $medicine->manufacturer ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Satuan</td>
                            <td>{{ $medicine->unit }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Kandungan</td>
                            <td>{{ $medicine->content ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Generik</td>
                            <td>{{ $medicine->is_generic ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Perlu Resep</td>
                            <td>{{ $medicine->requires_prescription ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td>{{ $medicine->is_active ? 'Aktif' : 'Tidak Aktif' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Stok</td>
                            <td>
                                <strong class="fs-5 {{ $currentStock <= 0 ? 'text-danger' : ($currentStock <= 10 ? 'text-warning' : 'text-success') }}">
                                    {{ $currentStock }}
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($medicine->description)
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white"><h6 class="mb-0">Deskripsi</h6></div>
                    <div class="card-body"><p class="mb-0">{{ $medicine->description }}</p></div>
                </div>
            @endif
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Stok per Batch</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Supplier</th>
                                <th>Qty</th>
                                <th>Harga Beli</th>
                                <th>Harga Jual</th>
                                <th>Expired</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr class="{{ $batch->is_expired ? 'table-danger' : '' }}">
                                    <td>{{ $batch->batch_number ?? '-' }}</td>
                                    <td>{{ $batch->supplier->name ?? '-' }}</td>
                                    <td>{{ $batch->quantity }}</td>
                                    <td>Rp {{ number_format($batch->unit_price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($batch->selling_price, 0, ',', '.') }}</td>
                                    <td>
                                        {{ $batch->expired_date?->format('d/m/Y') }}
                                        @if($batch->is_expired)
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">Tidak ada stok</td>
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

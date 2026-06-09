@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Stok Obat</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('inventories.low-stock') }}" class="btn btn-warning">Stok Menipis</a>
            <a href="{{ route('inventories.expiring') }}" class="btn btn-info">Akan Expired</a>
            <a href="{{ route('inventories.expired') }}" class="btn btn-danger">Expired</a>
            <a href="{{ route('inventories.create') }}" class="btn btn-primary">+ Tambah Stok</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Obat</label>
                    <select name="medicine_id" class="form-select">
                        <option value="">Semua Obat</option>
                        @foreach($medicines as $med)
                            <option value="{{ $med->id }}" {{ request('medicine_id') == $med->id ? 'selected' : '' }}>{{ $med->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nama Obat</th>
                        <th>Batch</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Expired</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $inv)
                        @php $isExpired = $inv->expired_date && now()->gte($inv->expired_date); @endphp
                        <tr class="{{ $isExpired ? 'table-danger' : '' }}">
                            <td>{{ $inv->medicine->name ?? '-' }}</td>
                            <td>{{ $inv->batch_number ?? '-' }}</td>
                            <td>{{ $inv->supplier->name ?? '-' }}</td>
                            <td>{{ $inv->quantity }}</td>
                            <td>Rp {{ number_format($inv->unit_price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($inv->selling_price, 0, ',', '.') }}</td>
                            <td>
                                {{ $inv->expired_date?->format('d/m/Y') }}
                                @if($isExpired)
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($inv->expired_date && $inv->expired_date->diffInDays(now()) <= 30)
                                    <span class="badge bg-warning">Segera</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('inventories.show', $inv) }}" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada data stok</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $inventories->links() }}
        </div>
    </div>
</div>
@endsection

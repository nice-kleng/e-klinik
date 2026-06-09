@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Akan Kedaluwarsa</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Semua Stok</a>
            <a href="{{ route('inventories.low-stock') }}" class="btn btn-warning">Stok Menipis</a>
            <a href="{{ route('inventories.expired') }}" class="btn btn-danger">Expired</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Obat yang Akan Kedaluwarsa dalam {{ $days }} Hari</h6>
            <span class="badge bg-info">{{ $items->count() }} item</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Obat</th>
                        <th>Batch</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Tanggal Expired</th>
                        <th>Sisa Hari</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php $daysLeft = now()->diffInDays($item->expired_date, false); @endphp
                        <tr>
                            <td>{{ $item->medicine->name ?? '-' }}</td>
                            <td>{{ $item->batch_number ?? '-' }}</td>
                            <td>{{ $item->supplier->name ?? '-' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->expired_date?->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-warning">{{ (int)$daysLeft }} hari</span>
                            </td>
                            <td>
                                <a href="{{ route('inventories.show', $item) }}" class="btn btn-sm btn-info">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada obat yang akan kedaluwarsa</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

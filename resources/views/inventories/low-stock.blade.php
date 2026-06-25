@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Stok Menipis</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Semua Stok</a>
            <a href="{{ route('inventories.expiring') }}" class="btn btn-info">Akan Expired</a>
            <a href="{{ route('inventories.expired') }}" class="btn btn-danger">Expired</a>
        </div>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Obat dengan Stok di Bawah Minimum</h6>
            <span class="badge bg-warning">{{ $medicines->count() }} obat</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Obat</th>
                        <th>Satuan</th>
                        <th>Stok Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        <tr>
                            <td>{{ $medicine->code }}</td>
                            <td>{{ $medicine->name }}</td>
                            <td>{{ $medicine->unit }}</td>
                            <td>
                                <span class="badge bg-danger fs-6">{{ $medicine->current_stock ?? 0 }}</span>
                            </td>
                            <td>
                                <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('inventories.create', ['medicine_id' => $medicine->id]) }}" class="btn btn-sm btn-primary">Tambah Stok</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Semua stok aman</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

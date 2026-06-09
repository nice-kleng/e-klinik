@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Obat</h4>
        <a href="{{ route('medicines.create') }}" class="btn btn-primary">+ Tambah Obat</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Cari</label>
                    <input type="text" name="search" class="form-control" placeholder="Kode, nama, atau nama generik..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('medicines.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Obat</th>
                        <th>Nama Generik</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th width="160">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($medicines as $medicine)
                        <tr>
                            <td>{{ $medicine->code }}</td>
                            <td>{{ $medicine->name }}</td>
                            <td>{{ $medicine->generic_name ?? '-' }}</td>
                            <td>{{ $medicine->category->name ?? '-' }}</td>
                            <td>{{ $medicine->unit }}</td>
                            <td>
                                @if($medicine->current_stock <= 0)
                                    <span class="badge bg-danger">{{ $medicine->current_stock }}</span>
                                @elseif($medicine->current_stock <= 10)
                                    <span class="badge bg-warning">{{ $medicine->current_stock }}</span>
                                @else
                                    <span class="badge bg-success">{{ $medicine->current_stock }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('medicines.show', $medicine) }}" class="btn btn-sm btn-info">Detail</a>
                                <a href="{{ route('medicines.edit', $medicine) }}" class="btn btn-sm btn-warning">Edit</a>
                                <a href="{{ route('inventories.create', ['medicine_id' => $medicine->id]) }}" class="btn btn-sm btn-primary">+ Stok</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data obat</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $medicines->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.volt')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Batch Stok</h4>
        <div>
            <a href="{{ route('inventories.show', $inventory) }}" class="btn btn-outline-secondary">Kembali</a>
        </div>
    </div>
    @include('components.alert')
    <form method="POST" action="{{ route('inventories.update', $inventory) }}">
        @csrf @method('PUT')
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Obat <span class="text-danger">*</span></label>
                        <select name="medicine_id" class="form-select" required>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}" {{ old('medicine_id', $inventory->medicine_id) == $med->id ? 'selected' : '' }}>{{ $med->code }} - {{ $med->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id', $inventory->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. Batch</label>
                        <input type="text" name="batch_number" class="form-control" value="{{ old('batch_number', $inventory->batch_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $inventory->quantity) }}" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', $inventory->unit_price) }}" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $inventory->selling_price) }}" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Produksi</label>
                        <input type="date" name="production_date" class="form-control" value="{{ old('production_date', $inventory->production_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Kedaluwarsa</label>
                        <input type="date" name="expired_date" class="form-control" value="{{ old('expired_date', $inventory->expired_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $inventory->notes) }}</textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                <a href="{{ route('inventories.show', $inventory) }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
@extends('layouts.volt')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-clipboard-check me-1"></i>Stok Opname</h4>
        <a href="{{ route('inventories.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
    @include('components.alert')
    <form method="POST" action="{{ route('inventories.opname') }}">
        @csrf
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Masukkan stok fisik aktual untuk setiap obat</span>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Opname
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th>Stok Sistem</th>
                                <th>Stok Aktual</th>
                                <th>Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicines as $med)
                            <tr>
                                <td>{{ $med->code }}</td>
                                <td>{{ $med->name }}</td>
                                <td class="text-end system-stock" data-qty="{{ $med->current_stock }}">{{ $med->current_stock }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $loop->index }}][medicine_id]" value="{{ $med->id }}">
                                    <input type="number" name="items[{{ $loop->index }}][actual_qty]" class="form-control form-control-sm actual-qty" style="width:100px" value="{{ $med->current_stock }}" min="0">
                                </td>
                                <td class="diff-display text-center fw-bold">0</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada obat aktif</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Simpan Opname
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.actual-qty').forEach(function(input) {
    input.addEventListener('input', function() {
        var row = this.closest('tr');
        var system = parseInt(row.querySelector('.system-stock').dataset.qty) || 0;
        var actual = parseInt(this.value) || 0;
        var diff = actual - system;
        var diffCell = row.querySelector('.diff-display');
        diffCell.textContent = diff >= 0 ? '+' + diff : diff;
        diffCell.style.color = diff === 0 ? '#6c757d' : (diff > 0 ? '#198754' : '#dc3545');
    });
    input.dispatchEvent(new Event('input'));
});
</script>
@endpush
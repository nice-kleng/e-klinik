@extends('layouts.volt')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fas fa-cash-register me-1"></i>Kasir</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('kasir.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Buat Invoice
            </a>
        </div>
    </div>
    @include('components.alert')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                @if(request('status') === 'paid')
                    <span class="badge bg-success fs-6 me-2">Lunas</span>
                @elseif(request('status') === 'cancelled')
                    <span class="badge bg-secondary fs-6 me-2">Batal</span>
                @else
                    <span class="badge bg-warning fs-6 me-2">Pending ({{ $pendingCount }})</span>
                @endif
            </div>
            <form method="GET" class="d-flex gap-2 align-items-center">
                <select name="status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    <option value="">Pending</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Batal</option>
                </select>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="Dari">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="Sampai">
                <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Pasien</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Kasir</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td><strong>{{ $inv->invoice_number }}</strong></td>
                            <td>{{ $inv->patient->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($inv->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($inv->status === 'paid')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-secondary">Batal</span>
                                @endif
                            </td>
                            <td>{{ $inv->user->name ?? '-' }}</td>
                            <td>{{ $inv->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('kasir.show', $inv) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($inv->status === 'paid')
                                <a href="{{ route('kasir.print', $inv) }}" class="btn btn-sm btn-secondary" target="_blank">
                                    <i class="fas fa-print"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada invoice</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
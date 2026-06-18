@extends('layouts.volt')

@section('title', 'Satu Sehat - Location')

@section('header', 'Location (Poliklinik)')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Poliklinik</th>
                            <th>IHS ID (Satu Sehat)</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($polyclinics as $poli)
                            @php
                                $ref = $resourceIds->get($poli->id);
                            @endphp
                            <tr>
                                <td><code>{{ $poli->code }}</code></td>
                                <td>{{ $poli->name }}</td>
                                <td>
                                    @if ($ref && $ref->resource_id_ss)
                                        <code class="text-success">{{ $ref->resource_id_ss }}</code>
                                    @else
                                        <span class="text-muted fst-italic">Belum sync</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($ref && $ref->status === 'synced')
                                        <span class="badge bg-success">Tersync</span>
                                    @elseif ($ref && $ref->status === 'failed')
                                        <span class="badge bg-danger">Gagal</span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary sync-location" data-id="{{ $poli->id }}" data-name="{{ $poli->name }}">
                                        <i class="fas fa-sync me-1"></i> Sync
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data poliklinik</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.sync-location').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const row = this.closest('tr');
        const original = this.innerHTML;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sync...';

        fetch('{{ route('satusehat.sync-location', '') }}/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Gagal: ' + res.message);
                    this.disabled = false;
                    this.innerHTML = original;
                }
            })
            .catch(e => {
                alert('Error: ' + e.message);
                this.disabled = false;
                this.innerHTML = original;
            });
    });
});
</script>
@endpush

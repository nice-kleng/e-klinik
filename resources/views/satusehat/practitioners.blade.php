@extends('layouts.volt')

@section('title', 'Satu Sehat - Practitioner')

@section('header', 'Practitioner (Dokter)')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Poli</th>
                            <th>NIK</th>
                            <th>SIP</th>
                            <th>IHS ID</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            @php
                                $ref = $resourceIds->get($doctor->id);
                            @endphp
                            <tr>
                                <td><code>{{ $doctor->code }}</code></td>
                                <td>{{ $doctor->name }}</td>
                                <td>{{ $doctor->polyclinic?->name ?? '-' }}</td>
                                <td><code>{{ $doctor->user?->nik ?? '-' }}</code></td>
                                <td>{{ $doctor->sip_number ?? '-' }}</td>
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
                                    <button class="btn btn-sm btn-primary sync-practitioner" data-id="{{ $doctor->id }}" data-name="{{ $doctor->name }}">
                                        <i class="fas fa-sync me-1"></i> Sync
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data dokter</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <h6 class="mb-2">Catatan</h6>
            <ul class="mb-0 small text-muted">
                <li>Practitioner memerlukan NIK yang terdaftar di sistem Satu Sehat (KFA).</li>
                <li>Di lingkungan staging, pembuatan practitioner tidak diizinkan (403 Forbidden).</li>
                <li>Di production, pastikan tenaga kesehatan sudah terdaftar di KFA sebelum sync.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.sync-practitioner').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        const original = this.innerHTML;

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sync...';

        fetch('{{ route('satusehat.sync-practitioner', '') }}/' + id, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
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

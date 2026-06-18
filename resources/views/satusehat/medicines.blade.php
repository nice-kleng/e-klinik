@extends('layouts.volt')

@section('title', 'Satu Sehat - Obat & Alkes')

@section('header', 'Obat & Alkes (Mapping KFA)')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Obat</th>
                            <th>KFA Code</th>
                            <th>KFA Name</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicines as $medicine)
                            <tr>
                                <td><code>{{ $medicine->code }}</code></td>
                                <td>{{ $medicine->name }}</td>
                                <td>
                                    @if ($medicine->kfa_code)
                                        <code class="text-success">{{ $medicine->kfa_code }}</code>
                                    @else
                                        <span class="text-muted fst-italic">Belum mapping</span>
                                    @endif
                                </td>
                                <td>{{ $medicine->kfa_name ?? '-' }}</td>
                                <td>
                                    @if ($medicine->kfa_code)
                                        <span class="badge bg-success">Mapping</span>
                                    @else
                                        <span class="badge bg-secondary">Belum</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info search-kfa" data-id="{{ $medicine->id }}" data-name="{{ $medicine->name }}">
                                        <i class="fas fa-search me-1"></i> Cari KFA
                                    </button>
                                    @if ($medicine->kfa_code)
                                        <button class="btn btn-sm btn-outline-danger clear-kfa" data-id="{{ $medicine->id }}">
                                            <i class="fas fa-times me-1"></i> Hapus
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data obat</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- KFA Search Modal -->
<div class="modal fade" id="kfaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cari KFA Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Obat</label>
                    <input type="text" id="kfaSearchInput" class="form-control" placeholder="Ketik nama obat...">
                </div>
                <div id="kfaResults" class="list-group" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted py-3">Ketik minimal 2 karakter untuk mencari</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let selectedMedicineId = null;
let searchTimeout = null;

document.querySelectorAll('.search-kfa').forEach(btn => {
    btn.addEventListener('click', function() {
        selectedMedicineId = this.dataset.id;
        document.getElementById('kfaSearchInput').value = this.dataset.name;
        document.getElementById('kfaResults').innerHTML = '<div class="text-center text-muted py-3">Mencari...</div>';
        searchKfa(this.dataset.name);
        new bootstrap.Modal(document.getElementById('kfaModal')).show();
    });
});

document.querySelectorAll('.clear-kfa').forEach(btn => {
    btn.addEventListener('click', function() {
        if (!confirm('Hapus mapping KFA?')) return;
        const id = this.dataset.id;
        fetch('{{ route('satusehat.update-kfa', '') }}/' + id, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ kfa_code: '', kfa_name: '' })
        }).then(r => r.json()).then(res => {
            if (res.success) location.reload();
        });
    });
});

document.getElementById('kfaSearchInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (this.value.length >= 2) searchKfa(this.value);
    }, 400);
});

function searchKfa(keyword) {
    const container = document.getElementById('kfaResults');
    if (!container) return;

    fetch('{{ route('satusehat.search-kfa') }}?keyword=' + encodeURIComponent(keyword), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success || !res.data) {
            container.innerHTML = '<div class="text-center text-muted py-3">Tidak ada hasil</div>';
            return;
        }
        const entries = res.data.entry || [];
        if (entries.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-3">Tidak ada hasil</div>';
            return;
        }
        container.innerHTML = entries.map(e => {
            const r = e.resource || e;
            const code = r.code || '';
            const display = r.display || r.name || '';
            return `<button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center kfa-select"
                        data-code="${code}" data-name="${display}">
                        <div>
                            <strong>${display}</strong>
                            <br><small class="text-muted">Kode: ${code}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">Pilih</span>
                    </button>`;
        }).join('');

        container.querySelectorAll('.kfa-select').forEach(el => {
            el.addEventListener('click', function() {
                const code = this.dataset.code;
                const name = this.dataset.name;
                saveKfa(selectedMedicineId, code, name);
            });
        });
    })
    .catch(() => {
        container.innerHTML = '<div class="text-center text-danger py-3">Gagal mencari KFA</div>';
    });
}

function saveKfa(medicineId, code, name) {
    fetch('{{ route('satusehat.update-kfa', '') }}/' + medicineId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ kfa_code: code, kfa_name: name })
    }).then(r => r.json()).then(res => {
        if (res.success) {
            bootstrap.Modal.getInstance(document.getElementById('kfaModal')).hide();
            location.reload();
        } else {
            alert('Gagal: ' + res.message);
        }
    });
}
</script>
@endpush

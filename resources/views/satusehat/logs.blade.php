@extends('layouts.volt')

@section('title', 'Satu Sehat - Sync Log')

@section('header', 'Sync Log')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tipe</th>
                            <th>Aksi</th>
                            <th>Request</th>
                            <th>Response</th>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td><code>{{ $log->resource_type }}</code></td>
                                <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    @if ($log->request)
                                        <a href="#" class="text-muted small view-payload" data-payload='@json($log->request)'>Lihat</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    @if ($log->response)
                                        <a href="#" class="text-muted small view-payload" data-payload='@json($log->response)'>Lihat</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->status === 'success')
                                        <span class="badge bg-success">Sukses</span>
                                    @else
                                        <span class="badge bg-danger">Gagal</span>
                                    @endif
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $log->error_message ?? '-' }}
                                </td>
                                <td><small class="text-muted">{{ $log->created_at?->diffForHumans() }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">Belum ada log</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($logs->hasPages())
        <div class="mt-3">{{ $logs->links() }}</div>
    @endif
</div>

<!-- Payload Modal -->
<div class="modal fade" id="payloadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payload</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="payloadContent" class="mb-0" style="max-height: 500px; overflow-y: auto; font-size: 11px; background: #f8f9fa; padding: 12px; border-radius: 6px;"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.view-payload').forEach(el => {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        const payload = this.dataset.payload;
        try {
            const parsed = typeof payload === 'string' ? JSON.parse(payload) : payload;
            document.getElementById('payloadContent').textContent = JSON.stringify(parsed, null, 2);
        } catch {
            document.getElementById('payloadContent').textContent = payload;
        }
        new bootstrap.Modal(document.getElementById('payloadModal')).show();
    });
});
</script>
@endpush

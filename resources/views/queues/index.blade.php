@extends('layouts.volt')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Antrean</h4>
        <a href="{{ route('queues.display') }}" class="btn btn-info" target="_blank">Display Antrean</a>
    </div>

    @include('components.alert')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('superadmin'))
                <div class="col-md-3">
                    <label class="form-label">Poliklinik</label>
                    <select name="polyclinic_id" class="form-select">
                        <option value="">Semua Poli</option>
                        @foreach($polyclinics as $poly)
                            <option value="{{ $poly->id }}" {{ $polyclinicId == $poly->id ? 'selected' : '' }}>{{ $poly->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="waiting" {{ $status == 'waiting' ? 'selected' : '' }}>Menunggu</option>
                        <option value="called" {{ $status == 'called' ? 'selected' : '' }}>Dipanggil</option>
                        <option value="in_progress" {{ $status == 'in_progress' ? 'selected' : '' }}>Diproses</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('queues.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped mb-0" id="queueTable">
                <thead>
                    <tr>
                        <th>No. Antrean</th>
                        <th>Pasien</th>
                        <th>Poliklinik</th>
                        <th>Dokter</th>
                        <th>Sumber</th>
                        <th>Triage</th>
                        <th>Status</th>
                        <th width="320">Aksi</th>
                    </tr>
                </thead>
                <tbody id="queueTableBody">
                    @forelse($queues as $queue)
                        @php
                            $reg = $queue->registration;
                            $triage = $reg?->triage;
                            $isReceptionist = auth()->user()->hasRole('receptionist');
                            $canTriage = auth()->user()->hasAnyRole(['admin', 'receptionist', 'nurse']);
                        @endphp
                        <tr data-queue-id="{{ $queue->id }}" data-status="{{ $queue->status }}">
                            <td><strong>{{ $queue->queue_number }}</strong></td>
                            <td>{{ $reg?->patient?->name ?? '-' }}</td>
                            <td>{{ $queue->polyclinic?->name ?? '-' }}</td>
                            <td>{{ $reg?->doctor?->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $queue->source == 'mjkn' ? 'primary' : 'secondary' }}">
                                    {{ $queue->source }}
                                </span>
                            </td>
                            <td>
                                @if($triage)
                                    <span class="badge bg-success" title="Triage selesai">Sudah</span>
                                @else
                                    <span class="badge bg-danger" title="Belum triage">Belum</span>
                                    @if($canTriage && in_array($queue->status, ['waiting']))
                                        <a href="{{ route('triage.create', $reg) }}" class="btn btn-sm btn-outline-primary ms-1">
                                            <i class="fas fa-stethoscope"></i>
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusBadge = match($queue->status) {
                                        'waiting' => 'bg-warning',
                                        'called' => 'bg-info',
                                        'in_progress' => 'bg-primary',
                                        'completed' => 'bg-success',
                                        'cancelled' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    $statusLabel = match($queue->status) {
                                        'waiting' => 'Menunggu',
                                        'called' => 'Dipanggil',
                                        'in_progress' => 'Diproses',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                        default => $queue->status
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                @if(in_array($queue->status, ['waiting', 'called']))
                                    @if($queue->status == 'waiting')
                                        <button class="btn btn-sm btn-info btn-call" data-queue-id="{{ $queue->id }}" data-url="{{ route('queues.call-ajax', $queue) }}" {{ !$triage ? 'disabled' : '' }}>Panggil</button>
                                    @endif
                                    @unless($isReceptionist)
                                    <button class="btn btn-sm btn-primary btn-process" data-queue-id="{{ $queue->id }}" data-url="{{ route('queues.in-progress', $queue) }}">Proses</button>
                                    @endunless
                                @endif
                                @unless($isReceptionist)
                                @if(in_array($queue->status, ['waiting', 'called', 'in_progress']))
                                    <button class="btn btn-sm btn-success btn-complete" data-queue-id="{{ $queue->id }}" data-url="{{ route('queues.complete', $queue) }}">Selesai</button>
                                @endif
                                @endunless
                                @if(in_array($queue->status, ['waiting', 'called']))
                                    <button class="btn btn-sm btn-danger btn-cancel" data-queue-id="{{ $queue->id }}" data-url="{{ route('queues.cancel', $queue) }}">Batal</button>
                                @endif
                                @if($queue->status == 'in_progress')
                                    <a href="{{ route('medical-records.workspace', $queue) }}" class="btn btn-sm btn-warning">RME</a>
                                @endif
                                <a href="{{ route('queues.show', $queue) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRow">
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada antrean</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        <div class="card-footer bg-white" id="paginationFooter">
            {{ $queues->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let ttsQueue = [];

function getTtsVoice() {
    const voices = window.speechSynthesis.getVoices();
    return voices.find(v => v.lang.startsWith('id')) || voices.find(v => v.lang === 'id-ID') || voices[0] || null;
}

function speakText(text) {
    if (!('speechSynthesis' in window)) return;
    return new Promise(resolve => {
        ttsQueue.push(resolve);
        const utterance = new SpeechSynthesisUtterance(text);
        const voice = getTtsVoice();
        if (voice) utterance.voice = voice;
        utterance.lang = voice ? voice.lang : 'id-ID';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.onend = () => {
            const fn = ttsQueue.shift();
            if (fn) fn();
        };
        utterance.onerror = () => {
            const fn = ttsQueue.shift();
            if (fn) fn();
        };
        setTimeout(() => window.speechSynthesis.speak(utterance), 100);
    });
}

async function speakCall(queueNumber, polyclinic, patientName) {
    const text = `Nomor antrean ${queueNumber}, ${patientName}, silakan menuju ${polyclinic}`;
    for (let i = 0; i < 3; i++) {
        await speakText(text);
    }
}

if ('speechSynthesis' in window) {
    speechSynthesis.getVoices();
    speechSynthesis.addEventListener('voiceschanged', () => speechSynthesis.getVoices(), { once: true });
}

function refreshTable() {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newBody = doc.querySelector('#queueTableBody');
            if (newBody) {
                document.querySelector('#queueTableBody').innerHTML = newBody.innerHTML;
            }
            const newFooter = doc.querySelector('#paginationFooter');
            if (newFooter) {
                document.querySelector('#paginationFooter').innerHTML = newFooter.innerHTML;
            }
        })
        .catch(() => location.reload());
}

document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    document.querySelector('#queueTableBody').addEventListener('click', function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        if (btn.classList.contains('btn-call')) {
            e.preventDefault();
            const url = btn.dataset.url;
            btn.disabled = true;
            btn.innerHTML = 'Memanggil...';
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    refreshTable();
                    speakCall(res.data.queue.queue_number, res.data.queue.polyclinic_name, res.data.queue.patient_name);
                } else {
                    alert(res.message || 'Gagal memanggil antrean');
                }
            })
            .catch(() => alert('Gagal memanggil antrean'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = 'Panggil';
            });
        }

        if (btn.classList.contains('btn-process')) {
            e.preventDefault();
            const url = btn.dataset.url;
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(res => {
                if (res.success !== false) refreshTable();
                else alert(res.message || 'Gagal');
            })
            .catch(() => {})
            .finally(() => { btn.disabled = false; });
        }

        if (btn.classList.contains('btn-complete')) {
            e.preventDefault();
            const url = btn.dataset.url;
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(res => {
                if (res.success !== false) refreshTable();
                else alert(res.message || 'Gagal');
            })
            .catch(() => {})
            .finally(() => { btn.disabled = false; });
        }

        if (btn.classList.contains('btn-cancel')) {
            e.preventDefault();
            if (!confirm('Batalkan antrean ini?')) return;
            const url = btn.dataset.url;
            btn.disabled = true;
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(res => {
                if (res.success !== false) refreshTable();
                else alert(res.message || 'Gagal');
            })
            .catch(() => {})
            .finally(() => { btn.disabled = false; });
        }
    });
});

document.addEventListener('queue-updated', function (e) {
    const data = e.detail;
    if (data.action === 'called') {
        speakCall(data.queueNumber, data.polyclinicName || '', data.patientName || '');
    }
    refreshTable();
});
</script>
@endpush

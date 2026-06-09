@extends('layouts.display')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="current-queue-box">
            @php $firstCalled = $polyclinics->flatMap->queues->where('status', 'called')->first(); @endphp
            @if($firstCalled)
                <div class="label">SEKARANG DIPANGGIL</div>
                <div class="queue-number">{{ $firstCalled->queue_number }}</div>
                <div class="poly-name">{{ $firstCalled->polyclinic->name }}</div>
                <div class="patient-name">{{ $firstCalled->patient->name ?? '-' }}</div>
            @else
                <div class="label">ANTREAN</div>
                <div class="queue-number" style="font-size:3rem;">Menunggu</div>
                <div class="poly-name">Belum ada antrean dipanggil</div>
            @endif
        </div>

        <div class="row">
            @foreach($polyclinics as $poly)
                @php
                    $called = $poly->queues->where('status', 'called');
                    $inProgress = $poly->queues->where('status', 'in_progress');
                    $waiting = $poly->queues->where('status', 'waiting');
                @endphp
                <div class="col-md-6">
                    <div class="polyclinic-card">
                        <h4>{{ $poly->name }}</h4>
                        @if($inProgress->count())
                            <div class="called-item" style="border-left: 4px solid #0d6efd;">
                                <span><strong>Diproses:</strong> {{ $inProgress->first()->queue_number }} - {{ $inProgress->first()->patient->name ?? '' }}</span>
                                <span class="badge-called" style="background:#0d6efd;">Proses</span>
                            </div>
                        @endif
                        @foreach($called as $q)
                            <div class="called-item">
                                <span><strong>{{ $q->queue_number }}</strong> - {{ $q->patient->name ?? '' }}</span>
                                <span class="badge-called">Dipanggil</span>
                            </div>
                        @endforeach
                        <div class="mt-2">
                            <small class="text-secondary">Menunggu ({{ $waiting->count() }})</small>
                            @foreach($waiting as $q)
                                <div class="waiting-item">
                                    <span><span class="q-num">{{ $q->queue_number }}</span> - {{ $q->patient->name ?? '' }}</span>
                                    <span class="text-secondary">{{ $q->doctor->name ?? '-' }}</span>
                                </div>
                            @endforeach
                            @if($waiting->isEmpty())
                                <div class="text-secondary small mt-1">Tidak ada antrean</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="polyclinic-card">
            <h4>Ringkasan</h4>
            <div class="d-flex justify-content-between mb-2">
                <span>Total Antrean</span>
                <strong>{{ $polyclinics->sum(fn($p) => $p->queues->count()) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Dipanggil</span>
                <strong class="text-info">{{ $polyclinics->sum(fn($p) => $p->queues->where('status', 'called')->count()) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Diproses</span>
                <strong class="text-primary">{{ $polyclinics->sum(fn($p) => $p->queues->where('status', 'in_progress')->count()) }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Menunggu</span>
                <strong class="text-warning">{{ $polyclinics->sum(fn($p) => $p->queues->where('status', 'waiting')->count()) }}</strong>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-secondary">{{ now()->format('d/m/Y H:i:s') }}</small>
            <br>
            <small class="text-secondary">Halaman otomatis refresh setiap 5 detik</small>
        </div>
    </div>
</div>
@endsection

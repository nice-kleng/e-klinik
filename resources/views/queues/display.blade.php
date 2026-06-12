<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrean</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0a1628;
            color: #fff;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .header {
            padding: 20px 30px;
            background: linear-gradient(135deg, #1a2a4a, #0a1628);
            border-bottom: 3px solid #00d4ff;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: #00d4ff;
        }
        .poli-card {
            background: #1a2a4a;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .poli-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00d4ff;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .queue-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .queue-item {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 8px 16px;
            text-align: center;
            min-width: 100px;
        }
        .queue-item .number {
            font-size: 1.4rem;
            font-weight: 700;
        }
        .queue-item .name {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        .queue-item.waiting { border-left: 3px solid #ffc107; }
        .queue-item.called { border-left: 3px solid #0dcaf0; animation: pulse 1.5s infinite; }
        .queue-item.in_progress { border-left: 3px solid #0d6efd; }
        @keyframes pulse {
            0% { background: rgba(13,202,240,0.1); }
            50% { background: rgba(13,202,240,0.2); }
            100% { background: rgba(13,202,240,0.1); }
        }
        .now-playing {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #00d4ff, #007bff);
            color: #000;
            padding: 20px 30px;
            text-align: center;
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: 4px;
        }
        .clock {
            font-size: 1.2rem;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div class="header d-flex justify-content-between align-items-center">
        <h1>ANTREAN POLIKLINIK</h1>
        <div class="clock" id="clock"></div>
    </div>

    <div class="container-fluid p-4" style="padding-bottom: 100px;">
        <div class="row">
            @forelse($polyclinics as $poli)
                <div class="col-md-6 col-lg-4">
                    <div class="poli-card">
                        <div class="poli-title">{{ $poli->name }}</div>
                        <div class="queue-list">
                            @forelse($poli->queues as $q)
                                @php $reg = $q->registration; @endphp
                                <div class="queue-item {{ $q->status }}">
                                    <div class="number">{{ $q->queue_number }}</div>
                                    <div class="name">{{ $reg?->patient?->name ?? '-' }}</div>
                                </div>
                            @empty
                                <div class="text-muted small">Tidak ada antrean</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Tidak ada poli aktif</h4>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
        setTimeout(function() { location.reload(); }, 30000);
    </script>
</body>
</html>

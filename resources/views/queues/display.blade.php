<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Poli — Display Antrean</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(0, 212, 255, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 90%, rgba(123, 31, 162, 0.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        h1 {
            position: relative;
            z-index: 1;
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 4px;
            margin-bottom: 3rem;
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .poli-grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            max-width: 900px;
            width: 100%;
            padding: 0 20px;
        }
        .poli-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 32px 20px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .poli-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.03), rgba(124, 58, 237, 0.03));
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .poli-card:hover {
            transform: translateY(-6px);
            border-color: rgba(0, 212, 255, 0.25);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            color: #fff;
        }
        .poli-card:hover::before {
            opacity: 1;
        }
        .poli-card .icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }
        .poli-card .name {
            font-size: 1.3rem;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        .poli-card .code {
            position: absolute;
            top: 12px;
            right: 16px;
            font-size: 0.7rem;
            opacity: 0.2;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            z-index: 1;
        }
        .badge-waiting {
            display: inline-block;
            margin-top: 10px;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        .badge-waiting.has-waiting {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.2);
        }
        .badge-waiting.empty {
            background: rgba(255, 255, 255, 0.04);
            color: rgba(255, 255, 255, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body>
    <h1>PILIH POLI</h1>
    <div class="poli-grid">
        @forelse($polyclinics as $poli)
            <a href="{{ route('queues.display-tv', $poli) }}" class="poli-card" target="_blank">
                <span class="code">{{ $poli->code }}</span>
                <div class="icon">🏥</div>
                <div class="name">{{ $poli->name }}</div>
                @if($poli->waiting_count > 0)
                    <span class="badge-waiting has-waiting">Menunggu {{ $poli->waiting_count }}</span>
                @else
                    <span class="badge-waiting empty">Kosong</span>
                @endif
            </a>
        @empty
            <div class="col-12 text-center text-muted py-5">
                <h4>Tidak ada poli aktif</h4>
            </div>
        @endforelse
    </div>
</body>
</html>

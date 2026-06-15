<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $polyclinic->name }} — Display Antrean</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            color: #fff;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(0, 212, 255, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 90%, rgba(123, 31, 162, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse 50% 60% at 10% 80%, rgba(0, 123, 255, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── HEADER ── */
        .header {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            flex-shrink: 0;
        }
        .header .app-name {
            font-size: 0.85rem;
            opacity: 0.35;
            letter-spacing: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .header .poli-name {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 1px;
        }
        .header .clock {
            font-size: 1.1rem;
            opacity: 0.5;
            font-variant-numeric: tabular-nums;
            letter-spacing: 1px;
        }

        /* ── BODY ── */
        .body-content {
            position: relative;
            z-index: 1;
            flex: 1;
            display: flex;
            gap: 28px;
            padding: 28px 40px;
            min-height: 0;
        }

        /* ── LEFT ── */
        .left-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 24px;
            min-width: 0;
        }

        .card-current {
            flex: 1;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.12), rgba(124, 58, 237, 0.12));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 240px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s ease;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .card-current::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 212, 255, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .card-current .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 4px;
            opacity: 0.5;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        .card-current .queue-number {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: 6px;
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card-current .patient-name {
            font-size: 1.8rem;
            font-weight: 600;
            margin-top: 4px;
            opacity: 0.92;
            position: relative;
            z-index: 1;
        }
        .card-current .doctor-name {
            font-size: 0.95rem;
            opacity: 0.5;
            margin-top: 2px;
            position: relative;
            z-index: 1;
        }
        .card-current .empty-text {
            font-size: 2rem;
            font-weight: 600;
            opacity: 0.3;
            position: relative;
            z-index: 1;
        }

        /* ── CALLED ── */
        .card-called {
            background: rgba(13, 202, 240, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(13, 202, 240, 0.2);
            border-radius: 18px;
            padding: 20px 36px;
            flex-shrink: 0;
            animation: called-pulse 2s ease-in-out infinite;
            transition: all 0.4s ease;
        }
        @keyframes called-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(13, 202, 240, 0.08), inset 0 0 20px rgba(13, 202, 240, 0.02); border-color: rgba(13, 202, 240, 0.2); }
            50% { box-shadow: 0 0 40px rgba(13, 202, 240, 0.2), inset 0 0 30px rgba(13, 202, 240, 0.05); border-color: rgba(13, 202, 240, 0.4); }
        }
        .card-called .label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            opacity: 0.5;
            font-weight: 600;
        }
        .card-called .queue-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0dcaf0;
        }
        .card-called .patient-name {
            font-size: 1.1rem;
            opacity: 0.75;
        }

        /* ── RIGHT ── */
        .right-section {
            width: 360px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
        }
        .card-waiting {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 20px;
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .card-waiting .header-waiting {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 14px;
            margin-bottom: 14px;
            flex-shrink: 0;
        }
        .card-waiting .header-waiting .title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #fbbf24;
        }
        .card-waiting .header-waiting .count {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border-radius: 20px;
            padding: 2px 14px;
            font-weight: 700;
            font-size: 0.85rem;
            border: 1px solid rgba(251, 191, 36, 0.2);
        }
        .waiting-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .waiting-list::-webkit-scrollbar { width: 4px; }
        .waiting-list::-webkit-scrollbar-track { background: transparent; }
        .waiting-list::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.1); border-radius: 4px; }

        .waiting-item {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 3px solid rgba(251, 191, 36, 0.3);
            animation: slide-in 0.35s ease-out;
            transition: all 0.2s ease;
        }
        .waiting-item:hover {
            background: rgba(255, 255, 255, 0.06);
        }
        @keyframes slide-in {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .waiting-item .number {
            font-size: 1.1rem;
            font-weight: 700;
            min-width: 80px;
        }
        .waiting-item .name {
            font-size: 0.9rem;
            opacity: 0.7;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .waiting-item .index {
            font-size: 0.75rem;
            opacity: 0.3;
            min-width: 24px;
            text-align: right;
            font-weight: 600;
        }
        .waiting-empty {
            text-align: center;
            padding: 40px 0;
            opacity: 0.25;
            font-size: 0.95rem;
        }

        /* ── UPDATE BANNER ── */
        .update-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 212, 255, 0.12);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-top: 1px solid rgba(0, 212, 255, 0.15);
            color: #fff;
            padding: 20px 40px;
            text-align: center;
            font-weight: 800;
            font-size: 2rem;
            letter-spacing: 4px;
            transform: translateY(100%);
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 100;
        }
        .update-banner.show {
            transform: translateY(0);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .body-content { flex-direction: column; padding: 16px; }
            .right-section { width: 100%; }
            .card-current .queue-number { font-size: 2.5rem; }
            .header { padding: 12px 16px; }
            .header .poli-name { font-size: 1.2rem; }
            .card-current { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <span class="app-name">e-KLINIK</span>
        <span class="poli-name">{{ $polyclinic->name }}</span>
        <span class="clock" id="clock"></span>
    </div>

    <div class="body-content">
        {{-- LEFT --}}
        <div class="left-section">
            <div class="card-current" id="cardCurrent">
                <div class="label">Sedang Dilayani</div>
                <div class="queue-number" id="currentNumber" @if(!$current)style="display:none"@endif>{{ $current->queue_number ?? '' }}</div>
                <div class="patient-name" id="currentName" @if(!$current)style="display:none"@endif>{{ $current?->registration?->patient?->name ?? '-' }}</div>
                <div class="doctor-name" id="currentDoctor" @if(!$current)style="display:none"@endif>{{ $current?->registration?->doctor?->name ?? '' }}</div>
                <div class="empty-text" id="currentEmpty" @if($current)style="display:none"@endif>—</div>
                <div class="doctor-name" id="currentDoctorEmpty" @if($current)style="display:none"@endif>
                    {{ $called?->registration?->doctor?->name ?? $waiting->first()?->registration?->doctor?->name ?? '' }}
                </div>
            </div>

            <div class="card-called" id="cardCalled" @if(!$called)style="display:none"@endif>
                <div class="label">Dipanggil</div>
                <div class="queue-number" id="calledNumber" @if(!$called)style="display:none"@endif>{{ $called->queue_number ?? '' }}</div>
                <div class="patient-name" id="calledName" @if(!$called)style="display:none"@endif>{{ $called?->registration?->patient?->name ?? '-' }}</div>
                <div class="patient-name" id="calledEmpty" @if($called)style="display:none"@endif style="opacity:0.4">—</div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="right-section">
            <div class="card-waiting">
                <div class="header-waiting">
                    <span class="title">Menunggu</span>
                    <span class="count" id="waitingCount">{{ $waiting->count() }}</span>
                </div>
                <div class="waiting-list" id="waitingList">
                    @forelse($waiting as $i => $w)
                        <div class="waiting-item" data-queue-id="{{ $w->id }}">
                            <span class="index">{{ $i + 1 }}</span>
                            <span class="number">{{ $w->queue_number }}</span>
                            <span class="name">{{ $w->registration?->patient?->name ?? '-' }}</span>
                        </div>
                    @empty
                        <div class="waiting-empty">Tidak ada antrean</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- UPDATE BANNER --}}
    <div class="update-banner" id="updateBanner"></div>

    <script>
        let ttsQueue = [];

        function getTtsVoice() {
            const voices = window.speechSynthesis.getVoices();
            return voices.find(v => v.lang.startsWith('id')) || voices.find(v => v.lang === 'id-ID') || voices[0] || null;
        }

        function speakText(text) {
            if (!('speechSynthesis' in window)) return new Promise(r => r());
            return new Promise(resolve => {
                ttsQueue.push(resolve);
                const u = new SpeechSynthesisUtterance(text);
                const voice = getTtsVoice();
                if (voice) u.voice = voice;
                u.lang = voice ? voice.lang : 'id-ID';
                u.rate = 0.9;
                u.onend = () => { const fn = ttsQueue.shift(); if (fn) fn(); };
                u.onerror = () => { const fn = ttsQueue.shift(); if (fn) fn(); };
                setTimeout(() => window.speechSynthesis.speak(u), 100);
            });
        }

        async function speakCall(queueNumber, patientName, polyclinicName) {
            const text = `Nomor antrean ${queueNumber}, ${patientName}, silakan menuju ${polyclinicName}`;
            for (let i = 0; i < 3; i++) {
                await speakText(text);
            }
        }

        if ('speechSynthesis' in window) {
            speechSynthesis.getVoices();
            speechSynthesis.addEventListener('voiceschanged', () => speechSynthesis.getVoices(), { once: true });
        }

        function showBanner(text) {
            const banner = document.getElementById('updateBanner');
            banner.textContent = text;
            banner.classList.add('show');
            setTimeout(() => banner.classList.remove('show'), 6000);
        }

        function rerender() {
            fetch('{{ route('queues.display-json-poly', $polyclinic) }}')
                .then(r => r.json())
                .then(d => {
                    const cc = document.getElementById('cardCurrent');
                    const cn = document.getElementById('currentNumber');
                    const cnm = document.getElementById('currentName');
                    const cdr = document.getElementById('currentDoctor');
                    const cempty = document.getElementById('currentEmpty');
                    const cdrempty = document.getElementById('currentDoctorEmpty');
                    if (d.current) {
                        cn.textContent = d.current.queue_number;
                        cnm.textContent = d.current.patient_name;
                        cdr.textContent = d.doctor_name !== '-' ? d.doctor_name : '';
                        cn.style.display = ''; cnm.style.display = ''; cdr.style.display = '';
                        if (cempty) cempty.style.display = 'none';
                        if (cdrempty) cdrempty.style.display = 'none';
                    } else {
                        if (cempty) { cempty.style.display = ''; cempty.textContent = '—'; }
                        if (cdrempty) cdrempty.style.display = '';
                        cn.style.display = 'none'; cnm.style.display = 'none'; cdr.style.display = 'none';
                    }

                    const ccalled = document.getElementById('cardCalled');
                    const cnum = document.getElementById('calledNumber');
                    const cn2 = document.getElementById('calledName');
                    const cempty2 = document.getElementById('calledEmpty');
                    if (d.called) {
                        cnum.textContent = d.called.queue_number;
                        cn2.textContent = d.called.patient_name;
                        cnum.style.display = ''; cn2.style.display = '';
                        ccalled.style.display = '';
                        if (cempty2) cempty2.style.display = 'none';
                    } else {
                        ccalled.style.display = 'none';
                        if (cempty2) cempty2.style.display = '';
                    }

                    const wl = document.getElementById('waitingList');
                    const wc = document.getElementById('waitingCount');
                    wc.textContent = d.waiting_count;
                    if (d.waiting.length === 0) {
                        wl.innerHTML = '<div class="waiting-empty">Tidak ada antrean</div>';
                    } else {
                        wl.innerHTML = d.waiting.map((q, i) =>
                            `<div class="waiting-item" data-queue-id="${q.id}">
                                <span class="index">${i + 1}</span>
                                <span class="number">${q.queue_number}</span>
                                <span class="name">${q.patient_name}</span>
                            </div>`
                        ).join('');
                    }
                })
                .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (window.Echo) {
                window.Echo.channel('queue')
                    .listen('QueueUpdated', (e) => {
                        if (String(e.polyclinicId) !== '{{ $polyclinic->id }}') return;
                        if (e.action === 'called') {
                            showBanner(`Nomor ${e.queueNumber} — ${e.patientName ?? ''}`);
                            setTimeout(() => speakCall(e.queueNumber, e.patientName ?? '', e.polyclinicName || '{{ $polyclinic->name }}'), 300);
                        }
                        setTimeout(rerender, 200);
                    });
            }
        });

        setInterval(rerender, 5000);
        setTimeout(rerender, 1000);

        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>

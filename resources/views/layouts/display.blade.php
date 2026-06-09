<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="5">
    <title>Display Antrean - {{ config('app.name', 'e-Klinik') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: #1a1a2e;
            color: #fff;
        }
        .display-container {
            min-height: 100vh;
            padding: 2rem;
        }
        .current-queue-box {
            background: linear-gradient(135deg, #16213e, #0f3460);
            border: 3px solid #e94560;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .current-queue-box .label {
            font-size: 1.5rem;
            opacity: 0.8;
        }
        .current-queue-box .queue-number {
            font-size: 6rem;
            font-weight: 800;
            color: #e94560;
            line-height: 1.1;
        }
        .current-queue-box .poly-name {
            font-size: 1.8rem;
            margin-top: 0.5rem;
        }
        .current-queue-box .patient-name {
            font-size: 1.4rem;
            opacity: 0.9;
        }
        .polyclinic-card {
            background: #16213e;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .polyclinic-card h4 {
            color: #e94560;
            border-bottom: 2px solid #0f3460;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }
        .called-item {
            background: #0f3460;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .called-item .badge-called {
            background: #e94560;
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .waiting-item {
            display: flex;
            justify-content: space-between;
            padding: 0.4rem 0.75rem;
            margin-bottom: 0.25rem;
            border-bottom: 1px solid #0f3460;
            font-size: 0.95rem;
        }
        .waiting-item .q-num {
            color: #e94560;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="display-container">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>

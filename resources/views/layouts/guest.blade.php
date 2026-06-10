<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'e-Klinik') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow" style="width: 100%; max-width: 450px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h4 class="mb-1">{{ config('app.name', 'e-Klinik') }}</h4>
                    <small class="text-muted">Sistem Informasi Manajemen Klinik</small>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>

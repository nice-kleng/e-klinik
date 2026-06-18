<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'e-Klinik'))</title>

    <link rel="icon" href="{{ asset('vendor/volt/assets/img/favicon/favicon-32x32.png') }}">

    <!-- Volt CSS -->
    <link type="text/css" href="{{ asset('vendor/volt/css/volt.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Select2 (non-module, strict-safe) -->
    <script defer src="{{ asset('vendor/select2/select2.min.js') }}"></script>

    @stack('styles')
</head>
<body>
    <!-- Mobile navbar -->
    <nav class="navbar navbar-dark navbar-theme-primary px-4 col-12 d-lg-none">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img class="navbar-brand-dark" src="{{ asset('vendor/volt/assets/img/brand/light.svg') }}" alt="e-Klinik" height="24">
            <img class="navbar-brand-light" src="{{ asset('vendor/volt/assets/img/brand/dark.svg') }}" alt="e-Klinik" height="24">
        </a>
        <div class="d-flex align-items-center">
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
        <div class="sidebar-inner px-4 pt-3">
            <div class="d-none d-md-block text-center pt-2 pb-3">
                <a href="{{ route('dashboard') }}">
                    <img class="navbar-brand-dark" src="{{ asset('vendor/volt/assets/img/brand/light.svg') }}" alt="e-Klinik" height="30">
                </a>
            </div>
            <div class="user-card d-flex d-md-none align-items-center justify-content-between justify-content-md-center pb-4">
                <div class="d-flex align-items-center">
                    <div class="d-block">
                        <h2 class="h5 mb-3">Hi, {{ Auth::user()->name }}</h2>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                                <i class="fas fa-right-from-bracket me-1"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="collapse-close d-md-none">
                    <a href="#sidebarMenu" data-bs-toggle="collapse">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>
            </div>

            @include('layouts.navigation')
        </div>
    </nav>

    <!-- Main content -->
    <main class="content">
        <nav class="navbar navbar-top navbar-expand navbar-dashboard navbar-light bg-white border-bottom border-3 border-primary ps-0 pe-2 pb-0">
            <div class="container-fluid px-0">
                <div class="d-flex justify-content-between w-100" id="navbarSupportedContent">
                    <div class="d-flex align-items-center">
                        <h5 class="text-gray-800 mb-0 ms-1">@yield('header', 'Dashboard')</h5>
                    </div>
                    <ul class="navbar-nav align-items-center">
                        <li class="nav-item dropdown ms-auto">
                            <a class="nav-link text-dark notification-bell dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                <i class="fas fa-user me-2"></i>
                                {{ Auth::user()->name }}
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item fw-bold" type="submit">
                                        <i class="fas fa-right-from-bracket me-2"></i>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="py-4">
            @include('components.alert')
            @yield('content')
        </div>
    </main>

    <!-- Core Vendor JS -->
    <script src="{{ asset('vendor/volt/vendor/onscreen/dist/on-screen.umd.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/vendor/vanillajs-datepicker/dist/js/datepicker.min.js') }}"></script>

    <script src="{{ asset('vendor/volt/js/volt.js') }}"></script>

    <script>
        window.flash = {
            success: @json(session('success')),
            error: @json(session('error')),
            warning: @json(session('warning')),
            info: @json(session('info')),
        };
    </script>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'e-Klinik'))</title>

    <link rel="icon" href="{{ asset('vendor/volt/assets/img/favicon/favicon-32x32.png') }}">

    <link type="text/css" href="{{ asset('vendor/volt/css/volt.css') }}" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <main>
        <section class="vh-lg-100 mt-5 mt-lg-0 bg-soft d-flex align-items-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="bg-white shadow border-0 rounded border-light p-4 p-lg-5 w-100 fmxw-500">
                            <div class="text-center text-md-center mb-4 mt-md-0">
                                <h1 class="mb-0 h3">@yield('header', 'e-Klinik')</h1>
                            </div>
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('vendor/volt/vendor/smooth-scroll/dist/smooth-scroll.polyfills.min.js') }}"></script>
    <script src="{{ asset('vendor/volt/js/volt.js') }}"></script>
    @stack('scripts')
</body>
</html>

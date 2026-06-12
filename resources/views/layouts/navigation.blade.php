@php
    $user = Auth::user();
    $role = $user?->getRoleNames()->first();
@endphp
<div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark" id="sidebar" style="width: 260px;">
    <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <span class="fs-5 fw-semibold">e-Klinik</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>
        </li>

        @role('admin|receptionist')
        <li>
            <a href="{{ route('registration.index') }}" class="nav-link text-white {{ request()->routeIs('registration.*') ? 'active' : '' }}">
                Pendaftaran
            </a>
        </li>
        @endrole

        @role('admin|receptionist|doctor')
        <li class="nav-item mt-2">
            <small class="text-secondary text-uppercase px-2">Master Data</small>
        </li>
        <li>
            <a href="{{ route('patients.index') }}" class="nav-link text-white {{ request()->routeIs('patients.*') ? 'active' : '' }}">
                Pasien
            </a>
        </li>
        @endrole

        @role('admin')
        <li>
            <a href="{{ route('polyclinics.index') }}" class="nav-link text-white {{ request()->routeIs('polyclinics.*') ? 'active' : '' }}">
                Poliklinik
            </a>
        </li>
        <li>
            <a href="{{ route('doctors.index') }}" class="nav-link text-white {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
                Dokter
            </a>
        </li>
        @endrole

        @role('admin|doctor')
        <li>
            <a href="{{ route('diagnoses.index') }}" class="nav-link text-white {{ request()->routeIs('diagnoses.*') ? 'active' : '' }}">
                ICD-10 Diagnosa
            </a>
        </li>
        @endrole

        {{-- Layanan --}}
        @role('admin|receptionist|doctor')
        <li class="nav-item mt-2">
            <small class="text-secondary text-uppercase px-2">Layanan</small>
        </li>
        <li>
            <a href="{{ route('queues.index') }}" class="nav-link text-white {{ request()->routeIs('queues.*') ? 'active' : '' }}">
                Antrean
            </a>
        </li>
        @endrole

        <li>
            <a href="{{ route('queues.display') }}" class="nav-link text-white {{ request()->routeIs('queues.display*') ? 'active' : '' }}">
                Display Antrean
            </a>
        </li>

        @role('admin|doctor')
        <li>
            <a href="{{ route('medical-records.index') }}" class="nav-link text-white {{ request()->routeIs('medical-records.*') ? 'active' : '' }}">
                Rekam Medis
            </a>
        </li>
        <li>
            <a href="{{ route('prescriptions.index') }}" class="nav-link text-white {{ request()->routeIs('prescriptions.*') ? 'active' : '' }}">
                Resep
            </a>
        </li>
        @endrole

        @role('admin|receptionist|doctor')
        <li class="nav-item mt-2">
            <small class="text-secondary text-uppercase px-2">BPJS</small>
        </li>
        <li>
            <a href="{{ route('bpjs-seps.index') }}" class="nav-link text-white {{ request()->routeIs('bpjs-seps.*') ? 'active' : '' }}">
                SEP BPJS
            </a>
        </li>
        @endrole

        @role('admin|doctor|laborant')
        <li class="nav-item mt-2">
            <small class="text-secondary text-uppercase px-2">Laboratorium</small>
        </li>
        <li>
            <a href="{{ route('lab-requests.index') }}" class="nav-link text-white {{ request()->routeIs('lab-requests.*') ? 'active' : '' }}">
                Permintaan Lab
            </a>
        </li>
        <li>
            <a href="{{ route('lab-results.index') }}" class="nav-link text-white {{ request()->routeIs('lab-results.*') ? 'active' : '' }}">
                Hasil Lab
            </a>
        </li>
        <li>
            <a href="{{ route('lab-tests.index') }}" class="nav-link text-white {{ request()->routeIs('lab-tests.*') ? 'active' : '' }}">
                Master Tes Lab
            </a>
        </li>
        <li>
            <a href="{{ route('lab-test-categories.index') }}" class="nav-link text-white {{ request()->routeIs('lab-test-categories.*') ? 'active' : '' }}">
                Kategori Tes
            </a>
        </li>
        @endrole

        @role('admin|pharmacist')
        <li class="nav-item mt-2">
            <small class="text-secondary text-uppercase px-2">Farmasi & Inventaris</small>
        </li>
        <li>
            <a href="{{ route('medicines.index') }}" class="nav-link text-white {{ request()->routeIs('medicines.*') ? 'active' : '' }}">
                Obat
            </a>
        </li>
        <li>
            <a href="{{ route('inventories.index') }}" class="nav-link text-white {{ request()->routeIs('inventories.*') ? 'active' : '' }}">
                Stok Obat
            </a>
        </li>
        <li>
            <a href="{{ route('inventories.expired') }}" class="nav-link text-white {{ request()->routeIs('inventories.*') ? 'active' : '' }}">
                Laporan
            </a>
        </li>
        @endrole
    </ul>
    <hr>
    <div class="text-white small px-2">
        {{ $user->name }}<br>
        <span class="text-secondary">{{ $role ?? 'User' }}</span>
    </div>
</div>

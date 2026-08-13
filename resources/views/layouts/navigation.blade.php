@php
    $user = Auth::user();
    $role = $user?->getRoleNames()->first();
@endphp

<ul class="nav flex-column pt-3 pt-md-0">
    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a href="{{ route('dashboard') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-chart-pie fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Dashboard</span>
        </a>
    </li>

    @role('admin|receptionist')
    <li class="nav-item {{ request()->routeIs('registration.*') ? 'active' : '' }}">
        <a href="{{ route('registration.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-calendar-check fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Pendaftaran</span>
        </a>
    </li>
    @endrole

    @role('admin|receptionist|doctor')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Master Data</span>
    </li>
    <li class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}">
        <a href="{{ route('patients.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-users fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Pasien</span>
        </a>
    </li>
    @endrole

    @role('admin')
    <li class="nav-item {{ request()->routeIs('polyclinics.*') ? 'active' : '' }}">
        <a href="{{ route('polyclinics.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-hospital fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Poliklinik</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('doctors.*') ? 'active' : '' }}">
        <a href="{{ route('doctors.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-user-md fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Dokter</span>
        </a>
    </li>
    @endrole

    @role('admin|doctor')
    <li class="nav-item {{ request()->routeIs('diagnoses.*') ? 'active' : '' }}">
        <a href="{{ route('diagnoses.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-stethoscope fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">ICD-10 Diagnosa</span>
        </a>
    </li>
    @endrole

    @role('admin|receptionist|doctor|nurse')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Layanan</span>
    </li>
    <li class="nav-item {{ request()->routeIs('queues.index') ? 'active' : '' }}">
        <a href="{{ route('queues.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-list fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Antrean</span>
        </a>
    </li>
    @endrole

    <li class="nav-item {{ request()->routeIs('queues.display*') ? 'active' : '' }}">
        <a href="{{ route('queues.display') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-tv fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Display Antrean</span>
        </a>
    </li>

    @role('admin|doctor')
    <li class="nav-item {{ request()->routeIs('medical-records.*') ? 'active' : '' }}">
        <a href="{{ route('medical-records.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-file-alt fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Rekam Medis</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('prescriptions.*') ? 'active' : '' }}">
        <a href="{{ route('prescriptions.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-prescription fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Resep</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('audit-trail.*') ? 'active' : '' }}">
        <a href="{{ route('audit-trail.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-clipboard-list fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Audit Trail</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('informed-consents.*') ? 'active' : '' }}">
        <a href="{{ route('informed-consents.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-file-signature fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Informed Consent</span>
        </a>
    </li>
    @endrole

    @role('admin|receptionist|doctor')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">BPJS</span>
    </li>
    <li class="nav-item {{ request()->routeIs('bpjs-seps.*') ? 'active' : '' }}">
        <a href="{{ route('bpjs-seps.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-id-card fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">SEP BPJS</span>
        </a>
    </li>
    @endrole

    @role('admin|doctor|laborant')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Laboratorium</span>
    </li>
    <li class="nav-item {{ request()->routeIs('lab-requests.*') ? 'active' : '' }}">
        <a href="{{ route('lab-requests.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-flask fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Permintaan Lab</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('lab-queues.*') ? 'active' : '' }}">
        <a href="{{ route('lab-queues.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-list-ol fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Antrian Lab</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('lab-results.*') ? 'active' : '' }}">
        <a href="{{ route('lab-results.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-vial fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Hasil Lab</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('lab-tests.*') ? 'active' : '' }}">
        <a href="{{ route('lab-tests.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-microscope fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Master Tes Lab</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('lab-test-categories.*') ? 'active' : '' }}">
        <a href="{{ route('lab-test-categories.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-tags fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Kategori Tes</span>
        </a>
    </li>
    @endrole

    @role('admin|pharmacist')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Farmasi</span>
    </li>
    <li class="nav-item {{ request()->routeIs('pharmacy-queues.*') ? 'active' : '' }}">
        <a href="{{ route('pharmacy-queues.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-list-ol fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Antrian Farmasi</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('prescriptions.pending*') ? 'active' : '' }}">
        <a href="{{ route('prescriptions.pending') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-clock fa-fw me-2"></i>
            </span>
            <span class="sidebar-text d-flex justify-content-between w-100">
                Resep Masuk
                @php $pendingCount = \App\Models\Prescription::where('status', 'active')->count(); @endphp
                @if($pendingCount > 0)
                    <span class="badge bg-danger rounded-pill ms-1">{{ $pendingCount }}</span>
                @endif
            </span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('prescriptions.*') && !request()->routeIs('prescriptions.pending*') ? 'active' : '' }}">
        <a href="{{ route('prescriptions.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-prescription fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Resep</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('medicines.*') ? 'active' : '' }}">
        <a href="{{ route('medicines.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-pills fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Obat</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('inventories.*') ? 'active' : '' }}">
        <a href="{{ route('inventories.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-warehouse fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Stok Obat</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('inventories.opname*') ? 'active' : '' }}">
        <a href="{{ route('inventories.opname') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-clipboard-check fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Stok Opname</span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('inventories.low-stock*') ? 'active' : '' }}">
        <a href="{{ route('inventories.low-stock') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-exclamation-triangle fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Stok Menipis</span>
        </a>
    </li>
    @endrole

    @role('admin|cashier')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Kasir</span>
    </li>
    <li class="nav-item {{ request()->routeIs('kasir.*') ? 'active' : '' }}">
        <a href="{{ route('kasir.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-cash-register fa-fw me-2"></i>
            </span>
            <span class="sidebar-text d-flex justify-content-between w-100">
                Invoice
                @php $pendingInvoiceCount = \App\Models\Invoice::where('status', 'pending')->count(); @endphp
                @if($pendingInvoiceCount > 0)
                    <span class="badge bg-warning rounded-pill ms-1">{{ $pendingInvoiceCount }}</span>
                @endif
            </span>
        </a>
    </li>
    <li class="nav-item {{ request()->routeIs('kasir.create*') ? 'active' : '' }}">
        <a href="{{ route('kasir.create') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-plus-circle fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Buat Invoice</span>
        </a>
    </li>
    @endrole

    @role('admin')
    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item">
        <span class="nav-link text-secondary text-uppercase small fw-bold px-3">Pengaturan</span>
    </li>
    <li class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <a href="{{ route('settings.index') }}" class="nav-link">
            <span class="sidebar-icon">
                <i class="fas fa-cog fa-fw me-2"></i>
            </span>
            <span class="sidebar-text">Pengaturan Farmasi</span>
        </a>
    </li>
    @endrole

    <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
    <li class="nav-item d-none d-md-block">
        <div class="px-3 py-2">
            <div class="small text-white">{{ $user->name }}</div>
            <div class="small text-secondary">{{ $role ?? 'User' }}</div>
        </div>
    </li>
    <li class="nav-item d-none d-md-block">
        <form method="POST" action="{{ route('logout') }}" class="px-3">
            @csrf
            <button type="submit" class="btn btn-secondary btn-sm d-inline-flex align-items-center w-100 justify-content-center">
                <i class="fas fa-right-from-bracket me-1"></i>
                Keluar
            </button>
        </form>
    </li>
</ul>

@extends('layouts.app')

@section('header', 'Pendaftaran Pasien')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.10.5/dist/autonumeric.min.js"></script>
@endpush

@section('content')
    <div class="container-fluid">
        {{-- Toast Container --}}
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999" id="toastContainer">
            <template id="toastTemplate">
                <div class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body"></div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Print Ticket Banner --}}
        @if (session('queue_id'))
            <div class="alert alert-success d-flex align-items-center justify-content-between mb-4" id="printBanner">
                <div>
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Pasien berhasil didaftarkan! Antrean: <strong>{{ session('success') }}</strong>
                </div>
                <a href="{{ route('registration.ticket', session('queue_id')) }}" class="btn btn-sm btn-success"
                    target="_blank">
                    <i class="bi bi-printer me-1"></i> Cetak Tiket
                </a>
            </div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4" id="regTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-register" data-bs-toggle="tab" data-bs-target="#panel-register"
                    type="button" role="tab">
                    <i class="bi bi-plus-circle me-1"></i> Pendaftaran Baru
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-queues" data-bs-toggle="tab" data-bs-target="#panel-queues" type="button"
                    role="tab">
                    <i class="bi bi-list-ol me-1"></i> Antrean Hari Ini
                    <span class="badge bg-warning ms-1" id="queueCount">{{ $todayQueues->flatten()->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#panel-history"
                    type="button" role="tab">
                    <i class="bi bi-clock-history me-1"></i> Riwayat Pendaftaran
                </button>
            </li>
        </ul>

        <div class="tab-content">
            {{-- ═══════════════ TAB 1: PENDAFTARAN BARU ═══════════════ --}}
            <div class="tab-pane fade show active" id="panel-register" role="tabpanel">
                {{-- Progress Stepper --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-3">
                        <div class="row text-center g-0">
                            <div class="col step-col">
                                <div class="step-dot active" id="step1">1</div>
                                <small class="d-block mt-1 text-muted">Cari / Input Pasien</small>
                            </div>
                            <div class="col step-col">
                                <div class="step-dot" id="step2">2</div>
                                <small class="d-block mt-1 text-muted">Pilih Poli & Dokter</small>
                            </div>
                            <div class="col step-col">
                                <div class="step-dot" id="step3">3</div>
                                <small class="d-block mt-1 text-muted">Konfirmasi & Daftar</small>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('registration.store') }}" id="regForm">
                    @csrf
                    <input type="hidden" name="patient_id" id="patient_id" value="{{ old('patient_id') }}">
                    <input type="hidden" name="source" id="source" value="walk_in">

                    <div class="row g-4">
                        {{-- {{── LEFT COLUMN — Search / MJKN / New Patient ──}} --}}
                        <div class="col-lg-7">
                            {{-- MJKN Online Panel --}}
                            <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="bi bi-phone text-primary me-1"></i> Online (Mobile JKN)
                                    </h6>
                                    <span class="badge bg-primary rounded-pill"
                                        id="mjknCount">{{ $mjknQueues->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    @if ($mjknQueues->isEmpty())
                                        <div class="text-center py-3 text-muted small">
                                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                            Belum ada antrean online
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light small">
                                                    <tr>
                                                        <th>Antrean</th>
                                                        <th>Nama</th>
                                                        <th>Poli</th>
                                                        <th>Jam</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($mjknQueues as $item)
                                                        <tr>
                                                            <td class="fw-bold">{{ $item->no_antrean ?? '-' }}</td>
                                                            <td class="small">{{ $item->patient->name ?? '-' }}</td>
                                                            <td><span
                                                                    class="badge bg-light text-dark">{{ $item->kode_poli }}</span>
                                                            </td>
                                                            <td class="small text-muted">
                                                                {{ $item->created_at->format('H:i') }}</td>
                                                            <td class="text-center">
                                                                <form
                                                                    action="{{ route('registration.checkin', $item->id) }}"
                                                                    method="POST" class="d-inline checkin-form">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-success px-3"
                                                                        onclick="return confirm('Check-in pasien ini?')">
                                                                        <i class="bi bi-check-lg"></i> Check-in
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Walk-in: Search Section --}}
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="bi bi-person-search me-1"></i> Cari Pasien</h6>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnNewForm">
                                            <i class="bi bi-person-plus"></i> Pasien Baru
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="searchKeyword"
                                            placeholder="Cari NIK / No. RM / Nama pasien..." autocomplete="off">
                                        <button class="btn btn-primary" type="button" id="btnSearch">
                                            <span id="searchSpinner" class="spinner-border spinner-border-sm d-none me-1"
                                                role="status"></span>
                                            Cari
                                        </button>
                                    </div>
                                    <div id="searchStatus" class="mt-1 small"></div>

                                    {{-- Search Results --}}
                                    <div id="searchResults" class="d-none mt-2 list-group"
                                        style="max-height:280px;overflow-y:auto"></div>

                                    {{-- Selected Patient Badge --}}
                                    <div id="selectedPatientCard" class="d-none mt-3">
                                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
                                            <div class="flex-shrink-0">
                                                <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                    style="width:48px;height:48px;font-weight:700;font-size:1.2rem"
                                                    id="avatarInitial"></div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong id="spName" class="d-block"></strong>
                                                <small class="text-muted">
                                                    RM: <span id="spRm"></span>
                                                    <span class="mx-1">|</span>
                                                    <span id="spNik"></span>
                                                    <span class="mx-1">|</span>
                                                    <span class="badge" id="spInsurance"></span>
                                                </small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                id="btnClearPatient">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Walk-in: New Patient Form --}}
                            <div id="newPatientSection" class="card border-0 shadow-sm mb-4 d-none">
                                <div class="card-header bg-white py-2">
                                    <h6 class="mb-0"><i class="bi bi-person-vcard me-1"></i> Data Pasien Baru</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-medium">NIK <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="nik" id="nik" class="form-control"
                                                maxlength="16" placeholder="16 digit NIK">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small fw-medium">Nama Lengkap <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="name" id="name" class="form-control"
                                                placeholder="Nama sesuai KTP">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-medium">Tgl Lahir <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="birth_date" id="birth_date"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-medium">JK <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex gap-2 mt-1">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        value="L" id="genderL">
                                                    <label class="form-check-label small" for="genderL">L</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="gender"
                                                        value="P" id="genderP">
                                                    <label class="form-check-label small" for="genderP">P</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-medium">Tempat Lahir</label>
                                            <input type="text" name="birth_place" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-medium">Telepon</label>
                                            <input type="text" name="phone" class="form-control"
                                                placeholder="08xxx">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-medium">Alamat</label>
                                            <input type="text" name="address" class="form-control"
                                                placeholder="Alamat lengkap">
                                        </div>
                                    </div>

                                    {{-- Collapsible Social Data --}}
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="collapse" data-bs-target="#socialData">
                                            <i class="bi bi-chevron-down me-1"></i> Data Sosial
                                        </button>
                                        <div class="collapse mt-2" id="socialData">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-medium">Pekerjaan</label>
                                                    <input type="text" name="occupation"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-medium">Pendidikan</label>
                                                    <select name="education" class="form-select form-select-sm">
                                                        <option value="">-</option>
                                                        @foreach (['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'] as $e)
                                                            <option value="{{ $e }}">{{ $e }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-medium">Agama</label>
                                                    <input type="text" name="religion"
                                                        class="form-control form-control-sm"
                                                        placeholder="Islam/Kristen/dll">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-medium">Status Nikah</label>
                                                    <select name="marriage_status" class="form-select form-select-sm">
                                                        <option value="">-</option>
                                                        <option value="Belum Kawin">Belum Kawin</option>
                                                        <option value="Kawin">Kawin</option>
                                                        <option value="Cerai">Cerai</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-medium">Nama Ibu</label>
                                                    <input type="text" name="mother_name"
                                                        class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-medium">Kontak Darurat</label>
                                                    <input type="text" name="emergency_contact"
                                                        class="form-control form-control-sm" placeholder="Nama & No">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small fw-medium">Alergi</label>
                                                    <input type="text" name="allergy"
                                                        class="form-control form-control-sm" placeholder="Obat/makanan">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-medium">Gol Darah</label>
                                                    <select name="blood_type" class="form-select form-select-sm">
                                                        <option value="">-</option>
                                                        @foreach (['A', 'B', 'AB', 'O'] as $bt)
                                                            <option value="{{ $bt }}">{{ $bt }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- {{── RIGHT COLUMN — Registration Form ──}} --}}
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm mb-4 border-start border-success border-4"
                                id="regInfoCard">
                                <div class="card-header bg-white py-2">
                                    <h6 class="mb-0"><i class="bi bi-clipboard-check me-1"></i> Registrasi Antrean</h6>
                                </div>
                                <div class="card-body">
                                    {{-- Insurance info --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Jenis Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <select name="insurance_type" id="insurance_type" class="form-select" required>
                                            <option value="">-- Pilih --</option>
                                            <option value="Umum">🏷️ Umum</option>
                                            <option value="BPJS">🆔 BPJS</option>
                                            <option value="Asuransi Lain">📋 Asuransi Lain</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 d-none" id="insuranceNumberGroup">
                                        <label class="form-label small fw-medium">No. Kartu BPJS</label>
                                        <input type="text" name="insurance_number" class="form-control"
                                            placeholder="0000000000000">
                                    </div>

                                    {{-- Polyclinic & Doctor --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Poliklinik <span
                                                class="text-danger">*</span></label>
                                        <select name="polyclinic_id" id="polyclinic_id" class="form-select" required>
                                            <option value="">-- Pilih Poliklinik --</option>
                                            @foreach ($polyclinics as $poly)
                                                <option value="{{ $poly->id }}" data-code="{{ $poly->code }}">
                                                    {{ $poly->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Dokter</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <select name="doctor_id" id="doctor_id" class="form-select">
                                                <option value="">-- Pilih Dokter --</option>
                                                @foreach ($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}"
                                                        data-polyclinic="{{ $doctor->polyclinic_id }}">
                                                        {{ $doctor->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span class="badge bg-light text-muted" id="docPolyBadge"
                                                style="display:none"></span>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Catatan</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="Keluhan awal atau catatan lain"></textarea>
                                    </div>

                                    {{-- {{── Estimated Queue Info ──}} --}}
                                    <div id="queuePreview" class="d-none p-3 bg-light rounded-3 mb-3">
                                        <small class="text-muted d-block">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Antrean terakhir: <strong id="previewLastQueue">-</strong>
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-white d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary px-4" id="btnSubmit" disabled>
                                        <i class="bi bi-send me-1"></i> Daftarkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- ═══════════════ TAB 2: ANTREAN HARI INI ═══════════════ --}}
            <div class="tab-pane fade" id="panel-queues" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-auto">
                                <select class="form-select form-select-sm" id="qFilterPoli" style="width:180px">
                                    <option value="">Semua Poli</option>
                                    @foreach ($polyclinics as $poly)
                                        <option value="{{ $poly->id }}">{{ $poly->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select form-select-sm" id="qFilterStatus" style="width:140px">
                                    <option value="">Semua Status</option>
                                    <option value="waiting">Menunggu</option>
                                    <option value="called">Dipanggil</option>
                                    <option value="in_progress">Diproses</option>
                                    <option value="completed">Selesai</option>
                                    <option value="cancelled">Batal</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-outline-primary" id="qFilterBtn">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-outline-secondary" id="qRefreshBtn">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh
                                </button>
                            </div>
                            <div class="col-auto ms-auto">
                                <small class="text-muted" id="qLastUpdate"></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="queueTable">
                            <thead class="table-light small">
                                <tr>
                                    <th>Antrean</th>
                                    <th>Nama</th>
                                    <th>Poli</th>
                                    <th>Dokter</th>
                                    <th>Sumber</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="queueTableBody">
                                @forelse($todayQueues->flatten() as $q)
                                    @php $reg = $q->registration; @endphp
                                    <tr data-poly-id="{{ $q->polyclinic_id }}" data-status="{{ $q->status }}"
                                        class="queue-row">
                                        <td class="fw-bold">{{ $q->queue_number }}</td>
                                        <td class="small">{{ $reg?->patient?->name ?? '-' }}</td>
                                        <td>{{ $q->polyclinic?->name ?? '-' }}</td>
                                        <td class="small">{{ $reg?->doctor?->name ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $q->source == 'mjkn' ? 'primary' : 'secondary' }}">{{ $q->source }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $map = [
                                                    'waiting' => 'warning',
                                                    'called' => 'info',
                                                    'in_progress' => 'primary',
                                                    'completed' => 'success',
                                                    'cancelled' => 'secondary',
                                                ];
                                                $label = [
                                                    'waiting' => 'Menunggu',
                                                    'called' => 'Dipanggil',
                                                    'in_progress' => 'Diproses',
                                                    'completed' => 'Selesai',
                                                    'cancelled' => 'Batal',
                                                ];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $map[$q->status] ?? 'secondary' }} status-badge">{{ $label[$q->status] ?? $q->status }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($q->status == 'waiting')
                                                <form action="{{ route('queues.call', $q) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-info"><i
                                                            class="bi bi-megaphone"></i></button>
                                                </form>
                                            @endif
                                            @if (in_array($q->status, ['waiting', 'called']))
                                                <form action="{{ route('queues.in-progress', $q) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary"><i
                                                            class="bi bi-play-fill"></i></button>
                                                </form>
                                            @endif
                                            @if (in_array($q->status, ['called', 'in_progress']))
                                                <form action="{{ route('queues.complete', $q) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-success"><i
                                                            class="bi bi-check-lg"></i></button>
                                                </form>
                                            @endif
                                            @if (in_array($q->status, ['waiting', 'called']))
                                                <form action="{{ route('queues.cancel', $q) }}" method="POST"
                                                    class="d-inline" data-confirm="Batalkan?">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger"><i
                                                            class="bi bi-x-lg"></i></button>
                                                </form>
                                            @endif
                                            <a href="{{ route('queues.show', $q) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center text-muted py-4">Belum ada antrean hari ini
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ TAB 3: RIWAYAT PENDAFTARAN ═══════════════ --}}
            <div class="tab-pane fade" id="panel-history" role="tabpanel">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-2">
                        <div class="row g-2 align-items-end">
                            <div class="col-auto">
                                <label class="form-label small mb-0">Dari</label>
                                <input type="date" class="form-control form-control-sm" id="hDateFrom"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                            </div>
                            <div class="col-auto">
                                <label class="form-label small mb-0">Sampai</label>
                                <input type="date" class="form-control form-control-sm" id="hDateTo"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-primary" id="hFilterBtn"><i class="bi bi-search"></i>
                                    Cari</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th>No. Registrasi</th>
                                    <th>Nama</th>
                                    <th>Poli</th>
                                    <th>Sumber</th>
                                    <th>Status Layanan</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                @forelse($history as $reg)
                                    <tr class="cursor-pointer"
                                        onclick="window.location='{{ route('queues.history', $reg) }}'">
                                        <td class="small fw-medium">{{ $reg->registration_number }}</td>
                                        <td>{{ $reg->patient?->name ?? '-' }}</td>
                                        <td>{{ $reg->polyclinic?->name ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $reg->source == 'mjkn' ? 'primary' : 'secondary' }}">{{ $reg->source }}</span>
                                        </td>
                                        <td>
                                            @php $sm = ['registered'=>'warning','in_consultation'=>'info','completed'=>'success','cancelled'=>'danger']; @endphp
                                            <span
                                                class="badge bg-{{ $sm[$reg->service_status] ?? 'secondary' }}">{{ str_replace('_', ' ', $reg->service_status) }}</span>
                                        </td>
                                        <td class="small">{{ $reg->registration_date?->format('d/m/Y') }}</td>
                                        <td class="small text-muted">{{ $reg->created_at?->format('H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Belum ada pendaftaran</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .step-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0 auto;
            transition: all .3s;
        }

        .step-dot.active {
            background: var(--bs-primary);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.2);
        }

        .step-dot.done {
            background: var(--bs-success);
            color: #fff;
        }

        .queue-row.called {
            animation: rowPulse 2s infinite;
        }

        @keyframes rowPulse {

            0%,
            100% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(13, 202, 240, 0.08);
            }
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .cursor-pointer:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.04);
        }

        .avatar-placeholder {
            font-size: 1.2rem;
            user-select: none;
        }

        .toast-container .toast {
            min-width: 300px;
        }

        .toast.bg-success .toast-body {
            color: #fff;
        }

        .toast.bg-danger .toast-body {
            color: #fff;
        }

        .toast.bg-warning .toast-body {
            color: #000;
        }

        .toast.bg-info .toast-body {
            color: #fff;
        }
    </style>
@endsection

@push('scripts')
    <script>
        // ═══════════════════════════════════════════════════════════════
        // TOAST NOTIFICATION
        // ═══════════════════════════════════════════════════════════════
        function showToast(message, type = 'success') {
            const tpl = document.getElementById('toastTemplate');
            const clone = tpl.content.cloneNode(true);
            const toast = clone.querySelector('.toast');
            const colors = {
                success: 'bg-success',
                error: 'bg-danger',
                warning: 'bg-warning',
                info: 'bg-info'
            };
            toast.classList.add(colors[type] || 'bg-success');
            toast.querySelector('.toast-body').textContent = message;
            document.getElementById('toastContainer').appendChild(clone);
            new bootstrap.Toast(toast, {
                delay: 5000
            }).show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }

        ['success', 'error', 'warning', 'info'].forEach(t => {
            if (window.flash?.[t]) showToast(window.flash[t], t);
        });

        // ═══════════════════════════════════════════════════════════════
        // STEPPER
        // ═══════════════════════════════════════════════════════════════
        function updateStep() {
            const pid = document.getElementById('patient_id').value;
            const poly = document.getElementById('polyclinic_id').value;
            const s1 = document.getElementById('step1');
            const s2 = document.getElementById('step2');
            const s3 = document.getElementById('step3');
            if (pid || document.getElementById('nik').value) {
                s1.classList.add('done');
                s1.textContent = '✓';
            } else {
                s1.classList.remove('done');
                s1.textContent = '1';
            }
            if (poly) {
                s2.classList.add('active');
            } else {
                s2.classList.remove('active');
            }
            // step3 active when everything is ready (handled by btnSubmit disable)
        }

        ['patient_id', 'nik', 'polyclinic_id', 'doctor_id', 'insurance_type'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', updateStep);
        });
        document.querySelectorAll('input[name="name"], input[name="birth_date"], input[name="gender"]').forEach(el => {
            el.addEventListener('input', updateStep);
            el.addEventListener('change', updateStep);
        });

        // ═══════════════════════════════════════════════════════════════
        // SEARCH PATIENT
        // ═══════════════════════════════════════════════════════════════
        const searchInput = document.getElementById('searchKeyword');
        const btnSearch = document.getElementById('btnSearch');
        const searchStatus = document.getElementById('searchStatus');
        const searchResults = document.getElementById('searchResults');
        const selectedCard = document.getElementById('selectedPatientCard');
        const newSection = document.getElementById('newPatientSection');

        function getInitials(name) {
            if (!name) return '?';
            return name.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
        }

        function selectPatient(data) {
            document.getElementById('patient_id').value = data.id;
            document.getElementById('source').value = data.insurance_type === 'BPJS' ? 'walk_in' : 'walk_in';
            document.getElementById('avatarInitial').textContent = getInitials(data.name);
            document.getElementById('spName').textContent = data.name;
            document.getElementById('spRm').textContent = data.no_rm;
            document.getElementById('spNik').textContent = data.nik;
            const badge = document.getElementById('spInsurance');
            badge.textContent = data.insurance_type;
            badge.className = 'badge ' + (data.insurance_type === 'BPJS' ? 'bg-success' : 'bg-info');
            selectedCard.classList.remove('d-none');
            newSection.classList.add('d-none');
            searchResults.classList.add('d-none');
            searchStatus.textContent = '';
            document.getElementById('btnSubmit').disabled = false;

            if (data.insurance_type === 'BPJS' && data.insurance_number) {
                document.getElementById('insurance_type').value = 'BPJS';
                document.getElementById('insuranceNumberGroup').classList.remove('d-none');
                document.querySelector('input[name="insurance_number"]').value = data.insurance_number;
            }

            document.querySelectorAll('#newPatientSection input, #newPatientSection select').forEach(el => el.disabled =
                true);
            updateStep();
        }

        document.getElementById('btnClearPatient').addEventListener('click', function() {
            document.getElementById('patient_id').value = '';
            selectedCard.classList.add('d-none');
            document.getElementById('btnSubmit').disabled = true;
            document.querySelectorAll('#newPatientSection input, #newPatientSection select').forEach(el => el
                .disabled = false);
            searchInput.value = '';
            searchInput.focus();
            updateStep();
        });

        btnSearch.addEventListener('click', function() {
            const kw = searchInput.value.trim();
            if (kw.length < 3) {
                searchStatus.textContent = 'Minimal 3 karakter';
                searchStatus.className = 'mt-1 small text-danger';
                return;
            }

            searchStatus.textContent = 'Mencari...';
            searchStatus.className = 'mt-1 small text-info';
            document.getElementById('searchSpinner').classList.remove('d-none');

            fetch('{{ route('registration.search') }}?keyword=' + encodeURIComponent(kw))
                .then(r => r.json())
                .then(data => {
                    document.getElementById('searchSpinner').classList.add('d-none');
                    searchResults.classList.remove('d-none');
                    if (!data.found || data.data.length === 0) {
                        searchResults.innerHTML =
                            '<a class="list-group-item list-group-item-action text-muted small">Pasien tidak ditemukan. Klik "Pasien Baru" untuk mendaftarkan.</a>';
                        searchStatus.textContent = '';
                        return;
                    }
                    searchResults.innerHTML = data.data.map(p => `
                <a class="list-group-item list-group-item-action d-flex align-items-center gap-2" href="#" onclick="selectPatient(${JSON.stringify(p).replace(/"/g,'&quot;')}); return false;">
                    <div class="avatar-placeholder rounded-circle bg-${p.insurance_type === 'BPJS' ? 'success' : 'secondary'} text-white d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:40px;height:40px;font-weight:600;font-size:0.9rem">${getInitials(p.name)}</div>
                    <div class="flex-grow-1">
                        <strong class="d-block small">${p.name}</strong>
                        <small class="text-muted">RM ${p.no_rm} • ${p.nik} • <span class="badge bg-${p.insurance_type === 'BPJS' ? 'success' : 'info'}">${p.insurance_type}</span></small>
                    </div>
                </a>
            `).join('');
                    searchStatus.textContent = '';
                })
                .catch(() => {
                    document.getElementById('searchSpinner').classList.add('d-none');
                    searchStatus.textContent = 'Gagal mencari';
                    searchStatus.className = 'mt-1 small text-danger';
                });
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnSearch.click();
            }
        });

        document.getElementById('btnNewForm').addEventListener('click', function() {
            selectedCard.classList.add('d-none');
            searchResults.classList.add('d-none');
            newSection.classList.toggle('d-none');
            document.querySelectorAll('#newPatientSection input, #newPatientSection select').forEach(el => el
                .disabled = newSection.classList.contains('d-none'));
            if (!newSection.classList.contains('d-none')) {
                document.getElementById('nik').focus();
                document.getElementById('patient_id').value = '';
                document.getElementById('btnSubmit').disabled = true;
                updateStep();
            }
        });

        // ═══════════════════════════════════════════════════════════════
        // INSURANCE TOGGLE
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('insurance_type').addEventListener('change', function() {
            document.getElementById('insuranceNumberGroup').classList.toggle('d-none', this.value !== 'BPJS');
            validateForm();
        });

        // ═══════════════════════════════════════════════════════════════
        // POLYCLINIC → DOCTOR FILTER
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('polyclinic_id').addEventListener('change', function() {
            const polyId = this.value;
            const sel = document.getElementById('doctor_id');
            const badge = document.getElementById('docPolyBadge');
            for (const opt of sel.options) {
                if (!opt.value) continue;
                const show = opt.dataset.polyclinic == polyId;
                opt.style.display = show ? '' : 'none';
                if (show && !sel.value) {
                    sel.value = opt.value;
                }
            }
            if (sel.value && sel.selectedOptions[0]?.dataset?.polyclinic != polyId) sel.value = '';
            badge.style.display = polyId ? 'inline-block' : 'none';
            if (polyId) badge.textContent = this.options[this.selectedIndex].text;
            validateForm();
        });

        // ═══════════════════════════════════════════════════════════════
        // FORM VALIDATION
        // ═══════════════════════════════════════════════════════════════
        function validateForm() {
            const btn = document.getElementById('btnSubmit');
            const pid = document.getElementById('patient_id').value;
            const nik = document.getElementById('nik').value;
            const name = document.getElementById('name').value;
            const bd = document.getElementById('birth_date').value;
            const gender = document.querySelector('input[name="gender"]:checked');
            const poly = document.getElementById('polyclinic_id').value;
            const ins = document.getElementById('insurance_type').value;

            const hasPatient = !!pid || (nik.length === 16 && name && bd && gender);
            const hasQueue = poly && ins;
            btn.disabled = !(hasPatient && hasQueue);
        }

        ['nik', 'name', 'birth_date', 'polyclinic_id', 'insurance_type'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', validateForm);
        });
        document.querySelectorAll('input[name="gender"]').forEach(el => el.addEventListener('change', validateForm));
        document.querySelectorAll('#newPatientSection input').forEach(el => el.addEventListener('input', validateForm));

        updateStep();
        validateForm();

        // ═══════════════════════════════════════════════════════════════
        // TAB 2: QUEUE FILTER + AUTO-REFRESH
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('qFilterBtn').addEventListener('click', filterQueue);
        document.getElementById('qFilterBtn').click = filterQueue;

        function filterQueue() {
            const poly = document.getElementById('qFilterPoli').value;
            const status = document.getElementById('qFilterStatus').value;
            document.querySelectorAll('#queueTableBody .queue-row').forEach(row => {
                const showPoly = !poly || row.dataset.polyId === poly;
                const showStatus = !status || row.dataset.status === status;
                row.style.display = showPoly && showStatus ? '' : 'none';
            });
        }

        document.getElementById('qRefreshBtn').addEventListener('click', () => location.reload());

        setInterval(() => {
            document.getElementById('qLastUpdate').textContent = 'Terakhir: ' + new Date().toLocaleTimeString(
                'id-ID');
        }, 1000);

        // ═══════════════════════════════════════════════════════════════
        // TAB 3: HISTORY FILTER
        // ═══════════════════════════════════════════════════════════════
        document.getElementById('hFilterBtn').addEventListener('click', function() {
            const from = document.getElementById('hDateFrom').value;
            const to = document.getElementById('hDateTo').value;
            if (!from || !to) return;
            fetch('{{ route('registration.search') }}?from=' + from + '&to=' + to)
                .then(r => r.json())
                .then(data => {
                    // reload page with params
                    window.location = '{{ route('registration.index') }}?from=' + from + '&to=' + to;
                })
                .catch(() => {});
        });
    </script>
@endpush

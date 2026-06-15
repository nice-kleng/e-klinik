# New Plan — e-Klinik

Dokumen ini berisi hasil diskusi dan kesepakatan per modul. Ditulis bertahap, modul per modul.
Setiap modul akan di-review bersama sebelum dieksekusi ke source code.

---

## Module Core & Front Office

### Alur Pendaftaran — Final

```
Pasien datang
│
├── 🔵 MJKN (Online)
│   → Data masuk ke bpjs_antrean (status: pending)
│   → Resepsionis klik "Check-in"
│   → bpjs_antrean.status = confirmed
│   → Insert registrations (source=mjkn, status=registered)
│   → Insert queues (queue_sequence dari Antrol, check_in_at=now)
│   → Cetak tiket
│   → Pasien ke poli
│
└── 🟢 Walk-in (Baru/Lama)
    → Baru: Input data diri + data sosial → Cetak Kartu Pasien (QR → no RM)
    → Lama: Cari by NIK / no RM / scan QR → Update data jika perlu
    → Insert registrations (source=walk_in, status=registered)
    → Insert queues (queue_sequence = max per poli hari ini +1, check_in_at=now)
    → Cetak tiket antrean (banner hijau + tombol cetak muncul di halaman)
    → Pasien ke poli
```

### Antrean & Pemanggilan

- Antrean per poli, independen
- Nomor urut: queue_sequence (integer, reset per poli per hari)
- Display nomor: `{kode_poli}-{nomor}` (contoh: `UMUM-001`, `GIGI-005`)
- **Aturan panggilan:** nomor queue_sequence terkecil yang sudah check_in dipanggil duluan
- Staff poli klik "Panggil" → status `waiting` → `called`
- Pasien masuk → `in_progress`
- Selesai periksa → `completed`

### Struktur Tabel Baru

#### 1. `registrations` — BARU (mencatat setiap kunjungan pasien)

```php
Schema::create('registrations', function (Blueprint $table) {
    $table->id();
    $table->string('registration_number');            // REG-202606-0001
    $table->foreignId('patient_id')->constrained();
    $table->foreignId('polyclinic_id')->constrained();
    $table->foreignId('doctor_id')->nullable()->constrained();
    $table->date('registration_date');
    $table->enum('source', ['walk_in', 'mjkn', 'rujukan']);
    $table->enum('service_status', [
        'registered', 'in_consultation', 'lab', 'pharmacy', 'cashier', 'completed', 'cancelled'
    ])->default('registered');
    $table->string('bpjs_antrian_id')->nullable();     // link ke bpjs_antrean
    $table->string('no_sep', 50)->nullable();           // SEP BPJS
    $table->string('age_text', 50);                     // "76 Tahun 10 Bulan 22 Hari"
    $table->tinyInteger('age_years');
    $table->tinyInteger('age_months');
    $table->tinyInteger('age_days');
    $table->text('notes')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();

    $table->index(['registration_date', 'polyclinic_id']);
    $table->index('registration_number');
    $table->index('patient_id');
});
```

**Fungsi `registrations`:**
- Mencatat setiap kunjungan (riwayat kunjungan pasien)
- Status layanan end-to-end (bukan hanya antrean)
- Umur pasien dihitung saat registrasi (akurat sesuai tanggal daftar)
- No.Reg (registration_number) untuk lacak RME, resep, tagihan
- Menampung no SEP dan data BPJS
- RME link via `registration_id` (bukan queue_id)

#### 2. `queues` — REFINE (hanya untuk calling)

```php
// Migrasi: tambah kolom + ubah relasi
// HAPUS: patient_id, service_type, bpjs_sep_id, bpjs_antrian_id, estimated_wait_time, notes
// TAMBAH: registration_id (FK), queue_sequence (integer)
// UBAH: status jadi lebih sederhana

$table->foreignId('registration_id')->constrained()->cascadeOnDelete();
$table->foreignId('polyclinic_id')->constrained();
$table->integer('queue_sequence');                // nomor urut per poli per hari
$table->string('queue_number');                   // display: UMUM-001
$table->date('queue_date');
$table->enum('source', ['walk_in', 'mjkn']);      // sumber pasien
$table->enum('status', ['waiting', 'called', 'in_progress', 'completed', 'cancelled'])->default('waiting');
$table->timestamp('check_in_at');
$table->timestamp('confirmed_at')->nullable();     // khusus MJKN
$table->foreignId('created_by')->constrained('users');
$table->timestamps();

$table->index(['queue_date', 'polyclinic_id', 'status']);
```

#### 3. `queue_calls` — BARU (riwayat pemanggilan per poli)

```php
Schema::create('queue_calls', function (Blueprint $table) {
    $table->id();
    $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
    $table->foreignId('polyclinic_id')->constrained();
    $table->foreignId('called_by')->constrained('users');    // staff poli
    $table->tinyInteger('call_sequence');                     // panggilan ke-1, ke-2, dst
    $table->timestamp('called_at');
    $table->timestamp('responded_at')->nullable();            // waktu pasien masuk
    $table->timestamps();
});
```

#### 4. `queue_milestones` — BARU (taskid BPJS Antrol 1-7)

```php
Schema::create('queue_milestones', function (Blueprint $table) {
    $table->id();
    $table->foreignId('queue_id')->constrained()->cascadeOnDelete();
    $table->tinyInteger('task_id');           // 1-7
    $table->string('task_name');              // "Pendaftaran", "Pemeriksaan Dokter", dll
    $table->timestamp('task_time');
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();

    $table->unique(['queue_id', 'task_id']);  // 1 queue hanya punya 1 waktu per task
});
```

### Perubahan Tabel yang Ada

#### `patients` — TAMBAH kolom data sosial

| Kolom Baru | Tipe |
|-----------|------|
| `pekerjaan` | string(100) nullable |
| `pendidikan` | enum: SD/SMP/SMA/D1/D2/D3/S1/S2/S3 nullable |
| `agama` | enum: Islam/Kristen/Katolik/Hindu/Buddha/Konghucu nullable |
| `golongan_darah` | enum: A/B/AB/O nullable |
| `status_pernikahan` | enum: Belum Kawin/Kawin/Cerai nullable |
| `nama_ibu` | string(100) nullable |
| `kontak_darurat` | string(200) nullable |
| `alergi` | text nullable |

#### `bpjs_antrean` — TETAP (sudah ada)

Hanya perlu ditambahi kolom `status` → `['pending', 'confirmed']` jika belum ada.
Source untuk data mentah MJKN sebelum di-check-in.

### Relasi Baru

```
patients ──→ registrations ──→ queues ──→ queue_calls
                │                  │
                │                  └── queue_milestones
                │
                ├── medical_records (RME) → nanti link via registration_id
                ├── prescriptions
                ├── lab_requests
                └── billings (Phase 5)
```

### Manfaat Perubahan

| Kebutuhan | Solusi |
|-----------|--------|
| Riwayat kunjungan | `registrations` — semua status, tidak terhapus |
| Umur saat daftar | Disimpan di `registrations.age_*` |
| Lacak RME | `medical_records.registration_id` |
| Status layanan detail | `registrations.service_status` (registered → in_consultation → lab → pharmacy → cashier → completed) |
| SEP BPJS | Langsung di `registrations.no_sep` |
| Panggilan antrean | `queues` hanya untuk calling, ringan |
| Taskid BPJS | `queue_milestones` |
| Audit panggilan | `queue_calls` — siapa, kapan, berapa kali |

### Role & Layout

**Role Final** (ENUM `role` di users):

| Role | Modul |
|------|-------|
| `admin` | Semua |
| `doctor` | RME, resep, lab, antrean polinya sendiri |
| `nurse` | Bantu dokter |
| `pharmacist` | Apotek & inventaris |
| `cashier` | Kasir & piutang (Phase 5) |
| `receptionist` | Pendaftaran & antrean |
| `laborant` | Lab |

**Layout Resepsionis — Sidebar:**

```
[Dashboard Pendaftaran]
├── 🔵 Online (MJKN)
│   └── Tabel: no antrean | nama | poli | jam booking    [Check-in]
├── 🟢 Walk-in
│   ├── [Registrasi Baru] — form data diri + data sosial
│   └── [Cari Pasien] — by NIK / no RM / scan QR
├── 📋 Monitor Antrean
│   └── Tabel per poli: no | nama | status | waktu tunggu
└── 📊 Laporan
    └── Rekap harian: total daftar (walk-in / mjkn)
```

**Layout Dokter — Sidebar:**

```
[Dashboard Dokter]
├── 🏥 Antrean Poli Saya
│   └── List pasien (urut queue_sequence, hanya yang check_in)
│       └── [Panggil] → status called → [Mulai Periksa] → in_progress
└── ...
```

### Matriks Akses Modul Pendaftaran

| Fitur | Admin | Receptionist | Dokter | Perawat |
|-------|-------|-------------|--------|---------|
| Registrasi pasien baru | ✅ | ✅ | ❌ | ❌ |
| Cari/edit pasien lama | ✅ | ✅ | ✅ (read only) | ✅ (read only) |
| Check-in MJKN | ✅ | ✅ | ❌ | ❌ |
| Cetak kartu pasien | ✅ | ✅ | ❌ | ❌ |
| Cetak tiket antrean | ✅ | ✅ | ❌ | ❌ |
| Lihat antrean per poli | ✅ (semua) | ✅ (semua) | ✅ (polinya saja) | ✅ (polinya saja) |
| Panggil antrean | ❌ | ❌ | ✅ | ✅ |
| Laporan pendaftaran | ✅ | ✅ | ❌ | ❌ |

---

## Execution Status — Module Core & Front Office ✅

Semua perubahan di atas sudah dieksekusi pada **12 Juni 2026** dengan rincian:

- **8 migration baru** (000001–000008): 3 tabel baru (registrations, queue_calls, queue_milestones), modifikasi queues + patients + medical_records + bpjs_seps
- **3 model baru**: `Registration`, `QueueCall`, `QueueMilestone`
- **5 model diupdate**: `Queue`, `Patient`, `MedicalRecord`, `Doctor`, `BpjsSep`
- **Service diupdate**: `QueueService` (full rewrite), `BpjsSepService` (route via registration)
- **Controller diupdate**: `RegistrationController`, `QueueController`, `BpjsSepController`, `Api\QueueController`
- **Routes diupdate**: role `receptionist` ditambahkan, route checkin/ticket/history baru
- **Views diupdate**: registration/index, queues/index, queues/show, queues/display, queues/history (baru), patients/index, patients/create, patients/edit, patients/show, patients/print-card (baru), layouts/navigation
- **Seeder**: `RoleSeeder` + `MasterDataSeeder` include receptionist
- **Package**: `simplesoftwareio/simple-qrcode` (QR code untuk cetak kartu pasien)
- **Role `receptionist`** siap login di `receptionist@e-klinik.com` / `receptionist123`

### Sudah dikerjakan (13 Juni 2026)
1. ✅ Sidebar role-aware via `@role()` directive — menu disembunyikan sesuai role user
2. ✅ Cetak kartu pasien dengan QR code — route `patients/{patient}/print-card`, layout 85.6×54mm, QR berisi no RM
3. ✅ Social fields (education, mother_name, emergency_contact, allergy) ditambahkan ke Patient CRUD views
4. ✅ `queues.history` view dibuat — detail registrasi + pasien + antrean + milestone + RME
5. ✅ Tombol cetak tiket muncul setelah registrasi — banner hijau dengan link cetak

### Masih perlu dikerjakan (saat itu)
1. Display TV real-time (WebSocket/Pusher)
2. Dashboard resepsionis khusus
3. Monitor antrean per poli dengan tampilan real-time

---

## Sprint 15 Juni 2026 — Real-time, Display TV, AJAX Calling

Semua perubahan di bawah dieksekusi pada **15 Juni 2026** untuk menyempurnakan modul Pendaftaran & Antrean.

### QueueUpdated Event — Real-time via ShouldBroadcastNow

**File:** `app/Events/QueueUpdated.php`

- Implement `ShouldBroadcastNow` — event langsung ke WebSocket tanpa lewat queue worker
- Tambah property `polyclinicName` (string) — untuk TTS menyebut nama poli
- Action event:
  - `created` — dikirim dari `QueueService::registerQueue()` setelah queue dibuat (pasien umum registrasi / BPJS check-in)
  - `called` — dikirim dari `QueueController::callAjax()` setelah `callAndProgress()`
  - `in_progress` / `completed` / `cancelled` — dari method masing-masing

### QueueService — callAndProgress() + QueueUpdated dispatch

**File:** `app/Services/QueueService.php`

**Method baru `callAndProgress()`:**
- Ambil antrean `waiting` tertua (queue_sequence ASC)
- Buat `QueueCall` record (riwayat panggilan)
- Set status langsung ke `in_progress` (**skip `called`**)
- Update `registration.service_status` → `in_consultation`
- Return queue fresh

**Dispatch `QueueUpdated` di `registerQueue()`:**
- Setelah `Queue::create()`, fire event dengan action `created`
- Display TV langsung re-render tanpa perlu polling 5 detik

### Queue AJAX — Tanpa Redirect

**File:** `app/Http/Controllers/Web/QueueController.php`

- **`callAjax()`** — method baru, panggil `callAndProgress()` + fire `QueueUpdated` action `called`. Return JSON `{ success, data: { queue: { id, queue_number, patient_name, polyclinic_name } } }`
- **`queueData()`** — JSON endpoint detail queue (status, patient, polyclinic, source, status_label)
- **`inProgress()`/`complete()`/`cancel()`** — dual response: JSON untuk `expectsJson()`, redirect untuk form

**File:** `resources/views/queues/index.blade.php`

- Tombol Panggil: AJAX via `data-url`, tanpa redirect, disable + loading state
- TTS 3x otomatis setelah panggil (Promise queue, speak 3 kali)
- Tombol "Dengarkan" dihapus (diganti auto-TTS)
- Proses/Selesai/Batal: AJAX via `data-url`, refresh tabel via `refreshTable()` (fetch + DOMParser)
- Echo listener: `queue-updated` event → TTS + refresh tabel

### Display TV — Glassmorphism + Real-time

**File:** `resources/views/queues/display.blade.php`

- Redesign landing grid: background gradien (dark purple-blue), glassmorphism card (`backdrop-filter: blur(16px)`, `rgba(255,255,255,0.03)`), hover lift + glow

**File:** `resources/views/queues/display-tv.blade.php`

- Background: `linear-gradient(135deg, #0f0c29, #302b63, #24243e)` + radial gradient overlay
- Semua card: glassmorphism (`backdrop-filter: blur(24px)`, `rgba(...)`, `border: 1px solid rgba(255,255,255,0.06)`)
- Current card: gradien `rgba(0,212,255,0.12) → rgba(124,58,237,0.12)` + blur, nomor antrean gradient text (`#00d4ff → #7c3aed`)
- Called card: glow pulse animation (`called-pulse`), `rgba(13,202,240,0.08)` + blur
- Update banner: `backdrop-filter: blur(40px)` + slide-up
- Waiting list: scrollbar custom, `rgba(251,191,36,0.15)` badge, slide-in animation
- **Fix kritis:** Semua elemen DOM (`currentNumber`, `currentName`, `currentDoctor`, `calledNumber`, `calledName`, dll) selalu di-render di HTML dengan `style="display:none"` saat tidak aktif — bukan conditional `@if/@else`. Mencegah null reference crash di JS `rerender()`
- **Fix called display:** Perbaiki `@empty(!$called)` → `@if(!$called)` (logika terbalik)
- **Fix JS rerender:** Reset `style.display` untuk `calledNumber`/`calledName` saat `d.called` truthy

### TTS — 3x Otomatis dengan Queue

**File:** `resources/views/queues/index.blade.php` dan `resources/views/queues/display-tv.blade.php`

- Fungsi `speakText(text)` — return Promise, queue-based (tidak overlap)
- Fungsi `speakCall(queueNumber, polyclinic, patientName)` — loop 3x, await setiap speak
- Voice detection: cari voice `id-ID`, fallback ke voice pertama
- Init: `speechSynthesis.getVoices()` + `voiceschanged` event listener

### Routes Baru

```php
Route::post('/{queue}/call-ajax', [QueueController::class, 'callAjax'])->name('call-ajax');
Route::get('/{queue}/data', [QueueController::class, 'queueData'])->name('data');
```

### Dokumentasi

**File:** `AGENTS.md`

- Queue lifecycle: `waiting → in_progress → completed` (skip `called`)
- Tambah Queue Controller section (callAjax, queueData, dual response)
- Update Realtime section (event actions, property, display TV notes)
- Update TTS section (3x dengan queue, tanpa tombol Dengarkan)
- Catatan: semua elemen DOM display TV harus selalu di-render (display:none)

### Ringkasan Perubahan

| File | Perubahan |
|------|-----------|
| `app/Events/QueueUpdated.php` | `ShouldBroadcastNow`, `polyclinicName` |
| `app/Services/QueueService.php` | `callAndProgress()`, QueueUpdated di `registerQueue()` |
| `app/Http/Controllers/Web/QueueController.php` | `callAjax()`, `queueData()`, expectsJson(), `callAndProgress()` |
| `routes/web.php` | Route `call-ajax` + `data` |
| `resources/views/queues/index.blade.php` | AJAX call, TTS 3x, tanpa tombol Dengarkan, Echo refresh |
| `resources/views/queues/display.blade.php` | Glassmorphism redesign |
| `resources/views/queues/display-tv.blade.php` | Glassmorphism + fix DOM null ref + called display + fix rerender |
| `AGENTS.md` | Update lifecycle, realtime, TTS, controller docs |

---

> **Catatan:** Dokumen ini akan terus bertambah per modul. Modul selanjutnya: **RME (SOAP, form spesialis, ICD-10, ICD-9-CM, lab request)**.

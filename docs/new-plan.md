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
    → Cetak tiket antrean
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

- **7 migration baru** (000001–000008): 3 tabel baru (registrations, queue_calls, queue_milestones), modifikasi queues + patients + medical_records + bpjs_seps
- **3 model baru**: `Registration`, `QueueCall`, `QueueMilestone`
- **5 model diupdate**: `Queue`, `Patient`, `MedicalRecord`, `Doctor`, `BpjsSep`
- **Service diupdate**: `QueueService` (full rewrite), `BpjsSepService` (route via registration)
- **Controller diupdate**: `RegistrationController`, `QueueController`, `BpjsSepController`, `Api\QueueController`
- **Routes diupdate**: role `receptionist` ditambahkan, route checkin/ticket/history baru
- **Views diupdate**: registration/index, queues/index, queues/show, queues/display, registration/ticket (baru)
- **Seeder**: `RoleSeeder` + `MasterDataSeeder` include receptionist
- **Role `receptionist`** siap login di `receptionist@e-klinik.com` / `receptionist123`

### Masih perlu dikerjakan (post-migration)
1. UI layout per role (sidebar receptionist berbeda dengan dokter/kasir)
2. Cetak kartu pasien dengan QR code (halaman pasien)
3. Display TV real-time (WebSocket/Pusher)
4. Dashboard resepsionis khusus
5. Monitor antrean per poli dengan tampilan real-time

---

> **Catatan:** Dokumen ini akan terus bertambah per modul. Modul selanjutnya: **RME (SOAP, form spesialis, ICD-10, ICD-9-CM, lab request)**.

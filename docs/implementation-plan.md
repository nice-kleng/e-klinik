# Rencana Implementasi Sistem Informasi Klinik (e-Klinik)

**Dokumen:** Rencana Implementasi Teknis  
**Proyek:** e-Klinik - Sistem Informasi Manajemen Klinik  
**Versi:** 1.0  
**Terakhir Diperbarui:** Juni 2026  

---

## Daftar Isi

1. [Gambaran Umum Proyek](#1-gambaran-umum-proyek)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Modul & Fitur](#3-modul--fitur)
   - [A. Manajemen Pasien & Antrean](#a-manajemen-pasien--antrean)
   - [B. Rekam Medis Elektronik (RME)](#b-rekam-medis-elektronik-rme)
   - [C. Manajemen Apotek & Inventaris](#c-manajemen-apotek--inventaris)
    - [D. Integrasi BPJS Kesehatan](#d-integrasi-bpjs-kesehatan)
    - [E. Integrasi Satu Sehat (Kemenkes)](#e-integrasi-satu-sehat-kemenkes)
    - [F. Manajemen Laboratorium](#f-manajemen-laboratorium)
4. [Struktur Database](#4-struktur-database)
5. [Alur Data Integrasi](#5-alur-data-integrasi)
6. [Tahapan Implementasi](#6-tahapan-implementasi)
7. [Struktur Folder Proyek](#7-struktur-folder-proyek)
8. [Konfigurasi Lingkungan](#8-konfigurasi-lingkungan)
9. [API Endpoints](#9-api-endpoints)
10. [Library & Dependencies](#10-library--dependencies)

---

## 1. Gambaran Umum Proyek

### 1.1 Nama Proyek

**e-Klinik** — Sistem Informasi Manajemen Klinik berbasis web yang dirancang untuk mengelola seluruh operasional klinik secara digital, mulai dari pendaftaran pasien, antrean, rekam medis elektronik, apotek dan inventaris, hingga integrasi dengan sistem eksternal seperti BPJS Kesehatan dan platform Satu Sehat milik Kementerian Kesehatan RI.

### 1.2 Tujuan

- Menyediakan sistem informasi terpadu untuk manajemen klinik yang efisien dan paperless.
- Memenuhi standar interoperabilitas data kesehatan nasional melalui integrasi Satu Sehat (FHIR).
- Mendukung proses klaim BPJS Kesehatan secara elektronik melalui VClaim API.
- Meningkatkan kualitas pelayanan pasien dengan sistem antrean terintegrasi dan rekam medis elektronik yang terstruktur.

### 1.3 Tech Stack

| Komponen         | Teknologi                                        |
| ---------------- | ------------------------------------------------ |
| **Framework**    | Laravel 13 (PHP 8.3+)                            |
| **Database**     | MySQL 8.0+ / MariaDB 10.6+                       |
| **HTTP Client**  | Guzzle HTTP Client 7.x                            |
| **Auth Web**     | Laravel Breeze (session-based)                   |
| **Auth API**     | Laravel Sanctum (token-based)                    |
| **Middleware**   | Custom RoleMiddleware (`role:admin,doctor,...`)  |
| **Queue**        | Laravel Queue (database/redis driver)            |
| **Cache**        | File cache                                       |
| **Frontend**     | Blade + Bootstrap 5                              |
| **Realtime**     | Laravel Echo + Pusher / WebSocket                |
| **PDF**          | DomPDF                                           |
| **Export/Import**| Laravel Excel (Maatwebsite)                      |
| **Image**        | Intervention Image                               |

### 1.4 Lingkungan Pengembangan

- **OS:** Windows 11 / Linux (Ubuntu 22.04+)
- **Web Server:** Apache / Nginx
- **PHP:** 8.3 atau lebih baru
- **Composer:** 2.x
- **Node.js:** 20.x (untuk asset compilation)
- **Database:** MySQL 8.0+ atau MariaDB 10.6+

---

## 2. Arsitektur Sistem

### 2.1 Arsitektur Backend: Laravel MVC + Service Layer Pattern

Sistem menggunakan arsitektur **Laravel MVC (Model-View-Controller)** yang diperkuat dengan **Service Layer Pattern** untuk memisahkan logika bisnis dari controller. Pendekatan ini memberikan:

- **Separation of Concerns:** Controller hanya menangani request/response, Service layer menangani logika bisnis.
- **Testability:** Service layer dapat di-unit test tanpa bergantung pada HTTP.
- **Reusability:** Logika bisnis yang sama bisa digunakan dari controller, command artisan, atau job queue.
- **Maintainability:** Perubahan aturan bisnis cukup dilakukan di satu tempat (service class).

Lapisan arsitektur:

```
Route (API)
  -> Controller (validasi input, format response)
    -> Service (logika bisnis, koordinasi antar model)
      -> Repository/Model (akses data / Eloquent)
        -> Database (MySQL)
```

Untuk integrasi eksternal (BPJS, Satu Sehat), ditambahkan lapisan **Client/Adapter**:

```
Service
  -> Client/Adapter (HTTP request ke API eksternal via Guzzle)
    -> Transformasi data (DTO/Array)
      -> Logging & Error Handling
```

### 2.2 RESTful API

Semua komunikasi antar modul dan dengan sistem eksternal dilakukan melalui RESTful API dengan format JSON response yang konsisten.

**Standard Response Format:**

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": { ... },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  },
  "errors": null
}
```

**Error Response:**

```json
{
  "success": false,
  "message": "Validation error",
  "data": null,
  "errors": {
    "nama": ["Nama pasien wajib diisi"],
    "tanggal_lahir": ["Format tanggal tidak valid"]
  }
}
```

### 2.3 Database

- **Engine:** InnoDB (support foreign key, transaction).
- **Charset:** `utf8mb4` dengan collation `utf8mb4_unicode_ci`.
- **JSON Fields:** Digunakan untuk menyimpan FHIR resources (Satu Sehat) dan data BPJS yang bersifat dinamis.
- **Soft Deletes:** Semua tabel master menggunakan soft deletes (`deleted_at`).
- **Timestamps:** Semua tabel memiliki `created_at`, `updated_at`.
- **Indexing:** Index pada kolom yang sering di-query (nomor RM, nomor SEP, tanggal, foreign key).

### 2.4 Authentication & Authorization

| Metode             | Keterangan                                                |
| ------------------ | --------------------------------------------------------- |
| **Breeze (Web)**   | Session-based auth untuk Blade (login, register, password) |
| **Sanctum (API)**  | Token-based API authentication untuk frontend              |
| **RoleMiddleware** | Custom middleware: `role:admin,doctor,pharmacist,cashier,laborant` pada rute web |
| **BPJS Auth**      | HMAC-SHA256 signature + AES-256-CBC decrypt untuk VClaim & Antrol |
| **Satu Sehat**     | OAuth2 Client Credentials Grant                           |

### 2.5 Integration Pattern: Service-Oriented Architecture (SOA)

Untuk integrasi dengan sistem eksternal (BPJS, Satu Sehat), digunakan pendekatan **Service-Oriented Architecture**:

- **BPJS Service:** Menangani seluruh komunikasi dengan API BPJS (VClaim, Antrol, Aplicares, P-Care).
- **Satu Sehat Service:** Menangani registrasi aplikasi, OAuth2, FHIR resource operations, iCare.
- **Logging & Monitoring:** Setiap request/response ke sistem eksternal dicatat di tabel `integration_logs` untuk audit trail dan debugging.
- **Retry & Fallback:** Menggunakan Laravel Queue dengan retry mechanism untuk request yang gagal.
- **Circuit Breaker:** Implementasi sederhana dengan mencatat jumlah kegagalan berurutan dan menghentikan sementara request jika threshold terlampaui.

---

## 3. Modul & Fitur

### A. Manajemen Pasien & Antrean

#### A.1 Master Pasien

| Fitur                           | Deskripsi                                                                 |
| ------------------------------- | ------------------------------------------------------------------------- |
| Registrasi Pasien Baru          | Input data pasien lengkap: nama, NIK, tempat/tanggal lahir, alamat, no. HP, pekerjaan, golongan darah, alergi, kontak darurat. Nomor Rekam Medis (RM) digenerate otomatis dengan format `RM-{tahun}-{nomor_urut}` |
| Pasien Lama                     | Pencarian berdasarkan nomor RM, NIK, atau nama. Update data jika diperlukan |
| Manajemen Identitas             | Foto pasien, identitas ganda (BPJS, KTP, SIM), status kawin, agama, suku |
| Riwayat Kunjungan               | Menampilkan seluruh history kunjungan pasien                              |
| Status Pasien                   | Aktif / Non-aktif / Meninggal / Pindah                                   |
| Export Data                     | Export data pasien ke Excel / CSV untuk pelaporan                        |

#### A.2 Sistem Antrean

| Fitur                        | Deskripsi                                                               |
| ---------------------------- | ----------------------------------------------------------------------- |
| Pembuatan Nomor Antrean      | Nomor antrean digenerate per poli, dengan format `{kode_poli}-{nomor_urut}` (contoh: `UMUM-001`, `GIGI-005`). Otomatis saat registrasi |
| Pencetakan Tiket Antrean     | Cetak via thermal printer (ESC/POS). Format tiket: nama, nomor antrean, poli, tanggal, jam. Implementasi menggunakan raw socket atau library `mike42/escpos-php` |
| Pemanggilan Antrean          | Petugas memanggil nomor antrean melalui sistem. Nomor ditampilkan di display TV dan di-voice-kan via TTS |
| Display Antrean (TV Monitor) | Tampilan real-time di TV/LED monitor yang menunjukkan: nomor sekarang, nomor antrean, nama poli, status (dilayani/menunggu). Update via WebSocket atau polling periodik |
| Voice Call System (TTS)      | Memanfaatkan TTS engine (Google TTS API, atau server lokal menggunakan eSpeak/SAPI). Kalimat default: "Nomor antrean {nomor}, silakan menuju ke {poli}. Nomor antrean {nomor}" |
| Status Antrean               | Pipeline status: `menunggu` -> `dipanggil` -> `dalam_pemeriksaan` -> `selesai` -> `tidak_hadir` |
| Laporan Antrean Harian       | Rekap jumlah pasien per poli, rata-rata waktu tunggu, jumlah tidak hadir |

#### A.3 Poli Klinik

| Fitur             | Deskripsi                                                 |
| ----------------- | --------------------------------------------------------- |
| Master Poli       | Data poli: kode, nama, lokasi, jam operasional            |
| Dokter Per Poli   | Assign dokter ke poli tertentu dengan jadwal praktek      |
| Kuota Pasien      | Maksimal pasien per hari per poli, per sesi (pagi/siang)  |

### B. Rekam Medis Elektronik (RME)

#### B.1 Pencatatan Rekam Medis

| Fitur                        | Deskripsi                                                                              |
| ---------------------------- | -------------------------------------------------------------------------------------- |
| Template SOAP                | Form input rekam medis berbasis SOAP (Subjective, Objective, Assessment, Plan)         |
| Subjective (S)               | Anamnesis: keluhan utama, riwayat penyakit sekarang, riwayat penyakit dahulu, riwayat keluarga, riwayat alergi |
| Objective (O)                | Pemeriksaan fisik: tanda vital (TD, nadi, suhu, RR), pemeriksaan status generalis, status lokalis |
| Assessment (A)               | Diagnosa kerja & diagnosa banding. Terintegrasi dengan ICD-10                         |
| Plan (P)                     | Rencana terapi: obat, tindakan, laboratorium, rujukan, edukasi                        |
| ICD-10 Integration           | Pencarian kode ICD-10 dengan autocomplete. Menampilkan nama diagnosa dalam Bahasa Indonesia & Inggris |
| Riwayat Berobat Pasien       | Tampilan kronologis seluruh kunjungan pasien. Akses langsung ke detail RME per tanggal |
| Dokumen Attachment           | Upload file hasil lab, rontgen, foto (format: PDF, JPG, PNG, DICOM). Preview inline   |
| Tanda Tangan Digital         | Opsional: verifikasi RME dengan tanda tangan digital dokter                            |
| Cetak Rekam Medis            | Export RME ke PDF untuk keperluan administratif                                        |

#### B.2 Master Data Medis

| Fitur               | Deskripsi                                                      |
| ------------------- | -------------------------------------------------------------- |
| ICD-10 Diagnosa     | Database kode ICD-10 (WHO versi Indonesia). Import dari CSV/JSON. Pencarian berdasarkan kode atau deskripsi |
| ICD-9 CM Tindakan   | Kode tindakan medis (ICD-9 CM)                                 |
| Template Diagnosa   | Template diagnosa cepat untuk kondisi umum                     |
| Master Tindakan     | Daftar tindakan medis yang tersedia di klinik, termasuk tarif   |

### C. Manajemen Apotek & Inventaris

#### C.1 Resep Obat

| Fitur                  | Deskripsi                                                                      |
| ---------------------- | ------------------------------------------------------------------------------ |
| Input Resep            | Dokter menulis resep dari modul RME. Pilih obat, dosis, rute, frekuensi, durasi, jumlah, catatan |
| Resep Racikan          | Resep dengan obat racikan (puyer, kapsul racikan). Kandungan per obat racikan  |
| Validasi Interaksi     | Validasi interaksi obat (drug-drug interaction) sederhana. Peringatan jika ada kontraindikasi |
| Status Resep           | `menunggu` -> `diproses` -> `selesai` -> `dibatalkan`                         |
| Cetak Resep            | Print resep ke kertas resep standar                                            |
| Riwayat Resep Pasien   | Riwayat seluruh resep yang pernah diberikan ke pasien                          |
| Copy Resep             | Duplikasi resep untuk pasien kontrol                                           |

#### C.2 Manajemen Stok Obat

| Fitur                    | Deskripsi                                                                          |
| ------------------------ | ---------------------------------------------------------------------------------- |
| Master Obat              | Data obat: nama generik, nama dagang, golongan (obat keras/bebas/narkotik), pabrik, kekuatan, satuan, kategori terapi |
| Kategori Obat            | Klasifikasi: Analgesik, Antibiotik, Antihipertensi, dll                           |
| Batch & Expired Date     | Setiap penerimaan barang dicatat per batch dengan tanggal kadaluwarsa              |
| Stok Minimum             | Batas stok minimum untuk peringatan (low stock alert)                              |
| Low Stock Alert          | Notifikasi otomatis jika stok suatu obat di bawah batas minimum. Dashboard & email |
| Peringatan Kedaluwarsa   | Daftar obat yang akan kadaluwarsa dalam 30/60/90 hari ke depan. Notifikasi        |
| Supplier Management      | Data supplier: nama, kontak, alamat, terms pembayaran                              |
| Mutasi Stok              | Transaksi: stok masuk (pembelian, retur), stok keluar (penjualan, disposisi, kadaluwarsa), stok opname (stok opname) |
| Stok Opname              | Proses fisik penghitungan stok. Catat selisih (discrepancy) dan sesuaikan database |
| Laporan Stok             | Laporan: kartu stok, mutasi stok, stok opname, stok kadaluwarsa                    |

#### C.3 Penjualan Obat (Non-Resep / Bebas)

| Fitur               | Deskripsi                                                          |
| ------------------- | ------------------------------------------------------------------ |
| Penjualan Bebas     | Penjualan obat bebas tanpa resep. Input obat -> hitung total -> pembayaran |
| Retur Penjualan     | Proses retur obat dari pasien                                       |

### D. Integrasi BPJS Kesehatan

#### D.1 VClaim API

| Fitur                     | Deskripsi                                                                                      |
| ------------------------- | ---------------------------------------------------------------------------------------------- |
| SEP (Surat Eligibilitas Peserta) | Generate/manage SEP: `SEP.Insert`, `SEP.Update`, `SEP.Delete`, `SEP.Status`. Format data sesuai standar BPJS |
| Pengajuan Klaim           | Submit data klaim rawat jalan & rawat inap ke BPJS. Format data INACBG's Group                  |
| Monitoring Klaim          | Cek status klaim: `Monitoring.Klaim.JS` (by no. SEP, by tanggal, by bulan)                    |
| Rujukan                   | `Rujukan.Insert`, `Rujukan.Update`, `Rujukan.Delete`, `Rujukan.Status`                         |
| Peserta                   | `Peserta.nik`, `Peserta.noka` — verifikasi data kepesertaan                                   |
| Laporan                   | `Laporan.Pendaftaran`, `Laporan.Pelayanan`, `Laporan.PenggantianBiaya`                        |

#### D.2 Antrean BPJS (Antrol)

| Fitur                         | Deskripsi                                                                      |
| ----------------------------- | ------------------------------------------------------------------------------ |
| Sinkronasi Jadwal BPJS        | Tarik jadwal praktik dokter dari BPJS Antrol                                   |
| Sinkronasi Nomor Antrean BPJS | Kirim data nomor antrean pasien BPJS ke Antrol. Ambil nomor antrean dari server BPJS |
| Dashboard Antrean BPJS        | Monitoring ketersediaan kuota antrean BPJS                                     |

#### D.3 Aplicares & P-Care

| Fitur               | Deskripsi                                            |
| ------------------- | ---------------------------------------------------- |
| Referensi Faskes    | Pencarian faskes rujukan via Aplicares API           |
| P-Care Integration  | Integrasi dengan P-Care untuk data kapitasi & kunjungan |

### F. Manajemen Laboratorium

#### F.1 Master Data Lab

| Fitur                       | Deskripsi                                                                 |
| --------------------------- | ------------------------------------------------------------------------- |
| Kategori Pemeriksaan Lab    | Kategorisasi tes (Hematologi, Kimia Darah, Urinalisis, Mikrobiologi, Imunologi) |
| Master Tes Laboratorium     | Kode, nama, jenis spesimen, satuan, nilai rujukan (rendah/tinggi/teks), harga, kode LOINC, filter gender & usia |
| Nilai Rujukan               | Reference range per tes, bisa berbeda berdasarkan jenis kelamin dan usia  |
| LOINC Mapping               | Kode LOINC untuk interoperabilitas Satu Sehat FHIR Observation            |

#### F.2 Permintaan Laboratorium

| Fitur                       | Deskripsi                                                                 |
| --------------------------- | ------------------------------------------------------------------------- |
| Permintaan Lab dari RME     | Dokter memilih tes lab dari form permintaan, terhubung ke Rekam Medis      |
| Multi-item Request          | Satu permintaan bisa berisi beberapa jenis tes lab sekaligus              |
| Status Pipeline             | `requested` → `sampled` → `processing` → `completed` / `cancelled`       |
| Filter & Pencarian          | Berdasarkan pasien (nama/No.RM) dan status permintaan                     |

#### F.3 Hasil Pemeriksaan

| Fitur                       | Deskripsi                                                                 |
| --------------------------- | ------------------------------------------------------------------------- |
| Input Hasil Massal          | Input hasil semua item dalam satu permintaan sekaligus                    |
| Nilai Rujukan Otomatis      | Nilai rujukan dari master tes terisi otomatis saat input hasil            |
| Flag Status                 | Normal / Abnormal / Critical / Not Tested, dengan warna badge             |
| Edit Hasil                  | Koreksi hasil yang sudah diinput, tercatat pemeriksa & waktu              |
| Filter Hasil                | Berdasarkan tes lab, flag, dan pencarian pasien                           |

#### F.4 Role & Akses

| Role       | Kategori Tes | Master Tes | Permintaan Lab | Hasil Lab |
| ---------- | :----------: | :--------: | :------------: | :-------: |
| admin      | ✓            | ✓          | ✓              | ✓         |
| doctor     | —            | —          | ✓ (buat/lihat) | ✓ (lihat) |
| laborant   | ✓            | ✓          | ✓ (proses)     | ✓ (input) |

---

### E. Integrasi Satu Sehat (Kemenkes)

#### E.1 FHIR Standard

| Fitur                    | Deskripsi                                                                 |
| ------------------------ | ------------------------------------------------------------------------- |
| FHIR R4                  | Implementasi FHIR Release 4 (R4). Dukungan terbatas untuk DSTU2 jika diperlukan |
| Resource Operations      | CRUD untuk resource: Patient, Encounter, Condition, Observation, MedicationRequest, Organization, Practitioner |
| Format Data              | JSON. Mapping dari data internal e-Klinik ke struktur FHIR resource       |

#### E.2 Autentikasi OAuth2

| Fitur                      | Deskripsi                                                              |
| -------------------------- | ---------------------------------------------------------------------- |
| Client Credentials Grant   | Dapatkan access token menggunakan client_id dan client_private_key     |
| Token Management           | Cache & refresh token. Handle expired token auto-refresh               |
| KYC Registration (IST)     | Registrasi aplikasi via portal Izin Sehat (IST) Kemenkes. Upload sertifikat digital |

#### E.3 Resource FHIR

| Resource             | Operasi         | Keterangan                                                    |
| -------------------- | --------------- | ------------------------------------------------------------- |
| Patient              | CRUD            | Data pasien. Mapping dari tabel `patients`                    |
| Encounter            | CRUD            | Kunjungan pasien. Mapping dari tabel `medical_records`        |
| Condition            | CRUD            | Diagnosa. Mapping dari tabel `medical_record_details` dgn ICD-10 |
| Observation          | CRUD            | Tanda vital, hasil lab. Mapping dari tabel `observations`     |
| MedicationRequest    | CRUD            | Resep obat. Mapping dari tabel `prescriptions` + `prescription_items` |
| Organization         | CRUD            | Data klinik/faskes. Mapping dari konfigurasi sistem           |
| Practitioner         | CRUD            | Data dokter/tenaga kesehatan. Mapping dari tabel `doctors` / `users` |

#### E.4 iCare Integration

| Fitur                  | Deskripsi                                                                 |
| ---------------------- | ------------------------------------------------------------------------- |
| iCare Consumer         | Konsumsi data riwayat medis pasien dari faskes lain via iCare API         |
| iCare Summary          | Mendapatkan ringkasan riwayat kesehatan pasien (obat, diagnosa, alergi)   |
| Data Consent           | Manajemen persetujuan pasien untuk pertukaran data                        |

#### E.5 Terminology

| Terminologi      | Keterangan                                                  |
| ---------------- | ----------------------------------------------------------- |
| ICD-10           | Diagnosa klinis. Sudah ada di database lokal                |
| LOINC           | Kode pemeriksaan laboratorium. Mapping untuk Observation    |
| KFA (Kode Obat)  | Kode obat nasional. Mapping untuk MedicationRequest         |
| INA-CBG          | Kode grouping untuk klaim BPJS                              |

---

## 4. Struktur Database

### 4.1 Diagram Relasi (Deskripsi)

Berikut adalah daftar tabel utama beserta kolom-kolom kunci dan relasi antar tabel.

### 4.2 Tabel Master & Referensi

#### `users`

| Kolom            | Tipe          | Keterangan                               |
| ---------------- | ------------- | ---------------------------------------- |
| id               | bigint (PK)   | Auto increment                           |
| username         | varchar(100)  | Unique login                             |
| email            | varchar(255)  | Unique                                   |
| password         | varchar(255)  | Bcrypt hash                              |
| nama_lengkap     | varchar(255)  | Nama user                                |
| nip              | varchar(50)   | Nomor Induk Pegawai (nullable)           |
| role             | enum          | admin, dokter, perawat, apoteker, kasir, pendaftaran |
| polyclinic_id    | bigint (FK)   | Relasi ke polyclinics (nullable)         |
| phone            | varchar(20)   | No. HP                                   |
| is_active        | boolean       | Status aktif                             |
| foto             | varchar(255)  | Path foto (nullable)                     |
| email_verified_at| timestamp     | Nullable                                 |
| remember_token   | varchar(100)  |                                          |
| created_at       | timestamp     |                                          |
| updated_at       | timestamp     |                                          |
| deleted_at       | timestamp     | Soft delete                              |

`Index:` unique pada `username`, `email`

#### `patients`

| Kolom            | Tipe          | Keterangan                                    |
| ---------------- | ------------- | --------------------------------------------- |
| id               | bigint (PK)   | Auto increment                                |
| no_rm            | varchar(20)   | Nomor Rekam Medis unik (format: RM-tahun-xxxx) |
| nik              | varchar(16)   | NIK KTP (unique)                              |
| nama_lengkap     | varchar(255)  |                                               |
| tempat_lahir     | varchar(100)  |                                               |
| tanggal_lahir    | date          |                                               |
| jenis_kelamin    | enum          | L / P                                         |
| golongan_darah   | enum          | A, B, AB, O (nullable)                        |
| alamat           | text          |                                               |
| provinsi         | varchar(100)  |                                               |
| kabupaten        | varchar(100)  |                                               |
| kecamatan        | varchar(100)  |                                               |
| kelurahan        | varchar(100)  |                                               |
| rt_rw            | varchar(20)   |                                               |
| kode_pos         | varchar(10)   |                                               |
| no_hp            | varchar(20)   |                                               |
| no_telp          | varchar(20)   | (nullable)                                    |
| email            | varchar(255)  |                                               |
| pekerjaan        | varchar(100)  |                                               |
| agama            | enum          | Islam, Kristen, Katolik, Hindu, Buddha, Konghucu, Lainnya |
| status_kawin     | enum          | Belum Kawin, Kawin, Cerai Hidup, Cerai Mati   |
| suku             | varchar(50)   |                                               |
| nama_ibu         | varchar(255)  | Nama ibu kandung                              |
| kontak_darurat   | varchar(255)  | Nama & no HP kontak darurat                   |
| alergi           | text          | Riwayat alergi (nullable)                     |
| foto             | varchar(255)  |                                               |
| status           | enum          | aktif, nonaktif, meninggal, pindah            |
| is_bpjs          | boolean       | Apakah pasien BPJS                            |
| created_at       | timestamp     |                                               |
| updated_at       | timestamp     |                                               |
| deleted_at       | timestamp     | Soft delete                                   |

`Index:` unique pada `no_rm`, `nik`; index pada `nama_lengkap`, `tanggal_lahir`, `no_hp`

#### `polyclinics`

| Kolom       | Tipe          | Keterangan                         |
| ----------- | ------------- | ---------------------------------- |
| id          | bigint (PK)   |                                    |
| kode        | varchar(10)   | Unique, contoh: UMUM, GIGI, MATA   |
| nama        | varchar(255)  | Nama poli                          |
| lokasi      | varchar(255)  | Ruangan / lantai                   |
| jam_buka    | time          |                                    |
| jam_tutup   | time          |                                    |
| kuota_pagi  | int           | Kuota pasien sesi pagi             |
| kuota_siang | int           | Kuota pasien sesi siang            |
| is_active   | boolean       |                                    |
| created_at  | timestamp     |                                    |
| updated_at  | timestamp     |                                    |
| deleted_at  | timestamp     |                                    |

#### `doctors`

| Kolom          | Tipe          | Keterangan                                      |
| -------------- | ------------- | ----------------------------------------------- |
| id             | bigint (PK)   |                                                 |
| user_id        | bigint (FK)   | Relasi ke users (nullable, jika user login)      |
| kode_dokter    | varchar(20)   | Unique                                          |
| nama_dokter    | varchar(255)  |                                                 |
| sip            | varchar(100)  | Nomor Surat Izin Praktek                        |
| str            | varchar(100)  | Nomor STR                                       |
| spesialisasi   | varchar(255)  |                                                 |
| no_hp          | varchar(20)   |                                                 |
| email          | varchar(255)  |                                                 |
| poli_default   | bigint (FK)   | Relasi ke polyclinics (poli utama)               |
| is_active      | boolean       |                                                 |
| created_at     | timestamp     |                                                 |
| updated_at     | timestamp     |                                                 |
| deleted_at     | timestamp     |                                                 |

#### `doctor_schedules`

| Kolom       | Tipe          | Keterangan                                   |
| ----------- | ------------- | -------------------------------------------- |
| id          | bigint (PK)   |                                              |
| doctor_id   | bigint (FK)   | Relasi ke doctors                            |
| polyclinic_id| bigint (FK)  | Relasi ke polyclinics                        |
| hari        | enum          | Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu |
| jam_mulai   | time          |                                              |
| jam_selesai | time          |                                              |
| kuota       | int           | Kuota pasien per sesi                        |
| created_at  | timestamp     |                                              |
| updated_at  | timestamp     |                                              |

#### `icd10_diagnoses`

| Kolom        | Tipe          | Keterangan                              |
| ------------ | ------------- | --------------------------------------- |
| id           | bigint (PK)   |                                         |
| kode         | varchar(10)   | Unique, contoh: A00, B20, I10           |
| nama_indo    | text          | Nama diagnosa dalam Bahasa Indonesia    |
| nama_eng     | text          | Nama diagnosa dalam Bahasa Inggris      |
| kategori     | varchar(50)   | Kategori penyakit                       |
| is_active    | boolean       |                                         |
| created_at   | timestamp     |                                         |
| updated_at   | timestamp     |                                         |

`Index:` unique pada `kode`; fulltext index pada `nama_indo`, `nama_eng`

### 4.3 Tabel Transaksi

#### `queues`

| Kolom          | Tipe          | Keterangan                                    |
| -------------- | ------------- | --------------------------------------------- |
| id             | bigint (PK)   |                                               |
| patient_id     | bigint (FK)   | Relasi ke patients                            |
| polyclinic_id  | bigint (FK)   | Relasi ke polyclinics                         |
| doctor_id      | bigint (FK)   | Relasi ke doctors (nullable)                  |
| no_antrean     | varchar(20)   | Format: POLI-XXX                              |
| tanggal        | date          | Tanggal antrean                               |
| sesi           | enum          | pagi, siang                                   |
| status         | enum          | menunggu, dipanggil, dalam_pemeriksaan, selesai, tidak_hadir, batal |
| sumber         | enum          | langsung, bpjs_antrol                         |
| no_sep         | varchar(50)   | Nomor SEP BPJS (nullable)                     |
| keluhan        | text          | Keluhan awal (nullable)                       |
| waktu_daftar   | datetime      |                                               |
| waktu_panggil  | datetime      | (nullable)                                    |
| waktu_mulai    | datetime      | Mulai periksa (nullable)                      |
| waktu_selesai  | datetime      | Selesai periksa (nullable)                    |
| dipanggil_ke   | int           | Jumlah pemanggilan (default 0)                |
| created_at     | timestamp     |                                               |
| updated_at     | timestamp     |                                               |

`Index:` index pada `tanggal`, `polyclinic_id`, `status`; unique constraint (`tanggal`, `no_antrean`)

#### `medical_records`

| Kolom          | Tipe          | Keterangan                                  |
| -------------- | ------------- | ------------------------------------------- |
| id             | bigint (PK)   |                                             |
| patient_id     | bigint (FK)   | Relasi ke patients                          |
| queue_id       | bigint (FK)   | Relasi ke queues (nullable)                 |
| doctor_id      | bigint (FK)   | Relasi ke doctors                           |
| polyclinic_id  | bigint (FK)   | Relasi ke polyclinics                       |
| tanggal        | datetime      | Tanggal & jam pemeriksaan                   |
| keluhan_utama  | text          | (Subjective)                               |
| riwayat_penyakit| text         | (Subjective)                               |
| pemeriksaan_fisik| text        | (Objective) tanda vital & status           |
| diagnosa_kerja | text          | (Assessment) simpan sebagai JSON array [{icd10_code, icd10_name, note}] |
| diagnosa_banding| text         | (Assessment) JSON array                    |
| rencana_tindakan| text         | (Plan)                                      |
| catatan_dokter | text          | (Plan)                                      |
| status         | enum          | draft, final, ditandatangani                |
| signed_by      | bigint (FK)   | Dokter yang menandatangani (nullable)       |
| signed_at      | timestamp     | (nullable)                                  |
| created_at     | timestamp     |                                             |
| updated_at     | timestamp     |                                             |
| deleted_at     | timestamp     |                                             |

`Index:` index pada `patient_id`, `tanggal`, `doctor_id`

#### `medical_record_details`

| Kolom            | Tipe          | Keterangan                                        |
| ---------------- | ------------- | ------------------------------------------------- |
| id               | bigint (PK)   |                                                   |
| medical_record_id| bigint (FK)   | Relasi ke medical_records                         |
| tipe             | enum          | diagnosa, tindakan, observasi, catatan            |
| icd10_kode       | varchar(10)   | Kode ICD-10 (nullable)                            |
| icd10_nama       | varchar(255)  | Nama diagnosa (nullable)                          |
| icd9_kode        | varchar(10)   | Kode ICD-9 (nullable)                             |
| deskripsi        | text          | Deskripsi detail                                  |
| catatan          | text          | Catatan tambahan                                  |
| created_at       | timestamp     |                                                   |
| updated_at       | timestamp     |                                                   |

#### `attachments`

| Kolom            | Tipe          | Keterangan             |
| ---------------- | ------------- | ---------------------- |
| id               | bigint (PK)   |                        |
| medical_record_id| bigint (FK)   |                        |
| patient_id       | bigint (FK)   |                        |
| nama_file        | varchar(255)  |                        |
| path             | varchar(255)  |                        |
| tipe_file        | varchar(50)   | PDF, JPG, PNG, DICOM   |
| ukuran           | int           | Bytes                  |
| kategori         | enum          | hasil_lab, rontgen, foto, lainnya |
| created_at       | timestamp     |                        |

### 4.4 Tabel Apotek & Inventaris

#### `medicine_categories`

| Kolom      | Tipe          | Keterangan      |
| ---------- | ------------- | --------------- |
| id         | bigint (PK)   |                 |
| nama       | varchar(255)  | Unik            |
| deskripsi  | text          |                 |
| created_at | timestamp     |                 |
| updated_at | timestamp     |                 |

#### `medicines`

| Kolom            | Tipe          | Keterangan                                    |
| ---------------- | ------------- | --------------------------------------------- |
| id               | bigint (PK)   |                                               |
| kode_obat        | varchar(20)   | Unique                                        |
| kfa_code         | varchar(20)   | Kode KFA nasional (nullable, untuk Satu Sehat)|
| nama_generik     | varchar(255)  |                                               |
| nama_dagang      | varchar(255)  |                                               |
| kategori_id      | bigint (FK)   | Relasi ke medicine_categories                 |
| golongan         | enum          | obat_bebas, obat_bebas_terbatas, obat_keras, narkotika, psikotropika |
| bentuk_sediaan   | varchar(100)  | Tablet, Kapsul, Sirup, Salep, Injeksi, dll    |
| kekuatan         | varchar(50)   | 500 mg, 10 mg/ml, dll                         |
| satuan           | varchar(20)   | Tablet, Botol, Tube, Ampul, dll               |
| pabrik           | varchar(255)  | Nama pabrik farmasi                           |
| harga_beli       | decimal(12,2) | Harga beli rata-rata                          |
| harga_jual       | decimal(12,2) | Harga jual                                    |
| stok_minimum     | int           | Batas stok minimum                            |
| stok_saat_ini    | int           | Stok real-time (di-update via trigger/event)  |
| is_active        | boolean       |                                               |
| keterangan       | text          |                                               |
| created_at       | timestamp     |                                               |
| updated_at       | timestamp     |                                               |
| deleted_at       | timestamp     |                                               |

`Index:` unique pada `kode_obat`, index pada `nama_generik`, `nama_dagang`, `kfa_code`

#### `suppliers`

| Kolom      | Tipe          | Keterangan          |
| ---------- | ------------- | ------------------- |
| id         | bigint (PK)   |                     |
| kode       | varchar(20)   | Unique              |
| nama       | varchar(255)  |                     |
| alamat     | text          |                     |
| no_hp      | varchar(20)   |                     |
| email      | varchar(255)  |                     |
| kontak_person| varchar(255) |                   |
| terms_pembayaran| varchar(100) | Net 30, Cash, dll |
| is_active  | boolean       |                     |
| created_at | timestamp     |                     |
| updated_at | timestamp     |                     |

#### `prescriptions`

| Kolom            | Tipe          | Keterangan                                  |
| ---------------- | ------------- | ------------------------------------------- |
| id               | bigint (PK)   |                                             |
| medical_record_id| bigint (FK)   | Relasi ke medical_records (nullable)        |
| patient_id       | bigint (FK)   | Relasi ke patients                          |
| doctor_id        | bigint (FK)   | Dokter penulis resep                        |
| no_resep         | varchar(20)   | Unique, format: RCP-tahun-xxxx              |
| tanggal          | date          |                                             |
| status           | enum          | menunggu, diproses, selesai, dibatalkan     |
| catatan          | text          | Catatan umum resep                          |
| created_at       | timestamp     |                                             |
| updated_at       | timestamp     |                                             |
| deleted_at       | timestamp     |                                             |

`Index:` unique pada `no_resep`

#### `prescription_items`

| Kolom          | Tipe          | Keterangan                           |
| -------------- | ------------- | ------------------------------------ |
| id             | bigint (PK)   |                                      |
| prescription_id| bigint (FK)   | Relasi ke prescriptions              |
| medicine_id    | bigint (FK)   | Relasi ke medicines                  |
| batch_id       | bigint (FK)   | Relasi ke inventory_batches (nullable) |
| jumlah         | int           | Jumlah diberikan                     |
| dosis          | varchar(100)  | Contoh: 3x1 tablet                   |
| rute           | varchar(50)   | Oral, Topikal, Injeksi, dll          |
| frekuensi      | varchar(50)   | 3x sehari, 2x sehari, dll            |
| durasi         | varchar(50)   | 5 hari, 7 hari, dll                  |
| catatan        | text          | Catatan penggunaan                   |
| subtotal       | decimal(12,2) | Harga * jumlah                       |
| created_at     | timestamp     |                                      |
| updated_at     | timestamp     |                                      |

#### `inventories`

| Kolom          | Tipe          | Keterangan                                |
| -------------- | ------------- | ----------------------------------------- |
| id             | bigint (PK)   |                                           |
| medicine_id    | bigint (FK)   | Relasi ke medicines                       |
| batch_no       | varchar(100)  | Nomor batch dari pabrik                   |
| expired_date   | date          | Tanggal kadaluwarsa                       |
| stok_awal      | int           | Stok awal di batch ini                    |
| stok_sisa      | int           | Stok tersisa                              |
| harga_beli     | decimal(12,2) | Harga beli per satuan untuk batch ini     |
| supplier_id    | bigint (FK)   | Relasi ke suppliers (nullable)            |
| tanggal_masuk  | date          |                                           |
| is_active      | boolean       |                                           |
| created_at     | timestamp     |                                           |
| updated_at     | timestamp     |                                           |
| deleted_at     | timestamp     |                                           |

`Index:` index pada `medicine_id`, `batch_no`, `expired_date`

#### `inventory_transactions`

| Kolom          | Tipe          | Keterangan                         |
| -------------- | ------------- | ---------------------------------- |
| id             | bigint (PK)   |                                    |
| medicine_id    | bigint (FK)   |                                    |
| batch_id       | bigint (FK)   | Relasi ke inventories (nullable)   |
| tipe           | enum          | masuk, keluar, opname              |
| referensi_tipe | varchar(50)   | pembelian, penjualan, disposisi, stok_opname, retur |
| referensi_id   | bigint        | ID dari tabel referensi            |
| jumlah         | int           | Positif untuk masuk, negatif untuk keluar (atau gunakan kolom arah) |
| arah           | enum          | in, out                            |
| stok_sebelum   | int           | Stok sebelum transaksi             |
| stok_sesudah   | int           | Stok setelah transaksi             |
| keterangan     | text          |                                    |
| created_by     | bigint (FK)   | Relasi ke users                    |
| created_at     | timestamp     |                                    |

`Index:` index pada `medicine_id`, `batch_id`, `tipe`, `created_at`

### 4.5 Tabel BPJS

#### `bpjs_patients`

| Kolom           | Tipe          | Keterangan                          |
| --------------- | ------------- | ----------------------------------- |
| id              | bigint (PK)   |                                     |
| patient_id      | bigint (FK)   | Relasi ke patients                  |
| no_kartu        | varchar(50)   | No. Kartu BPJS (bisa beda dengan NIK) |
| no_sep          | varchar(50)   | SEP aktif terakhir (nullable)       |
| jenis_peserta   | varchar(50)   | PBI, Non-PBI, PPU, dll              |
| kelas_rawat     | enum          | 1, 2, 3                             |
| faskes_tingkat  | int           | 1 (FKTP) atau 2 (FKRTL)            |
| status          | enum          | aktif, nonaktif                     |
| created_at      | timestamp     |                                     |
| updated_at      | timestamp     |                                     |

#### `bpjs_claims`

| Kolom           | Tipe          | Keterangan                            |
| --------------- | ------------- | ------------------------------------- |
| id              | bigint (PK)   |                                       |
| patient_id      | bigint (FK)   |                                       |
| sep_no          | varchar(50)   | No. SEP                               |
| tipe_klaim      | enum          | rawat_jalan, rawat_inap               |
| tanggal_masuk   | date          | Tanggal pelayanan                     |
| diagnosa_utama  | varchar(10)   | Kode ICD-10                           |
| diagnosa_sekunder| text         | JSON array ICD-10 codes               |
| prosedur        | text          | JSON array ICD-9 codes                |
| tarif_rs        | decimal(12,2) |                                       |
| tarif_bpjs      | decimal(12,2) |                                       |
| inacbg_code     | varchar(50)   | Kode INA-CBG                          |
| inacbg_desc     | varchar(255)  | Deskripsi INA-CBG                     |
| status          | enum          | draft, submitted, validated, pending, paid, rejected, revised |
| status_desc     | text          | Keterangan status dari BPJS           |
| tgl_submit      | datetime      |                                       |
| tgl_validasi    | datetime      |                                       |
| response_bpjs   | json          | Raw response dari BPJS API            |
| created_at      | timestamp     |                                       |
| updated_at      | timestamp     |                                       |

#### `bpjs_referrals`

| Kolom           | Tipe          | Keterangan                        |
| --------------- | ------------- | --------------------------------- |
| id              | bigint (PK)   |                                   |
| patient_id      | bigint (FK)   |                                   |
| no_rujukan      | varchar(50)   | No. rujukan BPJS                  |
| tipe_rujukan    | enum          | internal, antar_faskes            |
| faskes_asal     | varchar(255)  |                                   |
| faskes_tujuan   | varchar(255)  |                                   |
| diagnosa        | text          |                                   |
| tgl_rujukan     | date          |                                   |
| status          | enum          | aktif, digunakan, expired         |
| response_bpjs   | json          |                                   |
| created_at      | timestamp     |                                   |
| updated_at      | timestamp     |                                   |

#### `bpjs_antrean`

| Kolom              | Tipe          | Keterangan                          |
| ------------------ | ------------- | ----------------------------------- |
| id                 | bigint (PK)   |                                     |
| patient_id         | bigint (FK)   |                                     |
| queue_id           | bigint (FK)   | Relasi ke queues (nullable)         |
| kode_poli_bpjs     | varchar(50)   | Kode poli dari BPJS                 |
| no_antrean_bpjs    | varchar(20)   | No. antrean dari BPJS               |
| no_sep             | varchar(50)   |                                     |
| tanggal            | date          |                                     |
| sesi               | enum          | pagi, siang                         |
| status_sinkron     | enum          | pending, sukses, gagal              |
| response_bpjs      | json          |                                     |
| created_at         | timestamp     |                                     |

#### `bpjs_jadwal`

| Kolom          | Tipe          | Keterangan                          |
| -------------- | ------------- | ----------------------------------- |
| id             | bigint (PK)   |                                     |
| kode_poli      | varchar(50)   | Kode poli dari BPJS                 |
| nama_poli      | varchar(255)  |                                     |
| kode_dokter    | varchar(50)   | Kode dokter dari BPJS               |
| nama_dokter    | varchar(255)  |                                     |
| hari           | int           | 1=Senin .. 7=Minggu                 |
| jam_mulai      | time          |                                     |
| jam_selesai    | time          |                                     |
| kuota          | int           |                                     |
| created_at     | timestamp     |                                     |
| updated_at     | timestamp     |                                     |

### 4.6 Tabel Satu Sehat

#### `satusehat_resources`

| Kolom           | Tipe          | Keterangan                            |
| --------------- | ------------- | ------------------------------------- |
| id              | bigint (PK)   |                                       |
| resource_type   | varchar(50)   | Patient, Encounter, Condition, dll    |
| resource_id     | varchar(100)  | ID dari FHIR server                   |
| reference_id    | bigint        | ID dari tabel internal                |
| reference_type  | varchar(100)  | Nama model internal                   |
| payload_request | json          | JSON yang dikirim ke FHIR server      |
| payload_response| json          | JSON respons dari FHIR server         |
| status          | enum          | created, updated, deleted, failed     |
| status_code     | int           | HTTP status code                      |
| version         | int           | Version resource                     |
| created_at      | timestamp     |                                       |
| updated_at      | timestamp     |                                       |

`Index:` index pada `resource_type`, `resource_id`, `reference_id`

#### `satusehat_logs`

| Kolom           | Tipe          | Keterangan                   |
| --------------- | ------------- | ---------------------------- |
| id              | bigint (PK)   |                              |
| resource_type   | varchar(50)   |                              |
| action          | varchar(20)   | create, read, update, delete |
| url             | varchar(500)  | FHIR endpoint URL            |
| request_body    | json          |                              |
| response_body   | json          |                              |
| status_code     | int           |                              |
| error_message   | text          |                              |
| duration_ms     | int           | Durasi request               |
| created_by      | bigint (FK)   | Relasi ke users              |
| created_at      | timestamp     |                              |

### 4.7 Tabel Laboratorium

#### `lab_test_categories`

| Kolom       | Tipe          | Keterangan                      |
| ----------- | ------------- | ------------------------------- |
| id          | bigint (PK)   | Auto increment                  |
| code        | varchar(20)   | Unique, contoh: HEMATOLOGI      |
| name        | varchar(255)  | Nama kategori                   |
| description | text          | Deskripsi (nullable)            |
| is_active   | boolean       | Status aktif                    |
| created_at  | timestamp     |                                 |
| updated_at  | timestamp     |                                 |

#### `lab_tests`

| Kolom          | Tipe          | Keterangan                              |
| -------------- | ------------- | --------------------------------------- |
| id             | bigint (PK)   |                                         |
| category_id    | bigint (FK)   | Relasi ke lab_test_categories           |
| code           | varchar(20)   | Unique, contoh: HB, GDS, SGPT           |
| name           | varchar(255)  | Nama pemeriksaan                        |
| specimen_type  | varchar(50)   | Darah, Urine, dll (nullable)            |
| unit           | varchar(30)   | g/dL, mg/dL, U/L (nullable)             |
| gender         | varchar(10)   | L/P/null (nullable, filter gender)      |
| age_min        | tinyint       | Usia minimal (nullable)                 |
| age_max        | tinyint       | Usia maksimal (nullable)                |
| ref_range_low  | varchar(50)   | Nilai rujukan rendah (nullable)         |
| ref_range_high | varchar(50)   | Nilai rujukan tinggi (nullable)         |
| ref_range_text | text          | Teks rujukan alternatif (nullable)      |
| price          | decimal(12,2) | Harga pemeriksaan                       |
| loinc_code     | varchar(20)   | Kode LOINC untuk Satu Sehat (nullable)  |
| is_active      | boolean       | Status aktif                            |
| created_by     | bigint (FK)   | Relasi ke users                         |
| created_at     | timestamp     |                                         |
| updated_at     | timestamp     |                                         |

`Index:` unique pada `code`; index pada `category_id`, `loinc_code`

#### `lab_requests`

| Kolom             | Tipe          | Keterangan                               |
| ----------------- | ------------- | ---------------------------------------- |
| id                | bigint (PK)   |                                          |
| medical_record_id | bigint (FK)   | Relasi ke medical_records                |
| patient_id        | bigint (FK)   | Relasi ke patients                       |
| doctor_id         | bigint (FK)   | Relasi ke doctors (nullable)             |
| notes             | text          | Catatan permintaan (nullable)            |
| status            | enum          | requested, sampled, processing, completed, cancelled |
| created_by        | bigint (FK)   | Relasi ke users                          |
| created_at        | timestamp     |                                          |
| updated_at        | timestamp     |                                          |

#### `lab_request_items`

| Kolom          | Tipe          | Keterangan                           |
| -------------- | ------------- | ------------------------------------ |
| id             | bigint (PK)   |                                      |
| lab_request_id | bigint (FK)   | Relasi ke lab_requests (cascade)     |
| lab_test_id    | bigint (FK)   | Relasi ke lab_tests                  |
| status         | enum          | pending, completed, cancelled        |
| created_by     | bigint (FK)   | Relasi ke users                      |
| created_at     | timestamp     |                                      |
| updated_at     | timestamp     |                                      |

`Index:` index pada `lab_request_id`, `status`

#### `lab_results`

| Kolom              | Tipe          | Keterangan                              |
| ------------------ | ------------- | --------------------------------------- |
| id                 | bigint (PK)   |                                         |
| lab_request_item_id| bigint (FK)   | Relasi ke lab_request_items (cascade)   |
| lab_test_id        | bigint (FK)   | Relasi ke lab_tests                     |
| patient_id         | bigint (FK)   | Relasi ke patients                      |
| result_value       | varchar(100)  | Nilai hasil numerik (nullable)          |
| result_text        | text          | Hasil deskriptif (nullable)             |
| ref_range_low      | varchar(50)   | Nilai rujukan rendah (nullable)         |
| ref_range_high     | varchar(50)   | Nilai rujukan tinggi (nullable)         |
| ref_range_text     | text          | Teks rujukan (nullable)                 |
| unit               | varchar(30)   | Satuan hasil (nullable)                 |
| flag               | enum          | normal, abnormal, critical, not_tested  |
| notes              | text          | Catatan pemeriksa (nullable)            |
| examined_by        | bigint (FK)   | Pemeriksa (relasi ke users, nullable)   |
| examined_at        | timestamp     | Waktu pemeriksaan (nullable)            |
| created_by         | bigint (FK)   | Relasi ke users                         |
| created_at         | timestamp     |                                         |
| updated_at         | timestamp     |                                         |

`Index:` index pada `patient_id`, `lab_test_id`, `flag`, `examined_at`

### 4.8 Tabel Logging & Konfigurasi

#### `integration_logs`

| Kolom           | Tipe          | Keterangan                       |
| --------------- | ------------- | -------------------------------- |
| id              | bigint (PK)   |                                  |
| tipe_integrasi  | varchar(50)   | bpjs_vclaim, bpjs_antrol, satusehat, aplikasi_lain |
| endpoint        | varchar(500)  |                                  |
| method          | varchar(10)   | GET, POST, PUT, DELETE           |
| request_body    | json          |                                  |
| response_body   | json          |                                  |
| status_code     | int           |                                  |
| status          | enum          | sukses, gagal                    |
| error_message   | text          |                                  |
| duration_ms     | int           |                                  |
| created_by      | bigint (FK)   |                                  |
| created_at      | timestamp     |                                  |

#### `configurations`

| Kolom     | Tipe          | Keterangan                     |
| --------- | ------------- | ------------------------------ |
| id        | bigint (PK)   |                                |
| group     | varchar(100)  | general, bpjs, satusehat, apotek |
| key       | varchar(255)  | Unique per group               |
| value     | text          |                                |
| tipe_data | enum          | string, integer, boolean, json |
| created_at| timestamp     |                                |
| updated_at| timestamp     |                                |

#### `notifications`

| Kolom       | Tipe          | Keterangan                                |
| ----------- | ------------- | ----------------------------------------- |
| id          | bigint (PK)   |                                           |
| user_id     | bigint (FK)   | Penerima notifikasi (nullable = broadcast)|
| tipe        | varchar(50)   | low_stock, expired, bpjs_claim, sistem    |
| judul       | varchar(255)  |                                           |
| pesan       | text          |                                           |
| data        | json          | Data tambahan (nullable)                  |
| is_read     | boolean       | Default false                             |
| read_at     | timestamp     |                                           |
| created_at  | timestamp     |                                           |

---

## 5. Alur Data Integrasi

### 5.1 Alur BPJS Kesehatan

#### A. Alur Pendaftaran & Antrean BPJS

```
Pasien datang
      |
      v
Verifikasi kepesertaan via VClaim API (Peserta.nik / Peserta.noka)
      |
      +-- Jika valid: data peserta ditampilkan (nama, no. kartu, kelas, status)
      |       |
      |       v
      |   Generate SEP via VClaim API (SEP.Insert)
      |       |   Data: no_kartu, tgl_pelayanan, kode_diagnosa, kode_poli, kode_faskes
      |       v
      |   SEP terbit -> simpan no_sep ke tabel bpjs_patients
      |       |
      |       v
      |   Sinkronasi antrean ke Antrol BPJS
      |       |   Data: no_kartu, no_sep, kode_poli, tanggal, sesi
      |       v
      |   Dapatkan no_antrean_bpjs dari server BPJS
      |       |
      |       v
      |   Nomor antrean masuk ke sistem antrean internal
      |       |
      |       v
      |   Pasien menunggu panggilan
      |
      +-- Jika tidak valid: tampilkan error / arahkan ke admin BPJS
```

#### B. Alur Pelayanan & Klaim BPJS

```
Pasien diperiksa dokter
      |
      v
Dokter mencatat anamnesis, diagnosa (ICD-10), tindakan (ICD-9), resep
      |
      v
Setelah pelayanan selesai:
      |
      v
Siapkan data klaim:
    - Data SEP
    - Diagnosa utama & sekunder (ICD-10)
    - Prosedur / tindakan (ICD-9)
    - Tarif RS & komponen biaya
      |
      v
Submit klaim via VClaim API (Klaim.Insert / Klaim.Update)
      |   Format: JSON INACBG's Group
      v
BPJS memproses klaim:
    Validasi -> Klaim diterima / ditolak / perlu revisi
      |
      v
Monitoring status via Monitoring.Klaim.JS
    Polling periodik atau manual check
      |
      v
Status klaim: Paid / Rejected
      |
      +-- Jika Rejected: lihat kode error, lakukan revisi, submit ulang
```

#### C. Alur Rujukan BPJS

```
Pasien perlu rujukan ke faskes lain
      |
      v
Dokter input data rujukan:
    - Diagnosa
    - Faskes tujuan (cari via Aplicares API)
    - Catatan rujukan
      |
      v
Submit rujukan via VClaim API (Rujukan.Insert)
      |
      v
No. rujukan terbit -> cetak surat rujukan
```

### 5.2 Alur Satu Sehat (Kemenkes)

#### A. Registrasi & Akses

```
Developer registrasi aplikasi di portal Satu Sehat (KYC / IST)
      |
      v
Dapatkan Client ID dan Private Key
      |
      v
Registrasi Organization di FHIR server
    - Data faskes (klinik)
    - Alamat, NIB, NPWP, dll
      |
      v
Daftarkan Tenant & Client via IST API
      |
      v
Aplikasi terdaftar & aktif
```

#### B. OAuth2 Authentication

```
Sistem membutuhkan akses ke FHIR API
      |
      v
Request access token:
    - Client Credentials Grant
    - Header: client_id, client_private_key (signed JWT)
    - Endpoint: https://api-satusehat.kemkes.go.id/oauth2/v1/accesstoken
      |
      v
Dapatkan access_token + refresh_token
    - Token disimpan di cache (Redis/file) dengan expiry time
      |
      v
Gunakan access_token di Authorization header:
    Authorization: Bearer <access_token>
      |
      v
Jika 401 Unauthorized:
    - Refresh token secara otomatis
    - Retry request
```

#### C. Kirim Resource FHIR

```
Transaksi internal terjadi (pasien baru, kunjungan baru, diagnosa baru)
      |
      v
Service Satu Sehat mendeteksi data perlu dikirim
      |
      v
Transformasi data internal -> FHIR resource (JSON)
    - Patient: mapping dari tabel patients
    - Encounter: mapping dari tabel medical_records + queues
    - Condition: mapping dari tabel medical_record_details (ICD-10)
    - Observation: mapping dari tabel observasi / tanda vital
    - MedicationRequest: mapping dari tabel prescriptions
      |
      v
Kirim ke FHIR API:
    - POST /fhir-r4/v1/{resource_type}
    - POST /fhir-r4/v1/{resource_type}/{id} (update)
      |
      v
Simpan response di satusehat_resources:
    - resource_id (dari server FHIR)
    - payload_request & payload_response
    - status & status_code
      |
      v
Jika gagal (network error, validasi error):
    - Catat di satusehat_logs
    - Retry via queue job
    - Notifikasi admin jika gagal terus
```

#### D. iCare (Konsumsi Data Riwayat)

```
Dokter ingin melihat riwayat pasien dari faskes lain
      |
      v
Cek consent pasien untuk akses data
      |
      v
Request iCare Summary:
    GET /fhir-r4/v1/Patient/{patient_id}/$everything
      |
      v
Terima data riwayat medis:
    - Riwayat diagnosa (Condition)
    - Riwayat obat (MedicationRequest)
    - Riwayat alergi (AllergyIntolerance)
    - Hasil lab (Observation)
      |
      v
Tampilkan di front-end dalam bentuk timeline yang mudah dibaca
```

#### E. Mapping Terminologi

```
Data internal (kode ICD-10, LOINC, KFA) perlu divalidasi dengan terminologi Satu Sehat
      |
      v
Validasi kode via FHIR Terminology API:
    GET /fhir-r4/v1/CodeSystem/{system}/validate-code?code={code}
      |
      v
Jika kode valid:
    - Gunakan kode tersebut
Jika kode tidak valid:
    - Cari kode alternatif via konsep mapping
    - Log warning untuk review manual
      |
      v
Simpan mapping terminologi di tabel internal
```

---

## 6. Tahapan Implementasi

### Phase 1: Foundation (Minggu 1-2)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Setup Proyek Laravel 13                      | `composer create-project laravel/laravel e-klinik`. `.env` dikonfigurasi: `DB_DATABASE=e_klinik`, `SESSION_DRIVER=file`, `CACHE_STORE=file` |
| Instalasi Dependencies                       | `laravel/breeze` (Blade+Bootstrap), `laravel/sanctum`, `laravel/pail` (logs realtime) — DomPDF & Excel tidak diinstal (belum dibutuhkan) |
| Konfigurasi Frontend                         | Bootstrap 5 via Vite: `resources/css/app.css` import Bootstrap, `resources/js/app.js` import Bootstrap JS. Build sukses (`app-BKcgrL9x.css` 227kB, `app-cfCUeujM.js` 80kB) |
| Setup Database                               | MySQL (Laragon), database `e_klinik`, 29 migration (semua tabel termasuk lab, BPJS, Satu Sehat, ICD-10) |
| Setup Auth Web                               | Laravel Breeze Blade + Bootstrap 5 (session auth via `routes/auth.php`). Login, register, logout, password reset, email verification |
| Setup Auth API                               | Laravel Sanctum `auth:sanctum` pada seluruh route `/api/v1/*`. Token Bearer via `POST /sanctum/token`. `app/Models/User` tambah trait `HasApiTokens` |
| Setup Role Middleware                        | Custom `app/Http/Middleware/RoleMiddleware.php` (bukan spatie). Daftarkan di `bootstrap/app.php` dengan alias `role`. ENUM kolom `role` di users: `admin,doctor,nurse,pharmacist,cashier,laborant` |
| Setup CORS                                   | `config/cors.php`: `allowed_origins = ['*']`, `paths = ['api/*', 'sanctum/csrf-cookie']`, `supports_credentials = true` |
| Setup Service Layer                          | Folder `app/Services/` dengan subfolder `BPJS/`, `SatuSehat/`. Service binding via `IntegrationServiceProvider.php` |
| Migration & Model Dasar                      | 29 migration: users, patients, polyclinics, doctors, queues, medical_records, medicines, prescriptions, inventories, bpjs_*, satusehat_*, lab_*, icd10. 25 model Eloquent |
| Controller Layer                             | 3 API Controller (`Api/BPJS/VClaim`, `Api/BPJS/Antrol`, `Api/SatuSehat/FHIR`) + 15 Web Controller termasuk `Web/BPJS/*`, `Web/SatuSehat/*` (copy dari API, session auth) |
| Seeder                                       | 4 user default (admin, dokter, apoteker, laboran — password `*123`), 8 poli, 3 dokter, 8 kategori obat, 5 kategori lab + 20 tes lab, ~1200 ICD-10 via migration |
| Artisan Commands                             | `satusehat:sync` untuk sinkronasi batch, `composer dev` untuk server + queue + logs + Vite concurrently |

**Deliverables:**
- Laravel 13 + MySQL running, 29 tabel ter-migrasi
- Web auth (Breeze Blade) + API auth (Sanctum)
- Role middleware dengan 6 role (termasuk laborant)
- CORS siap untuk integrasi lintas domain
- Service layer BPJS (VClaim, Antrol) & Satu Sehat (FHIR R4)
- 26 route API (Sanctum) + 40 route JSON internal (session auth) + 90+ route Blade
- Data seeder: user, poli, dokter, obat, lab, ICD-10

### Phase 2: Modul Pasien & Antrean (Minggu 3-4)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| CRUD Pasien                                   | Service & Controller untuk registrasi pasien baru, update, delete, search, list. Generate nomor RM otomatis |
| Dashboard Admin                               | Halaman utama setelah login. Menampilkan statistik: jumlah pasien hari ini, status antrean, jadwal dokter, grafik kunjungan, notifikasi stok obat & expired. Data aggregated dari seluruh modul |
| Manajemen Identitas Pasien                    | Upload foto, data kontak darurat, alergi                                                   |
| Sistem Antrean                                | Logic generate nomor antrean per poli per hari. CRUD antrean. Status pipeline               |
| Pencetakan Tiket Antrean                      | Implementasi cetak thermal (ESC/POS). Format tiket: nama, nomor, poli, tanggal              |
| Display Antrean (TV Monitor)                  | Halaman display real-time menggunakan WebSocket atau SSE (Server-Sent Events). Update status antrean live |
| Voice Call System (TTS)                       | Integrasi TTS (Google Cloud TTS / eSpeak). Pemanggilan otomatis via button di sistem        |
| Pelaporan Antrean                             | Laporan harian, rata-rata waktu tunggu, per poli                                           |
| Export/Import Pasien                          | Export data pasien ke Excel. Import data pasien dari Excel/CSV                             |

**Deliverables:**
- Dashboard admin dengan statistik real-time
- Full CRUD pasien dengan search & filter
- Sistem antrean dengan tiket, display TV, voice call
- Laporan antrean

### Phase 3: Modul RME + ICD-10 (Minggu 5-6)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Master ICD-10                                | Import data ICD-10 (full). API pencarian dengan autocomplete. Admin panel untuk update      |
| Template SOAP                                | Form input RME dengan layout SOAP. Auto-save draft                                         |
| Rekam Medis CRUD                             | Create, read, update RME. Validasi kelengkapan sebelum finalisasi                          |
| Detail Rekam Medis                           | Diagnosa (ICD-10), tindakan (ICD-9), catatan, observasi                                    |
| Riwayat Berobat Pasien                       | Timeline kronologis kunjungan. Link ke detail RME per tanggal                              |
| Dokumen Attachment                           | Upload, preview, delete file attachment. Batasan tipe & ukuran file                         |
| Cetak Rekam Medis (PDF)                      | Generate PDF rekam medis untuk pasien/tujuan administrasi                                  |
| Observations / Tanda Vital                   | Input TTv (tekanan darah, nadi, suhu, RR, berat badan, tinggi badan). Grafik riwayat TTv   |

**Deliverables:**
- ICD-10 database & API pencarian
- Form RME SOAP
- Riwayat berobat & attachment
- Cetak PDF

### Phase 4: Modul Apotek & Inventaris (Minggu 7-8)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Master Obat & Kategori                       | CRUD obat dengan kode, nama generik/dagang, golongan, bentuk sediaan, kekuatan, pabrik, kategori |
| Supplier Management                          | CRUD supplier                                                                               |
| Manajemen Batch & Expired                    | Input batch obat dengan harga beli, tanggal kadaluwarsa. Tracking stok per batch            |
| Manajemen Stok (Mutasi)                      | Transaksi stok masuk (pembelian) & keluar (penjualan/disposisi). Update stok real-time      |
| Low Stock Alert                              | Notifikasi dashboard & email jika stok di bawah minimum                                    |
| Peringatan Kedaluwarsa                       | Daftar obat yang akan expired dalam 30/60/90 hari. Notifikasi                              |
| Resep Obat                                   | Input resep dari RME atau manual. Pilih obat, dosis, jadwal. Status resep                  |
| Racikan Obat                                 | Resep racikan dengan komposisi beberapa obat                                               |
| Stok Opname                                  | Proses opname: buat sesi opname, input stok fisik, hitung selisih, adjust stok             |
| Laporan Kartu Stok & Mutasi                   | Kartu stok per obat, mutasi per periode, stok opname                                       |
| Cetak Resep (PDF)                            | Print resep ke format standar                                                               |
| Penjualan Bebas                              | Transaksi penjualan obat non-resep                                                         |

**Deliverables:**
- Full CRUD obat, supplier, kategori
- Manajemen batch, expired, stok
- Resep obat dengan racikan
- Laporan stok

### Phase 5: Integrasi BPJS VClaim & Antrol (Minggu 9-10)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Setup BPJS Client                            | Buat BPJS Client class (Guzzle). Konfigurasi base URL, basic auth, secret key. Handle signature (hash) |
| Verifikasi Peserta BPJS                      | Implementasi VClaim Peserta.nik / Peserta.noka. Simpan data kepesertaan                    |
| SEP (Surat Eligibilitas Peserta)             | CRUD SEP: Insert, Update, Delete, Status. Simpan no_sep ke database                        |
| Antrean BPJS (Antrol)                        | Sinkronasi jadwal dokter. Kirim antrean. Dapatkan nomor antrean BPJS                       |
| Aplicares                                    | Pencarian faskes rujukan via API Aplicares                                                  |
| Rujukan BPJS                                 | Insert, Update, Delete, Status rujukan. Cetak surat rujukan                                |
| Klaim BPJS                                   | Submit klaim rawat jalan & rawat inap. Data INACBG's Group. Attachment klaim               |
| Monitoring Klaim                             | Cek status klaim via VClaim API. Update status di database                                 |
| Laporan BPJS                                 | Laporan pendaftaran, pelayanan, penggantian biaya                                          |
| Logging & Error Handling                     | Log setiap request/response BPJS. Handle timeout, error code, retry mechanism              |

**Deliverables:**
- Verifikasi peserta BPJS & SEP
- Antrean BPJS sinkron
- Submit & monitoring klaim
- Rujukan BPJS

### Phase 6: Integrasi Satu Sehat FHIR (Minggu 11-12)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Setup Satu Sehat Client                      | OAuth2 authentication. Token management (cache & refresh). HTTP client dengan Guzzle        |
| FHIR Resource: Patient                       | Mapping dari tabel patients ke FHIR Patient. Create & update di FHIR server                |
| FHIR Resource: Encounter                     | Mapping dari medical_records + queues. Send setelah kunjungan selesai                      |
| FHIR Resource: Condition                     | Mapping dari medical_record_details (ICD-10). Kirim diagnosa terkait Encounter             |
| FHIR Resource: Observation                   | Mapping tanda vital & hasil lab. Kirim Observation ke FHIR server                          |
| FHIR Resource: MedicationRequest             | Mapping resep obat ke FHIR MedicationRequest                                               |
| FHIR Resource: Organization & Practitioner   | Data faskes & dokter di FHIR server                                                        |
| iCare Integration                            | Konsumsi data riwayat pasien dari faskes lain. Tampilkan informasi iCare summary           |
| Terminology Mapping                          | Validasi ICD-10, LOINC, KFA codes. Mapping kode internal ke terminologi Satu Sehat         |
| Logging & Audit Trail                        | Setiap operasi FHIR dicatat di satusehat_resources & satusehat_logs                        |
| Dashboard Integrasi Satu Sehat               | Status resource terkirim, gagal, pending. Manual retry                                     |

**Deliverables:**
- OAuth2 & token management
- FHIR Patient, Encounter, Condition, Observation, MedicationRequest
- iCare integration
- Terminology validation
- Integration dashboard

### Phase 7: Testing, Debugging, UAT (Minggu 13-14)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Unit Testing                                 | PHPUnit test untuk service layer, model relationship, helper functions                      |
| Feature Testing                              | Test API endpoints full flow. Test setiap skenario: sukses, validasi error, not found, unauthorized |
| Integration Testing                          | Test integrasi BPJS (mock response). Test integrasi Satu Sehat (sandbox)                   |
| Performance Testing                          | Load test dengan Apache Bench / k6. Identifikasi bottleneck query. Optimasi index & query   |
| Security Testing                             | SQL injection, XSS, CSRF, auth bypass. Test role-based access                              |
| User Acceptance Testing (UAT)                | Demo ke user (staf klinik, dokter, apoteker). Kumpulkan feedback. Iterasi perbaikan        |
| Bug Fixing                                   | Prioritaskan critical & high bugs. Regression test setelah fix                             |

**Deliverables:**
- Laporan hasil testing
- Bug tracker terkelola
- Fix untuk semua critical & high bugs
- Sign-off UAT

### Phase 8: Deployment & Dokumentasi (Minggu 15-16)

| Aktivitas                                    | Detail                                                                                      |
| -------------------------------------------- | ------------------------------------------------------------------------------------------- |
| Persiapan Server                             | Setup production server (Nginx/Apache, PHP 8.3+, MySQL, Redis). Konfigurasi firewall, SSL  |
| Deployment                                   | Deploy menggunakan git + deploy script. Setup environment production. Migrate database      |
| Backup Strategy                              | Setup backup database harian. Backup file storage (attachment, foto). Retention policy     |
| Monitoring Setup                             | Setup Laravel Horizon (queue), Laravel Telescope (debug), error tracking (Sentry opsional)  |
| Dokumentasi API                              | Dokumentasi API menggunakan Scribe / Swagger. Endpoint, parameter, contoh request/response  |
| Dokumentasi Pengguna                         | Buku panduan penggunaan untuk admin, dokter, apoteker, staf pendaftaran, kasir              |
| Dokumentasi Teknis                           | Dokumentasi arsitektur, konfigurasi environment, deployment guide, maintenance guide        |
| Pelatihan User                               | Sesi pelatihan untuk setiap role. Video tutorial (opsional)                                 |
| Go Live                                      | Cutover dari sistem lama (jika ada). Monitoring ketat 1 minggu pertama                      |
| Maintenance Plan                             | Jadwal maintenance rutin. Prosedur handling incident. Kontak support                        |

**Deliverables:**
- Production server siap
- Dokumentasi API, user, teknis
- Pelatihan user
- Go live

---

## 7. Struktur Folder Proyek

```
e-klinik/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── SyncSatusehat.php              # Sinkronasi data ke Satu Sehat
│   │       └── Icd10Seeder                    # Seeder ICD-10 (via migration)
│   │
│   ├── Enums/
│   │   ├── QueueStatus.php                  # Status antrean
│   │   ├── PatientStatus.php                # Status pasien
│   │   ├── VisitType.php                    # Tipe kunjungan
│   │   └── ...                              # Enum lainnya
│   │
│   ├── Exceptions/
│   │   ├── BPJS/
│   │   │   └── BPJSException.php            # Exception BPJS integration
│   │   └── SatuSehat/
│   │       └── SatuSehatException.php        # Exception Satu Sehat
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                         # REST API (hanya integrasi eksternal)
│   │   │   │   ├── BPJS/
│   │   │   │   │   ├── VClaimController.php  # SEP, Peserta, Claim, Referensi
│   │   │   │   │   └── AntrolController.php  # Antrean BPJS
│   │   │   │   └── SatuSehat/
│   │   │   │       └── FHIRController.php    # Sync FHIR: Patient, Encounter, Condition, Observation, MedicationRequest
│   │   │   │
│   │   │   ├── Web/                         # Web controllers (Blade views + JSON internal)
│   │   │   │   ├── BPJS/                       # JSON endpoints BPJS (session auth)
│   │   │   │   │   ├── VClaimController.php    # SEP, Peserta, Claim, Referensi
│   │   │   │   │   └── AntrolController.php    # Antrean BPJS
│   │   │   │   ├── SatuSehat/                  # JSON endpoints Satu Sehat (session auth)
│   │   │   │   │   └── FHIRController.php      # Sync FHIR: Patient, Encounter, Condition, Observation, MedicationRequest
│   │   │   │   ├── BpjsSepController.php      # SEP BPJS
│   │   │   │   ├── DashboardController.php   # Dashboard admin
│   │   │   │   ├── PatientController.php     # CRUD pasien
│   │   │   │   ├── QueueController.php       # Manajemen antrean + display TV
│   │   │   │   ├── MedicalRecordController.php # RME
│   │   │   │   ├── DiagnosisController.php   # ICD-10 lookup
│   │   │   │   ├── MedicineController.php    # Master obat
│   │   │   │   ├── PrescriptionController.php # Resep
│   │   │   │   ├── InventoryController.php   # Stok & batch
│   │   │   │   ├── LabTestCategoryController.php # Kategori lab
│   │   │   │   ├── LabTestController.php       # Master tes lab
│   │   │   │   ├── LabRequestController.php    # Permintaan lab
│   │   │   │   ├── LabResultController.php     # Hasil lab
│   │   │   │   ├── PolyclinicController.php  # Data poli
│   │   │   │   └── DoctorController.php      # Data dokter
│   │   │   │
│   │   │   ├── Auth/                        # Breeze auth controllers
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   └── ... (reset, verify, confirm)
│   │   │   │
│   │   │   └── ProfileController.php        # Manajemen profil user
│   │   │
│   │   ├── Middleware/
│   │   │   ├── RoleMiddleware.php            # Role-based access: admin,doctor,laborant,dll
│   │   │   └── BpjsSignature.php            # Validasi signature BPJS callback
│   │   │
│   │   ├── Requests/
│   │   │   ├── PatientRequest.php
│   │   │   ├── QueueRequest.php
│   │   │   ├── MedicalRecordRequest.php
│   │   │   └── PrescriptionRequest.php
│   │   │
│   │   └── Resources/
│   │       ├── PatientResource.php
│   │       ├── PatientCollection.php
│   │       ├── QueueResource.php
│   │       ├── MedicalRecordResource.php
│   │       └── PrescriptionResource.php
│   │
│   ├── Integrations/
│   │   ├── Satusehat/
│   │   │   └── KYCController.php            # Registrasi KYC Kemenkes
│   │   ├── Icare/
│   │   │   └── IcareService.php             # iCare interoperability
│   │   └── BPJS/                            # (future)
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Patient.php
│   │   ├── Polyclinic.php
│   │   ├── Doctor.php
│   │   ├── Queue.php
│   │   ├── MedicalRecord.php
│   │   ├── Icd10Diagnosis.php
│   │   ├── MedicineCategory.php
│   │   ├── Medicine.php
│   │   ├── Supplier.php
│   │   ├── Prescription.php
│   │   ├── PrescriptionItem.php
│   │   ├── Inventory.php                    # Batch stok
│   │   ├── InventoryTransaction.php
│   │   ├── BpjsPatient.php
│   │   ├── BpjsClaim.php
│   │   ├── BpjsReferral.php
│   │   ├── BpjsAntrean.php
│   │   ├── BpjsSep.php
│   │   ├── SatusehatResource.php
│   │   ├── SatusehatLog.php
│   │   ├── LabTestCategory.php
│   │   ├── LabTest.php
│   │   ├── LabRequest.php
│   │   ├── LabRequestItem.php
│   │   └── LabResult.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php           # Register IntegrationServiceProvider
│   │   └── IntegrationServiceProvider.php   # Bind BPJS & SatuSehat services
│   │
│   └── Services/
│       ├── BPJS/
│       │   ├── BPJSHttpClient.php           # HMAC-SHA256 + AES decrypt
│       │   ├── VClaimService.php            # VClaim API (peserta, SEP, claim, referensi)
│       │   ├── AntrolService.php            # Antrean BPJS API
│       │   └── AplicaresService.php         # Referensi faskes API
│       │
│       ├── SatuSehat/
│       │   ├── AuthService.php              # OAuth2 client_credentials
│       │   ├── SatuSehatClient.php          # FHIR HTTP client
│       │   ├── PatientService.php           # FHIR Patient resource
│       │   ├── EncounterService.php         # FHIR Encounter resource
│       │   ├── ConditionService.php         # FHIR Condition resource
│       │   ├── ObservationService.php       # FHIR Observation (vital sign/Lab)
│       │   ├── MedicationRequestService.php # FHIR MedicationRequest
│       │   ├── OrganizationService.php      # FHIR Organization
│       │   ├── PractitionerService.php      # FHIR Practitioner
│       │   └── TerminologyService.php       # ICD-10, LOINC, KFA search
│       │
│       ├── PatientService.php              # Business logic pasien
│       ├── QueueService.php                # Business logic antrean
│       ├── MedicalRecordService.php        # Business logic RME
│       ├── Icd10Service.php                # Business logic ICD-10
│       ├── InventoryService.php            # Business logic inventaris
│       ├── BpjsSepService.php              # SEP BPJS (createFromQueue, update, delete, get)
│       ├── VoiceCallService.php            # TTS announcement
│       ├── TtsProvider.php                 # TTS interface
│       ├── GoogleCloudTtsProvider.php      # Google Cloud TTS
│       └── BrowserTtsProvider.php          # Browser fallback
│
├── config/
│   ├── bpjs.php                            # BPJS endpoints & credentials
│   └── satusehat.php                       # Satu Sehat endpoints & credentials
│
├── database/
│   ├── migrations/                         # 29 migration files
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── ... (patients, queues, medical_records, bpjs_*, satusehat_*, lab_*)
│   │   ├── 000021_create_icd10_seeder.php  # Seeder ~1200 ICD-10 codes
│   │   ├── 000022_create_bpjs_seps_table.php
│   │   ├── 000023_add_bpjs_sep_id_to_queues_table.php
│   │   ├── 000024_add_laborant_role_to_users.php
│   │   └── 000025-000029 (lab migrations)
│   └── seeders/
│
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php               # Bootstrap 5 admin layout + sidebar
│       │   ├── guest.blade.php             # Bootstrap 5 auth layout
│       │   ├── display.blade.php          # Fullscreen TV display layout
│       │   └── navigation.blade.php        # Dark sidebar menu
│       ├── dashboard.blade.php
│       ├── auth/                           # Login, register, forgot/reset password
│       ├── profile/                        # Edit profil, ganti password
│       ├── patients/                       # Index, create, edit, show
│       ├── queues/                         # Index, create, show, display TV
│       ├── medical-records/                # Index, create, edit, show
│       ├── diagnoses/                      # ICD-10 search
│       ├── medicines/                      # Index, create, edit, show
│       ├── prescriptions/                  # Index, create, show, print
│       ├── inventories/                    # Index, create, show, low-stock, expiring, expired
│       ├── polyclinics/                    # Index, create, edit, show
│       ├── doctors/                        # Index, create, edit, show
│       ├── bpjs-seps/                      # Index, create, show
│       ├── lab-test-categories/            # Index, create, edit
│       ├── lab-tests/                      # Index, create, edit, show
│       ├── lab-requests/                   # Index, create, show
│       └── lab-results/                    # Index, input, edit
│
├── routes/
│   ├── web.php                             # Blade + JSON internal routes (130+ route, auth + role protected)
│   ├── api.php                             # API routes (26 endpoint: BPJS + SatuSehat, Sanctum) — untuk mobile
│   ├── auth.php                            # Breeze auth routes
│   └── console.php                         # Artisan commands
│
├── docs/
│   ├── implementation-plan.md              # Dokumen perencanaan
│   └── panduan-integrasi-bridging.md       # Panduan integrasi BPJS & Satu Sehat
│
├── AGENTS.md                               # Agent instruction file
├── .env.example                            # Template konfigurasi environment
├── composer.json
└── package.json
```
│   │   ├── IntegrationLog.php
│   │   ├── Configuration.php
│   │   └── Notification.php
│   │
│   ├── Observers/
│   │   ├── PatientObserver.php               # Event saat create/update pasien
│   │   ├── MedicalRecordObserver.php         # Event saat create/update RME
│   │   └── PrescriptionObserver.php          # Event saat create/update resep
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   ├── RepositoryServiceProvider.php     # Bind interface ke implementasi
│   │   └── IntegrationServiceProvider.php    # Register integration services
│   │
│   ├── Services/
│   │   ├── PatientService.php                # Logika bisnis pasien
│   │   ├── QueueService.php                  # Logika antrean
│   │   ├── QueueDisplayService.php           # Update display realtime
│   │   ├── VoiceCallService.php              # TTS engine
│   │   ├── MedicalRecordService.php          # Logika RME
│   │   ├── Icd10Service.php                  # Pencarian ICD-10
│   │   ├── PrescriptionService.php           # Logika resep
│   │   ├── MedicineService.php               # Logika obat
│   │   ├── InventoryService.php              # Logika stok & batch
│   │   ├── StockOpnameService.php            # Logika stok opname
│   │   ├── NotificationService.php           # Notifikasi internal
│   │   ├── ReportService.php                 # Generate laporan
│   │   │
│   │   ├── Bpjs/
│   │   │   ├── BpjsService.php               # Base BPJS service
│   │   │   ├── BpjsPesertaService.php        # Verifikasi peserta
│   │   │   ├── BpjsSepService.php            # SEP
│   │   │   ├── BpjsAntreanService.php        # Antrol
│   │   │   ├── BpjsRujukanService.php        # Rujukan
│   │   │   ├── BpjsKlaimService.php          # Klaim
│   │   │   └── BpjsAplicaresService.php      # Aplicares
│   │   │
│   │   └── Satusehat/
│   │       ├── SatusehatService.php           # Base service
│   │       ├── SatusehatAuthService.php       # OAuth2
│   │       ├── SatusehatPatientService.php
│   │       ├── SatusehatEncounterService.php
│   │       ├── SatusehatConditionService.php
│   │       ├── SatusehatObservationService.php
│   │       ├── SatusehatMedicationRequestService.php
│   │       ├── SatusehatOrganizationService.php
│   │       ├── SatusehatPractitionerService.php
│   │       ├── SatusehatICareService.php
│   │       └── SatusehatTerminologyService.php
│   │
│   ├── Clients/
│   │   ├── BpjsClient.php                    # HTTP client BPJS (Guzzle)
│   │   └── SatusehatClient.php               # HTTP client Satu Sehat (Guzzle)
│   │
│   ├── DTOs/
│   │   ├── PatientDto.php                    # Data transfer object pasien
│   │   ├── QueueDto.php
│   │   ├── MedicalRecordDto.php
│   │   ├── PrescriptionDto.php
│   │   ├── BpjsSepDto.php
│   │   ├── BpjsClaimDto.php
│   │   └── Satusehat/
│   │       ├── FhirPatientDto.php
│   │       ├── FhirEncounterDto.php
│   │       ├── FhirConditionDto.php
│   │       └── FhirObservationDto.php
│   │
│   ├── Traits/
│   │   ├── ApiResponse.php                   # Standard JSON response
│   │   ├── HasCreatedBy.php                  # Track created_by
│   │   ├── HasUpdatedBy.php                  # Track updated_by
│   │   ├── GenerateNoRm.php                  # Generator nomor RM
│   │   ├── GenerateNoAntrean.php             # Generator nomor antrean
│   │   ├── GenerateNoResep.php               # Generator nomor resep
│   │   └── Filterable.php                    # Trait filter & search
│   │
│   └── Helpers/
│       ├── BpjsSignature.php                 # Generate hash signature BPJS
│       ├── BpjsConverter.php                 # Format converter BPJS
│       ├── SatusehatConverter.php            # Mapping ke FHIR format
│       ├── DateHelper.php                    # Format tanggal
│       └── NumberHelper.php                  # Format angka/rupiah
│
├── bootstrap/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── sanctum.php
│   ├── bpjs.php                              # Config BPJS API (base_url, consumer_id, secret_key)
│   ├── satusehat.php                         # Config Satu Sehat (base_url, client_id, environment)
│   └── queue.php
│
├── database/
│   ├── factories/
│   │   ├── PatientFactory.php
│   │   ├── QueueFactory.php
│   │   └── MedicineFactory.php
│   │
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_patients_table.php
│   │   ├── 0001_01_01_000002_create_polyclinics_table.php
│   │   ├── 0001_01_01_000003_create_doctors_table.php
│   │   ├── 0001_01_01_000004_create_doctor_schedules_table.php
│   │   ├── 0001_01_01_000005_create_queues_table.php
│   │   ├── 0001_01_01_000006_create_medical_records_table.php
│   │   ├── 0001_01_01_000007_create_medical_record_details_table.php
│   │   ├── 0001_01_01_000008_create_attachments_table.php
│   │   ├── 0001_01_01_000009_create_icd10_diagnoses_table.php
│   │   ├── 0001_01_01_000010_create_medicine_categories_table.php
│   │   ├── 0001_01_01_000011_create_medicines_table.php
│   │   ├── 0001_01_01_000012_create_suppliers_table.php
│   │   ├── 0001_01_01_000013_create_prescriptions_table.php
│   │   ├── 0001_01_01_000014_create_prescription_items_table.php
│   │   ├── 0001_01_01_000015_create_inventories_table.php
│   │   ├── 0001_01_01_000016_create_inventory_transactions_table.php
│   │   ├── 0001_01_01_000017_create_bpjs_patients_table.php
│   │   ├── 0001_01_01_000018_create_bpjs_claims_table.php
│   │   ├── 0001_01_01_000019_create_bpjs_referrals_table.php
│   │   ├── 0001_01_01_000020_create_bpjs_antrean_table.php
│   │   ├── 0001_01_01_000021_create_bpjs_jadwal_table.php
│   │   ├── 0001_01_01_000022_create_satusehat_resources_table.php
│   │   ├── 0001_01_01_000023_create_satusehat_logs_table.php
│   │   ├── 0001_01_01_000024_create_integration_logs_table.php
│   │   ├── 0001_01_01_000025_create_configurations_table.php
│   │   └── 0001_01_01_000026_create_notifications_table.php
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── PolyclinicSeeder.php
│       ├── Icd10DiagnosisSeeder.php
│       ├── MedicineCategorySeeder.php
│       └── ConfigurationSeeder.php
│
├── docs/
│   ├── implementation-plan.md                # Dokumen ini
│   ├── api-reference.md                      # Dokumentasi API
│   ├── database-schema.md                    # Diagram database
│   └── user-guide/                           # Buku panduan user
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── uploads/
│       ├── patients/                         # Foto pasien
│       ├── attachments/                      # Dokumen RME
│       └── temp/                             # File sementara
│
├── resources/
│   ├── views/
│   │   ├── antrean/
│   │   │   ├── display.blade.php             # Display TV monitor
│   │   │   └── tiket.blade.php               # Template cetak tiket
│   │   ├── pdf/
│   │   │   ├── rekam-medis.blade.php         # Cetak RME
│   │   │   ├── resep.blade.php               # Cetak resep
│   │   │   └── surat-rujukan.blade.php       # Cetak rujukan
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   └── ...
│   │
│   └── lang/
│       └── id/                               # Bahasa Indonesia localization
│           ├── validation.php
│           ├── messages.php
│           └── ...
│
├── routes/
│   ├── api.php                                # API routes v1
│   ├── web.php                                # Web routes (opsional)
│   └── console.php                            # Artisan commands
│
├── storage/
│   └── logs/
│       ├── laravel.log
│       ├── bpjs.log                          # Log khusus BPJS
│       └── satusehat.log                     # Log khusus Satu Sehat
│
├── tests/
│   ├── Unit/
│   │   ├── Services/
│   │   │   ├── PatientServiceTest.php
│   │   │   ├── QueueServiceTest.php
│   │   │   ├── MedicalRecordServiceTest.php
│   │   │   ├── InventoryServiceTest.php
│   │   │   └── BpjsSepServiceTest.php
│   │   └── Helpers/
│   │       └── BpjsSignatureTest.php
│   │
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── PatientApiTest.php
│   │   │   ├── QueueApiTest.php
│   │   │   ├── MedicalRecordApiTest.php
│   │   │   ├── MedicineApiTest.php
│   │   │   ├── PrescriptionApiTest.php
│   │   │   ├── InventoryApiTest.php
│   │   │   ├── BpjsSepApiTest.php
│   │   │   └── BpjsKlaimApiTest.php
│   │   └── Integration/
│   │       └── SatusehatIntegrationTest.php
│   │
│   └── TestCase.php
│
├── .env.example                              # Template environment
├── .env                                       # Environment (tidak di-commit)
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
└── README.md
```

---

## 8. Konfigurasi Lingkungan

### 8.1 File `.env.example`

Berikut adalah template file `.env` yang berisi seluruh konfigurasi yang diperlukan:

```ini
# =====================
# APLIKASI
# =====================
APP_NAME=e-Klinik
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# =====================
# DATABASE
# =====================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_klinik
DB_USERNAME=root
DB_PASSWORD=

# =====================
# QUEUE & CACHE
# =====================
QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=file

# =====================
# REDIS (opsional, untuk queue & cache)
# =====================
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# =====================
# SANCTUM (AUTH)
# =====================
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000
SESSION_DOMAIN=localhost

# =====================
# BPJS KESEHATAN
# =====================
BPJS_VCLAIM_BASE_URL=https://dvlp.bpjs-kesehatan.go.id/vclaim-rest-api/v2
BPJS_ANTROL_BASE_URL=https://dvlp.bpjs-kesehatan.go.id/antreanrs/rs
BPJS_APLICARES_BASE_URL=https://dvlp.bpjs-kesehatan.go.id/apicares

BPJS_CONSUMER_ID=your_consumer_id_here
BPJS_CONSUMER_SECRET=your_consumer_secret_here
BPJS_USER_KEY=your_user_key_here

# =====================
# SATU SEHAT (KEMENKES)
# =====================
SATUSEHAT_BASE_URL=https://api-satusehat.kemkes.go.id
SATUSEHAT_AUTH_URL=https://api-satusehat.kemkes.go.id/oauth2/v1
SATUSEHAT_FHIR_BASE_URL=https://api-satusehat.kemkes.go.id/fhir-r4/v1
SATUSEHAT_ICARE_BASE_URL=https://api-satusehat.kemkes.go.id/icare/v1

SATUSEHAT_CLIENT_ID=your_client_id_here
SATUSEHAT_PRIVATE_KEY_PATH=storage/keys/satusehat-private.pem
SATUSEHAT_ORGANIZATION_ID=your_org_id_here

# Environment: development / production
SATUSEHAT_ENVIRONMENT=development

# =====================
# WEBHOOK / REALTIME
# =====================
# Untuk WebSocket (opsional)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

# =====================
# MAIL (untuk notifikasi & laporan)
# =====================
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@e-klinik.com
MAIL_FROM_NAME="${APP_NAME}"

# =====================
# FILE STORAGE
# =====================
FILESYSTEM_DISK=local
# Untuk production: gunakan S3 atau cloud storage
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=
# AWS_BUCKET=

# =====================
# THERMAL PRINTER
# =====================
# Koneksi printer (opsional: network / usb)
PRINTER_CONNECTION_TYPE=network
PRINTER_IP=192.168.1.100
PRINTER_PORT=9100
```

### 8.2 File Konfigurasi Laravel

#### `config/bpjs.php`

```php
<?php

return [
    'vclaim' => [
        'base_url' => env('BPJS_VCLAIM_BASE_URL', 'https://dvlp.bpjs-kesehatan.go.id/vclaim-rest-api/v2'),
        'consumer_id' => env('BPJS_CONSUMER_ID'),
        'consumer_secret' => env('BPJS_CONSUMER_SECRET'),
        'user_key' => env('BPJS_USER_KEY'),
    ],
    'antrol' => [
        'base_url' => env('BPJS_ANTROL_BASE_URL', 'https://dvlp.bpjs-kesehatan.go.id/antreanrs/rs'),
    ],
    'aplicares' => [
        'base_url' => env('BPJS_APLICARES_BASE_URL', 'https://dvlp.bpjs-kesehatan.go.id/apicares'),
    ],
    'timeout' => 30, // seconds
    'retry' => [
        'max_attempts' => 3,
        'delay_ms' => 1000,
    ],
];
```

#### `config/satusehat.php`

```php
<?php

return [
    'base_url' => env('SATUSEHAT_BASE_URL', 'https://api-satusehat.kemkes.go.id'),
    'auth_url' => env('SATUSEHAT_AUTH_URL', 'https://api-satusehat.kemkes.go.id/oauth2/v1'),
    'fhir_base_url' => env('SATUSEHAT_FHIR_BASE_URL', 'https://api-satusehat.kemkes.go.id/fhir-r4/v1'),
    'icare_base_url' => env('SATUSEHAT_ICARE_BASE_URL', 'https://api-satusehat.kemkes.go.id/icare/v1'),

    'client_id' => env('SATUSEHAT_CLIENT_ID'),
    'private_key_path' => env('SATUSEHAT_PRIVATE_KEY_PATH', storage_path('keys/satusehat-private.pem')),
    'organization_id' => env('SATUSEHAT_ORGANIZATION_ID'),

    'environment' => env('SATUSEHAT_ENVIRONMENT', 'development'),

    'timeout' => 30,
    'retry' => [
        'max_attempts' => 3,
        'delay_ms' => 2000,
    ],
];
```

### 8.3 Queue Configuration

Queue digunakan untuk proses-proses yang membutuhkan waktu lama atau perlu di-retry:

| Queue Name             | Proses                                               |
| ---------------------- | ---------------------------------------------------- |
| `bpjs-sync`            | Sinkronasi data BPJS (antrean, jadwal, claim)        |
| `satusehat-sync`       | Kirim resource FHIR ke Satu Sehat                    |
| `notifications`        | Kirim notifikasi (email, dashboard)                  |
| `reports`              | Generate laporan (PDF, Excel)                        |
| `default`              | Tugas umum lainnya                                   |

**Priority:** `bpjs-sync` & `satusehat-sync` lebih tinggi dari `default` untuk memastikan data integrasi terkirim tepat waktu.

### 8.4 Cache Strategy

| Data                          | Cache Key                               | TTL       | Driver    |
| ----------------------------- | --------------------------------------- | --------- | --------- |
| ICD-10 search results         | `icd10.search.{query}`                  | 1 jam     | file      |
| BPJS token / signature        | `bpjs.token.{consumer_id}`              | 10 menit  | file      |
| Satu Sehat access token       | `satusehat.access_token`                | 55 menit  | file/redis|
| Pasien by RM                  | `patient.rm.{no_rm}`                    | 5 menit   | file      |
| Queue status                  | `queue.status.{tanggal}.{poli_id}`      | 1 menit   | file      |
| Konfigurasi sistem            | `config.{group}.{key}`                  | 24 jam    | file      |

---

## 9. Rute & Endpoint

Aplikasi menggunakan arsitektur **hybrid**: Blade server-rendered untuk fitur utama, REST API hanya untuk integrasi eksternal. Semua rute web di-protect oleh middleware `auth` dari Breeze + `role` untuk akses peran. API routes di-protect oleh `auth:sanctum`.

### 9.1 Web Routes (Blade) — `routes/web.php`

| Method | URL | Controller | Keterangan |
| ------ | --- | ---------- | ---------- |
| **Dashboard** |
| GET | `/dashboard` | `Web\DashboardController@index` | Statistik pasien, antrean, notifikasi |
| **Pasien** |
| GET | `/patients` | `Web\PatientController@index` | List pasien (search, pagination) |
| GET | `/patients/create` | `Web\PatientController@create` | Form registrasi |
| POST | `/patients` | `Web\PatientController@store` | Simpan pasien baru |
| GET | `/patients/{id}` | `Web\PatientController@show` | Detail + riwayat kunjungan |
| GET | `/patients/{id}/edit` | `Web\PatientController@edit` | Form edit |
| PUT | `/patients/{id}` | `Web\PatientController@update` | Update pasien |
| DELETE | `/patients/{id}` | `Web\PatientController@destroy` | Hapus (soft) |
| **Antrean** |
| GET | `/queues` | `Web\QueueController@index` | List antrean (filter: tgl, poli, status) |
| GET | `/queues/create` | `Web\QueueController@create` | Form daftar antrean |
| POST | `/queues` | `Web\QueueController@store` | Simpan antrean |
| GET | `/queues/{id}` | `Web\QueueController@show` | Detail antrean |
| POST | `/queues/{id}/call` | `Web\QueueController@call` | Panggil antrean + trigger TTS |
| POST | `/queues/{id}/in-progress` | `Web\QueueController@inProgress` | Mulai pemeriksaan |
| POST | `/queues/{id}/complete` | `Web\QueueController@complete` | Selesai |
| POST | `/queues/{id}/cancel` | `Web\QueueController@cancel` | Batal |
| GET | `/queues/display/tv` | `Web\QueueController@display` | Display TV (fullscreen) |
| **Rekam Medis** |
| GET | `/medical-records` | `Web\MedicalRecordController@index` | List RME |
| GET | `/medical-records/create` | `Web\MedicalRecordController@create` | Form RME baru |
| POST | `/medical-records` | `Web\MedicalRecordController@store` | Simpan RME |
| GET | `/medical-records/{id}` | `Web\MedicalRecordController@show` | Detail RME |
| GET | `/medical-records/{id}/edit` | `Web\MedicalRecordController@edit` | Form edit |
| PUT | `/medical-records/{id}` | `Web\MedicalRecordController@update` | Update RME |
| DELETE | `/medical-records/{id}` | `Web\MedicalRecordController@destroy` | Hapus (soft) |
| **ICD-10 Diagnosa** |
| GET | `/diagnoses` | `Web\DiagnosisController@index` | Search & lookup ICD-10 |
| **Obat (Farmasi)** |
| GET | `/medicines` | `Web\MedicineController@index` | List obat (search) |
| GET | `/medicines/create` | `Web\MedicineController@create` | Form obat baru |
| POST | `/medicines` | `Web\MedicineController@store` | Simpan obat |
| GET | `/medicines/{id}` | `Web\MedicineController@show` | Detail + stok |
| GET | `/medicines/{id}/edit` | `Web\MedicineController@edit` | Form edit |
| PUT | `/medicines/{id}` | `Web\MedicineController@update` | Update obat |
| **Resep** |
| GET | `/prescriptions` | `Web\PrescriptionController@index` | List resep |
| GET | `/prescriptions/create` | `Web\PrescriptionController@create` | Form resep baru |
| POST | `/prescriptions` | `Web\PrescriptionController@store` | Simpan resep |
| GET | `/prescriptions/{id}` | `Web\PrescriptionController@show` | Detail + items |
| GET | `/prescriptions/{id}/print` | `Web\PrescriptionController@print` | Cetak resep |
| **Inventaris Stok** |
| GET | `/inventories` | `Web\InventoryController@index` | List stok per batch |
| GET | `/inventories/create` | `Web\InventoryController@create` | Form tambah stok |
| POST | `/inventories` | `Web\InventoryController@store` | Simpan transaksi stok |
| GET | `/inventories/{id}` | `Web\InventoryController@show` | Detail + riwayat transaksi |
| GET | `/inventories/reports/low-stock` | `Web\InventoryController@lowStock` | Stok menipis |
| GET | `/inventories/reports/expiring` | `Web\InventoryController@expiring` | Akan kedaluwarsa |
| GET | `/inventories/reports/expired` | `Web\InventoryController@expired` | Kadaluwarsa |
| **Poliklinik** |
| GET | `/polyclinics` | `Web\PolyclinicController@index` | List poli |
| GET | `/polyclinics/create` | `Web\PolyclinicController@create` | Form poli baru |
| POST | `/polyclinics` | `Web\PolyclinicController@store` | Simpan poli |
| GET | `/polyclinics/{id}` | `Web\PolyclinicController@show` | Detail + dokter |
| GET | `/polyclinics/{id}/edit` | `Web\PolyclinicController@edit` | Form edit |
| PUT | `/polyclinics/{id}` | `Web\PolyclinicController@update` | Update poli |
| **Dokter** |
| GET | `/doctors` | `Web\DoctorController@index` | List dokter |
| GET | `/doctors/create` | `Web\DoctorController@create` | Form dokter baru |
| POST | `/doctors` | `Web\DoctorController@store` | Simpan dokter |
| GET | `/doctors/{id}` | `Web\DoctorController@show` | Detail |
| GET | `/doctors/{id}/edit` | `Web\DoctorController@edit` | Form edit |
| PUT | `/doctors/{id}` | `Web\DoctorController@update` | Update dokter |
| **BPJS SEP** |
| GET | `/bpjs-seps` | `Web\BpjsSepController@index` | List SEP BPJS |
| GET | `/bpjs-seps/create` | `Web\BpjsSepController@create` | Form SEP baru |
| POST | `/bpjs-seps` | `Web\BpjsSepController@store` | Simpan SEP |
| GET | `/bpjs-seps/{id}` | `Web\BpjsSepController@show` | Detail SEP |
| DELETE | `/bpjs-seps/{id}` | `Web\BpjsSepController@destroy` | Hapus SEP |
| **Kategori Lab** |
| GET | `/lab-test-categories` | `Web\LabTestCategoryController@index` | List kategori |
| GET | `/lab-test-categories/create` | `Web\LabTestCategoryController@create` | Form kategori baru |
| POST | `/lab-test-categories` | `Web\LabTestCategoryController@store` | Simpan kategori |
| GET | `/lab-test-categories/{id}/edit` | `Web\LabTestCategoryController@edit` | Form edit |
| PUT | `/lab-test-categories/{id}` | `Web\LabTestCategoryController@update` | Update kategori |
| DELETE | `/lab-test-categories/{id}` | `Web\LabTestCategoryController@destroy` | Hapus kategori |
| **Master Tes Lab** |
| GET | `/lab-tests` | `Web\LabTestController@index` | List tes lab |
| GET | `/lab-tests/create` | `Web\LabTestController@create` | Form tes baru |
| POST | `/lab-tests` | `Web\LabTestController@store` | Simpan tes |
| GET | `/lab-tests/{id}` | `Web\LabTestController@show` | Detail tes + rujukan |
| GET | `/lab-tests/{id}/edit` | `Web\LabTestController@edit` | Form edit |
| PUT | `/lab-tests/{id}` | `Web\LabTestController@update` | Update tes |
| DELETE | `/lab-tests/{id}` | `Web\LabTestController@destroy` | Hapus tes |
| **Permintaan Lab** |
| GET | `/lab-requests` | `Web\LabRequestController@index` | List permintaan (filter status) |
| GET | `/lab-requests/create` | `Web\LabRequestController@create` | Form permintaan baru |
| POST | `/lab-requests` | `Web\LabRequestController@store` | Simpan permintaan |
| GET | `/lab-requests/{id}` | `Web\LabRequestController@show` | Detail + hasil |
| PATCH | `/lab-requests/{id}/status` | `Web\LabRequestController@updateStatus` | Update status pipeline |
| **Hasil Lab** |
| GET | `/lab-results` | `Web\LabResultController@index` | List hasil (filter flag/tes) |
| GET | `/lab-results/input/{labRequest}` | `Web\LabResultController@input` | Form input hasil massal |
| POST | `/lab-results` | `Web\LabResultController@store` | Simpan hasil |
| GET | `/lab-results/{id}/edit` | `Web\LabResultController@edit` | Form edit hasil |
| PUT | `/lab-results/{id}` | `Web\LabResultController@update` | Update hasil |
| **Auth (Breeze)** |
| GET | `/login` | `Auth\AuthenticatedSessionController@create` | Halaman login |
| POST | `/login` | `Auth\AuthenticatedSessionController@store` | Proses login |
| POST | `/logout` | `Auth\AuthenticatedSessionController@destroy` | Logout |
| GET | `/register` | `Auth\RegisteredUserController@create` | Halaman register |
| POST | `/register` | `Auth\RegisteredUserController@store` | Proses register |
| GET | `/profile` | `ProfileController@edit` | Edit profil |
| ... | ... | ... | Reset password, verify email, dll |
| **BPJS (internal JSON, session auth)** |
| GET | `/bpjs/vclaim/peserta` | `Web\BPJS\VClaimController@peserta` | Cek peserta BPJS |
| POST | `/bpjs/vclaim/sep` | `Web\BPJS\VClaimController@sepStore` | Buat SEP |
| GET | `/bpjs/vclaim/sep/{noSep}` | `Web\BPJS\VClaimController@sepShow` | Detail SEP |
| PUT | `/bpjs/vclaim/sep` | `Web\BPJS\VClaimController@sepUpdate` | Update SEP |
| DELETE | `/bpjs/vclaim/sep` | `Web\BPJS\VClaimController@sepDelete` | Hapus SEP |
| POST | `/bpjs/vclaim/claim` | `Web\BPJS\VClaimController@claimStore` | Submit klaim |
| GET | `/bpjs/vclaim/claim/{noSep}/status` | `Web\BPJS\VClaimController@claimStatus` | Status klaim |
| GET | `/bpjs/vclaim/referensi/diagnosa` | `Web\BPJS\VClaimController@referensiDiagnosa` | Referensi ICD-10 BPJS |
| GET | `/bpjs/vclaim/referensi/poli` | `Web\BPJS\VClaimController@referensiPoli` | Referensi poli |
| GET | `/bpjs/vclaim/referensi/faskes` | `Web\BPJS\VClaimController@referensiFaskes` | Referensi faskes |
| POST | `/bpjs/antrol/antrean` | `Web\BPJS\AntrolController@addAntrean` | Tambah antrean BPJS |
| PUT | `/bpjs/antrol/antrean` | `Web\BPJS\AntrolController@updateAntrean` | Update antrean |
| DELETE | `/bpjs/antrol/antrean` | `Web\BPJS\AntrolController@deleteAntrean` | Hapus antrean |
| GET | `/bpjs/antrol/antrean/{kodePoli}/{tanggal}` | `Web\BPJS\AntrolController@getAntreanPoli` | Antrean per poli |
| GET | `/bpjs/antrol/dashboard/tanggal/{tanggal}` | `Web\BPJS\AntrolController@dashboardTanggal` | Dashboard harian |
| GET | `/bpjs/antrol/dashboard/bulan/{bulan}/{tahun}` | `Web\BPJS\AntrolController@dashboardBulan` | Dashboard bulanan |
| **Satu Sehat (internal JSON, session auth)** |
| POST | `/satusehat/sync/patient/{patient}` | `Web\SatuSehat\FHIRController@syncPatient` | Sync pasien |
| POST | `/satusehat/sync/encounter/{medical_record}` | `Web\SatuSehat\FHIRController@syncEncounter` | Sync encounter |
| POST | `/satusehat/sync/condition/{medical_record}` | `Web\SatuSehat\FHIRController@syncCondition` | Sync diagnosa |
| POST | `/satusehat/sync/observation/{medical_record}` | `Web\SatuSehat\FHIRController@syncObservation` | Sync vital sign |
| POST | `/satusehat/sync/medication-request/{prescription}` | `Web\SatuSehat\FHIRController@syncMedicationRequest` | Sync resep |
| POST | `/satusehat/sync/all/{medical_record}` | `Web\SatuSehat\FHIRController@syncAll` | Sync semua data |
| GET | `/satusehat/search/patient` | `Web\SatuSehat\FHIRController@searchPatient` | Cari pasien di SS |
| GET | `/satusehat/search/icd10` | `Web\SatuSehat\FHIRController@searchIcd10` | Cari ICD-10 di SS |
| GET | `/satusehat/search/loinc` | `Web\SatuSehat\FHIRController@searchLoinc` | Cari LOINC di SS |
| GET | `/satusehat/status` | `Web\SatuSehat\FHIRController@status` | Status sinkronasi |

### 9.2 API Routes — `routes/api.php`

Hanya untuk integrasi eksternal/aplikasi mobile (BPJS Kesehatan & Satu Sehat Kemenkes). Prefix: `/api/v1`. Semua endpoint di-protect oleh middleware `auth:sanctum`. Duplikasi dari `Web\BPJS\*` dan `Web\SatuSehat\*` di web routes — API layer dipertahankan untuk pengembangan mobile ke depan.

#### BPJS VClaim

| Method | Endpoint | Controller | Keterangan |
| ------ | -------- | ---------- | ---------- |
| GET | `/api/v1/bpjs/vclaim/peserta` | `VClaimController@peserta` | Cek peserta (by noKartu/nik) |
| POST | `/api/v1/bpjs/vclaim/sep` | `VClaimController@sepStore` | Buat SEP baru |
| GET | `/api/v1/bpjs/vclaim/sep/{noSep}` | `VClaimController@sepShow` | Detail SEP |
| PUT | `/api/v1/bpjs/vclaim/sep` | `VClaimController@sepUpdate` | Update SEP |
| DELETE | `/api/v1/bpjs/vclaim/sep` | `VClaimController@sepDelete` | Hapus SEP |
| POST | `/api/v1/bpjs/vclaim/claim` | `VClaimController@claimStore` | Submit klaim |
| GET | `/api/v1/bpjs/vclaim/claim/{noSep}/status` | `VClaimController@claimStatus` | Status klaim |
| GET | `/api/v1/bpjs/vclaim/referensi/diagnosa` | `VClaimController@referensiDiagnosa` | Referensi ICD-10 BPJS |
| GET | `/api/v1/bpjs/vclaim/referensi/poli` | `VClaimController@referensiPoli` | Referensi poli BPJS |
| GET | `/api/v1/bpjs/vclaim/referensi/faskes` | `VClaimController@referensiFaskes` | Referensi faskes |

#### BPJS Antrol

| Method | Endpoint | Controller | Keterangan |
| ------ | -------- | ---------- | ---------- |
| POST | `/api/v1/bpjs/antrol/antrean` | `AntrolController@addAntrean` | Tambah antrean BPJS |
| PUT | `/api/v1/bpjs/antrol/antrean` | `AntrolController@updateAntrean` | Update antrean |
| DELETE | `/api/v1/bpjs/antrol/antrean` | `AntrolController@deleteAntrean` | Hapus antrean |
| GET | `/api/v1/bpjs/antrol/antrean/{kodePoli}/{tanggal}` | `AntrolController@getAntreanPoli` | List antrean per poli |
| GET | `/api/v1/bpjs/antrol/dashboard/tanggal/{tanggal}` | `AntrolController@dashboardTanggal` | Dashboard harian |
| GET | `/api/v1/bpjs/antrol/dashboard/bulan/{bulan}/{tahun}` | `AntrolController@dashboardBulan` | Dashboard bulanan |

#### Satu Sehat FHIR

| Method | Endpoint | Controller | Keterangan |
| ------ | -------- | ---------- | ---------- |
| POST | `/api/v1/satusehat/sync/patient/{patient}` | `FHIRController@syncPatient` | Sync pasien |
| POST | `/api/v1/satusehat/sync/encounter/{medical_record}` | `FHIRController@syncEncounter` | Sync kunjungan |
| POST | `/api/v1/satusehat/sync/condition/{medical_record}` | `FHIRController@syncCondition` | Sync diagnosa |
| POST | `/api/v1/satusehat/sync/observation/{medical_record}` | `FHIRController@syncObservation` | Sync vital sign |
| POST | `/api/v1/satusehat/sync/medication-request/{prescription}` | `FHIRController@syncMedicationRequest` | Sync resep |
| POST | `/api/v1/satusehat/sync/all/{medical_record}` | `FHIRController@syncAll` | Sync semua resource |
| GET | `/api/v1/satusehat/search/patient` | `FHIRController@searchPatient` | Cari pasien di SS |
| GET | `/api/v1/satusehat/search/icd10` | `FHIRController@searchIcd10` | Cari ICD-10 di SS |
| GET | `/api/v1/satusehat/search/loinc` | `FHIRController@searchLoinc` | Cari LOINC di SS |
| GET | `/api/v1/satusehat/status` | `FHIRController@status` | Status koneksi SS |

---

## 10. Library & Dependencies

### 10.1 Composer Packages (Production)

| Package                           | Version   | Keterangan                                                              |
| --------------------------------- | --------- | ----------------------------------------------------------------------- |
| `php`                             | ^8.3      | PHP version requirement                                                 |
| `laravel/framework`               | ^13.0     | Core Laravel framework                                                  |
| `laravel/sanctum`                 | ^4.0      | API token authentication                                                |
| `guzzlehttp/guzzle`               | ^7.9      | HTTP client untuk request ke API BPJS & Satu Sehat                      |
| `barryvdh/laravel-dompdf`         | ^3.0      | Generate PDF untuk cetak antrean, resep, rekam medis                    |
| `maatwebsite/laravel-excel`       | ^3.1      | Export/import data ke Excel                                             |
| `intervention/image`              | ^3.x      | Manipulasi gambar (resize foto pasien, thumbnail)                       |
| `spatie/laravel-permission`       | ^6.0      | Role & permission management (opsional, bisa custom)                    |
| `spatie/data-transfer-object`     | ^3.9      | DTO untuk data transfer antar layer (opsional)                          |
| `mike42/escpos-php`               | ^2.2      | Cetak ke thermal printer (ESC/POS protocol)                             |
| `predis/predis`                   | ^2.2      | Redis client (opsional, jika menggunakan Redis)                         |
| `giggsey/libphonenumber-for-php`  | ^8.13     | Validasi & format nomor telepon internasional (opsional)                |
| `mpdf/mpdf`                       | ^8.2      | Alternatif DomPDF untuk PDF dengan fitur lebih kompleks (opsional)      |

### 10.2 Composer Packages (Development)

| Package                           | Version   | Keterangan                                                              |
| --------------------------------- | --------- | ----------------------------------------------------------------------- |
| `laravel/sail`                    | ^2.0      | Docker development environment (opsional)                               |
| `barryvdh/laravel-ide-helper`     | ^3.0      | IDE helper untuk autocompletion                                         |
| `laravel/telescope`               | ^5.0      | Debugging & monitoring (opsional)                                       |
| `knuckleswtf/scribe`              | ^4.0      | Generate dokumentasi API otomatis                                       |
| `phpunit/phpunit`                 | ^11.0     | Testing framework                                                       |
| `mockery/mockery`                 | ^1.6      | Mocking untuk unit test                                                 |
| `fakerphp/faker`                  | ^1.23     | Data generator untuk testing & seeding                                  |
| `laravel/horizon`                 | ^5.0      | Queue monitoring dashboard (opsional, butuh Redis)                      |

### 10.3 Node.js Packages (Frontend Asset)

| Package                           | Keterangan                                        |
| --------------------------------- | ------------------------------------------------- |
| `laravel-mix` / `vite`            | Asset bundler                                     |
| `bootstrap` (5.x)                 | CSS framework                                     |
| `alpine.js` / `vue.js`            | Frontend interactivity (opsional)                 |
| `axios`                           | HTTP client untuk frontend                        |
| `chart.js`                        | Grafik dashboard                                  |
| `select2` / `tom-select`          | Autocomplete untuk dropdown                       |

### 10.4 Service & Tool Dependencies (External)

| Service/Tool                      | Keterangan                                                              |
| --------------------------------- | ----------------------------------------------------------------------- |
| MySQL 8.0+ / MariaDB 10.6+        | Database relational utama                                               |
| Redis 6+ (opsional)               | Cache & queue driver (alternatif file-based)                            |
| Composer 2.x                      | PHP dependency manager                                                  |
| Node.js 20+ & NPM                 | Frontend assets                                                         |
| Nginx / Apache                    | Web server                                                              |
| Supervisor (Linux)                | Process monitor untuk queue worker                                      |
| Google TTS API / eSpeak           | Text-to-Speech engine                                                   |
| WebSocket Server (Pusher/laravel-websockets) | Realtime update display antrean                              |

---

*Dokumen ini adalah panduan implementasi teknis untuk proyek e-Klinik - Sistem Informasi Manajemen Klinik. Dokumen ini akan terus diperbarui seiring dengan perkembangan implementasi.*

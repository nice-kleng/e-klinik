# e-Klinik — Agent Guide

## Tech
- **Laravel 13 / PHP 8.3 / MySQL** — DB name `e_klinik`, configured in `.env`
- **Auth**: Laravel Sanctum (`auth:sanctum`) on all `/api/v1/*` routes; session auth for web (Breeze)
- **Role/Spatie**: `role:admin|receptionist|doctor|pharmacist|...` middleware on web route groups via Spatie Permission
- **CORS**: `config/cors.php` allows `*` origins on `api/*` + `sanctum/csrf-cookie`
- **Frontend**: Bootstrap 5 + Vite (`resources/css/app.css`, `resources/js/app.js`)

## Commands
| Command | What |
|---------|------|
| `composer dev` | Runs server + queue listener + logs + Reverb + Vite concurrently |
| `php artisan migrate` | Runs 45 migrations (all tables) |
| `php artisan db:seed` | Seeds: roles, master data (poli/dokter/kategori), meds, suppliers, patients, lab, ICD-10 |
| `php artisan satusehat:sync {type?} {--force}` | Sync unsynced data to Satu Sehat (patients/encounters/conditions/all) |
| `composer test` | `config:clear` then `phpunit` (SQLite :memory:) |

## Architecture

### Routes — `routes/web.php` (130+ routes, auth + Spatie role protected)
- Blade + **JSON internal endpoints** (BPJS & Satu Sehat, pakai session auth)
- Web controllers di `app/Http/Controllers/Web/BPJS/`, `Web/SatuSehat/`
- Response envelope JSON: `{ success: bool, data: ..., message: "..." }`

### Routes — `routes/api.php` (prefix `/api/v1`, 26 endpoints — untuk mobile)
- Protected by `auth:sanctum` (wrapped in `Route::middleware('auth:sanctum')`)
- BPJS: VClaim (10) + Antrol (6) — HMAC-SHA256 signed
- Satu Sehat: FHIR sync (7) + search (3) — OAuth2 Bearer
- Controllers inject service classes via constructor
- Response envelope: `{ success: bool, data: ..., message: "..." }`

### Service Layer
- `app/Services/` — business logic: QueueService, PatientService, MedicalRecordService, VoiceCallService, Icd10Service, InventoryService
- `app/Services/BPJS/` — BPJS bridging: VClaimService, AntrolService, AplicaresService (HMAC-SHA256 signature + AES-256-CBC response decrypt)
- `app/Services/SatuSehat/` — FHIR R4 integration: Patient, Encounter, Condition, Observation, MedicationRequest, Practitioner, Organization, Terminology, Auth (OAuth2 cached)
- Auto-sync: QueueService calls BPJS Antrol when patient is BPJS + BpjsSepService; PatientService/MedicalRecordService sync to Satu Sehat

### Integration Wiring — `app/Providers/IntegrationServiceProvider.php`
- Registered by `AppServiceProvider@register`
- All BPJS & Satu Sehat services bound as singletons
- Config from `config/bpjs.php` and `config/satusehat.php`

### Models — `app/Models/` (36 models)
- **Registrations**: entitas kunjungan, menggantikan peran ganda `queues`. Menyimpan umur per kunjungan, status layanan, SEP, sumber pendaftaran
- **Queue**: hanya untuk calling (waiting/called/in_progress/completed). Link ke Registration via `registration_id`
- **QueueCall**: riwayat panggilan per poli (siapa, kapan, keberapa kali)
- **QueueMilestone**: taskid BPJS Antrol 1-7
- Patient: SoftDeletes, social fields (education, mother_name, emergency_contact, allergy), traits: `HasCreatedBy`, `Filterable`
- MedicalRecord: SoftDeletes, casts `vital_signs`/`diagnosis_secondary` as array, link ke `registration_id`, traits: `HasCreatedBy`, `Filterable`
- BPJS models: BpjsPatient, BpjsClaim, BpjsReferral, BpjsAntrean, BpjsSep (now links to registration), BpjsJadwal
- Also: DoctorSchedule, MedicalRecordDetail, Attachment, IntegrationLog, Configuration, Notification

### Lab Module
- **5 migrations**: `lab_test_categories`, `lab_tests`, `lab_requests`, `lab_request_items`, `lab_results`
- **5 models**: LabTestCategory, LabTest, LabRequest, LabRequestItem, LabResult
- **4 controllers**: LabTestCategoryController, LabTestController, LabRequestController, LabResultController
- **11 Blade views**: CRUD for categories, tests, requests, results
- Lab results map to Satu Sehat Observation resource (LOINC)

### Observers (auto-sync to Satu Sehat)
- `app/Observers/PatientObserver` — calls `PatientService::syncPatient()` on create/update
- `app/Observers/MedicalRecordObserver` — calls `EncounterService::syncEncounter()`, `ConditionService::syncCondition()`, `ObservationService::syncObservation()` on create/update
- `app/Observers/PrescriptionObserver` — calls `MedicationRequestService::syncMedicationRequest()` on create
- Registered in `AppServiceProvider@boot`

### Key ENV (must set before integrations work)
```
DB_CONNECTION=mysql
BPJS_CONS_ID= / BPJS_SECRET_KEY= / BPJS_USER_KEY=
SATUSEHAT_CLIENT_ID= / SATUSEHAT_CLIENT_SECRET= / SATUSEHAT_ORGANIZATION_ID=
```

### Realtime (Laravel Reverb + Laravel Echo)
- **Laravel Reverb** installed (v1.10.2) — WebSocket server
- **laravel-echo** npm package installed — client-side WebSocket consumer
- `BROADCAST_CONNECTION=reverb` di `.env`
- `composer dev` menjalankan `php artisan reverb:start --debug`
- `app/Events/QueueUpdated` — broadcast event (public channel `queue`, implements `ShouldBroadcastNow`), dikirim dari:
  - `registerQueue()` — action `created` (real-time muncul di display TV)
  - `callAjax()` — action `called` (trigger TTS + banner)
  - `inProgress()/complete()/cancel()` — action `in_progress`/`completed`/`cancelled`
- Property event: `queueId`, `polyclinicId`, `status`, `action`, `queueNumber`, `patientName`, `polyclinicName`, `polyclinicQueueCount`, `actionPayload`
- `resources/js/app.js` — inisialisasi Echo, dengarkan `QueueUpdated`, tampilkan toast + dispatch custom event `queue-updated`
- `resources/views/queues/index.blade.php` — dengarkan `queue-updated`, auto-refresh tabel + TTS otomatis
- Display TV (`resources/views/queues/display-tv.blade.php`) — dengarkan `QueueUpdated` via Echo (filter by polyclinicId), show banner + TTS + rerender. Polling fallback setiap 5 detik
- Display landing (`resources/views/queues/display.blade.php`) — grid per poli dengan glassmorphism design
- Display routes (`/queues/display/tv`, `/queues/display-json`) tanpa middleware role (hanya session auth)
- **Penting:** Semua elemen DOM di display TV selalu di-render di HTML (sembunyikan dengan `style="display:none"`), bukan conditional `@if/@else` — mencegah null reference di JS `rerender()`

### TTS / Voice Call
- **Browser SpeechSynthesis API** — TTS real-time saat panggil antrean (bahasa Indonesia `id-ID`)
- Tombol "Dengarkan" di queue index untuk memutar ulang announcement
- Auto-TTS saat `QueueUpdated` dengan action `called` (dari Echo listener)
- `VoiceCallService` → `TtsProvider` interface (GoogleCloudTtsProvider | BrowserTtsProvider fallback)
- Switched by `services.google_tts.api_key` config
- Announcement templates in Indonesian: `"Nomor antrean {number}, {nama}, silakan menuju {poli}"`

### Queue Controller — Web Actions
- **`callAjax()`** — method AJAX tanpa redirect. Panggil `callAndProgress()` + fire `QueueUpdated` action `called`
- **`queueData()`** — JSON endpoint detail queue (status, patient, polyclinic)
- **`inProgress()`/`complete()`/`cancel()`** — dual response: JSON untuk AJAX (`expectsJson()`), redirect untuk form
- **Tombol di index:** Panggil (AJAX), Proses/Selesai/Batal (AJAX), Detail (link redirect)
- **Role-aware:** receptionist hanya lihat Panggil + Batal, doctor/perawat lihat semua aksi

## Conventions
- All response messages in **Indonesian**
- Queue lifecycle: waiting → in_progress → completed (or cancelled) — **Panggil langsung in_progress**, tidak ada status `called` persisten. `called` hanya digunakan sebagai action event untuk trigger TTS/banner
- Registration lifecycle: registered → in_consultation → lab/pharmacy/cashier → completed (or cancelled)
- Soft deletes: users, patients, medical_records, medicines
- ICD-10 seeder is a **migration** (not seeder class) at `database/migrations/000021_*`

## Architecture Notes (Registration-refactor)
- **`registrations`** table baru: mencatat setiap kunjungan pasien. Memiliki `registration_number`, `age_text/years/months/days` (dihitung saat daftar), `service_status` end-to-end, `no_sep`, `bpjs_antrian_id`
- **`queues`** disederhanakan: hanya untuk calling. Kolom `registration_id` (FK), `queue_sequence` (integer), `source` (walk_in/mjkn), `confirmed_at` (nullable untuk MJKN). Kolom lama dihapus: `patient_id`, `doctor_id`, `service_type`, `bpjs_antrian_id`, `bpjs_sep_id`, `estimated_wait_time`, `called_at`, `completed_at`, `notes`
- **`queue_calls`** baru: riwayat pemanggilan per antrean (call_sequence, called_by, called_at, responded_at)
- **`queue_milestones`** baru: taskid BPJS Antrol 1-7
- **Patient** tambah kolom: `education`, `mother_name`, `emergency_contact`, `allergy`
- **MedicalRecord** tambah kolom: `registration_id` (FK)
- **BpjsSep** tambah kolom: `registration_id` (FK)
- **Role baru**: `receptionist` (untuk pendaftaran, pisah dari cashier)
- **Role baru**: `receptionist` (untuk pendaftaran, pisah dari cashier)
- **Relasi**: `patients → registrations → queues → queue_calls/milestones`
- Akses pasien via Queue: `$queue->registration->patient` (bukan `$queue->patient`)
- Akses dokter via Queue: `$queue->registration->doctor` (bukan `$queue->doctor`)

## Roles (via Spatie)
| Role | Modul |
|------|-------|
| `admin` | Semua |
| `receptionist` | Pendaftaran, pasien, antrean |
| `doctor` | RME, resep, lab, antrean polinya sendiri |
| `nurse` | Bantu dokter |
| `pharmacist` | Apotek & inventaris |
| `cashier` | Kasir & piutang (Phase 5) |
| `laborant` | Lab |

## Login defaults (after seed)
| Email | Password | Role |
|-------|----------|------|
| admin@e-klinik.com | admin123 | admin |
| receptionist@e-klinik.com | receptionist123 | receptionist |
| dokter@e-klinik.com | dokter123 | doctor |
| apoteker@e-klinik.com | apoteker123 | pharmacist |
| laboran@e-klinik.com | laboran123 | laborant |
| kasir@e-klinik.com | kasir123 | cashier |
| perawat@e-klinik.com | perawat123 | nurse |

## Known gaps
- **No tests** — phpunit.xml exists (SQLite in-memory) but `tests/` is empty
- **Volt Admin template** — not yet overlaid
- **BPJS/SatuSehat ENV** — credentials kosong, bridging tidak bisa diuji
- **Email config** — default Laravel, belum diganti

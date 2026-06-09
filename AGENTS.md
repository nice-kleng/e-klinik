# e-Klinik — Agent Guide

## Tech
- **Laravel 13 / PHP 8.3 / MySQL** — DB name `e_klinik`, configured in `.env`
- **Auth**: Laravel Sanctum (`auth:sanctum`) on all `/api/v1/*` routes; session auth for web (Breeze)
- **Role middleware**: `role:admin,doctor,...` on web route groups (admin=full, doctor=RME, pharmacist=farmasi, cashier=pendaftaran)
- **CORS**: `config/cors.php` allows `*` origins on `api/*` + `sanctum/csrf-cookie`
- **Frontend**: Bootstrap 5 + Vite (`resources/css/app.css`, `resources/js/app.js`)

## Commands
| Command | What |
|---------|------|
| `composer dev` | Runs server + queue listener + logs + Vite concurrently |
| `php artisan migrate` | Runs 29 migrations (all tables) |
| `php artisan satusehat:sync {type?} {--force}` | Sync unsynced data to Satu Sehat (patients/encounters/conditions/all) |
| `php artisan db:seed --class=LabDataSeeder` | Seeds lab categories + 20 lab tests + laborant user |
| `php artisan db:seed --class=Icd10Seeder` | Seeds ~1200 ICD-10 codes |
| `composer test` | `config:clear` then `phpunit` (SQLite :memory:) |

## Architecture

### Routes — `routes/web.php` (130+ routes, auth + role protected)
- Blade + **JSON internal endpoints** (BPJS & Satu Sehat duplikasi dari API, pakai session auth)
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
- Auto-sync: QueueService calls BPJS Antrol when patient is BPJS; PatientService/MedicalRecordService sync to Satu Sehat

### Integration Wiring — `app/Providers/IntegrationServiceProvider.php`
- Registered by `AppServiceProvider@register`
- All BPJS & Satu Sehat services bound as singletons
- Config from `config/bpjs.php` and `config/satusehat.php`

### Models — `app/Models/` (25 models)
- Patient: SoftDeletes, `age` accessor, `bpjsPatient` HasOne
- MedicalRecord: SoftDeletes, casts `vital_signs`/`diagnosis_secondary` as array
- SatusehatResource: polymorphic morphTo `model()`
- BPJS models: BpjsPatient, BpjsClaim, BpjsReferral, BpjsAntrean

### Lab Module
- **5 migrations**: `lab_test_categories`, `lab_tests`, `lab_requests`, `lab_request_items`, `lab_results`
- **5 models**: LabTestCategory, LabTest, LabRequest, LabRequestItem, LabResult
- **4 controllers**: LabTestCategoryController, LabTestController, LabRequestController, LabResultController
- **11 Blade views**: CRUD for categories, tests, requests, results
- **23 routes**: protected by `role:admin,laborant` (master data), `role:admin,doctor,laborant` (requests), `role:admin,laborant,doctor` (results)
- Lab results map to Satu Sehat Observation resource (LOINC)
- Role `laborant` added to users ENUM

### Key ENV (must set before integrations work)
```
DB_CONNECTION=mysql
BPJS_CONS_ID= / BPJS_SECRET_KEY= / BPJS_USER_KEY=
SATUSEHAT_CLIENT_ID= / SATUSEHAT_CLIENT_SECRET= / SATUSEHAT_ORGANIZATION_ID=
```

### TTS / Voice Call
- `VoiceCallService` → `TtsProvider` interface (GoogleCloudTtsProvider | BrowserTtsProvider fallback)
- Switched by `services.google_tts.api_key` config
- Announcement templates in Indonesian

## Conventions
- All response messages in **Indonesian**
- Queue lifecycle: waiting → called → in_progress → completed (or cancelled)
- Soft deletes: users, patients, medical_records, medicines
- ICD-10 seeder is a **migration** (not seeder class) at `database/migrations/000021_*`

## Known gaps
- **No tests** — phpunit.xml exists (SQLite in-memory) but `tests/` is empty
- **Volt Admin template** — not yet overlaid
- **BPJS/SatuSehat ENV** — credentials kosong, bridging tidak bisa diuji
- **Email config** — default Laravel, belum diganti
- **Display TV** — hanya JSON endpoint, belum ada UI real-time

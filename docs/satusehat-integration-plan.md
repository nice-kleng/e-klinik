# Satu Sehat Integration Plan — e-Klinik

## Status per Resource (Real)

| Resource | FHIR Type | Status | Keterangan |
|----------|-----------|--------|------------|
| Patient | `Patient` | ✅ 10 sync | FHIR profile compliance + KD codes extensions |
| Location | `Location` | ✅ 10 sync | Per polyclinic, IHS IDs tersimpan |
| Practitioner | `Practitioner` | ❌ 403 staging | Create tidak diizinkan di staging. Butuh KFA registration (production) |
| Encounter | `Encounter` | ❌ butuh Practitioner | participant mandatory (RuleNumber 10336) |
| Condition | `Condition` | ❌ butuh Encounter | chain dependency |
| Observation | `Observation` | ❌ butuh Encounter | vital signs + lab |
| MedicationRequest | `MedicationRequest` | ❌ butuh Practitioner + KFA code | perlu mapping obat + practitioner |

**OAuth2:** ✅ Berhasil — `POST /oauth2/v1/accesstent?grant_type=client_credentials`
- Token didapat, format Bearer
- `base_uri` trailing slash penting agar path `/v1/` tidak terganti

## Staging Environment
- **Base URL:** `https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1`
- **Auth URL:** `https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1`
- **Organization ID:** `b740515b-2e63-476f-81df-91c10bce9f39`
- **KFA CodeSystem:** Ada tapi 0 entries (`total:0`)
- **KFA `$search`/$lookup:** ❌ Tidak didukung — error `bad_resource_id`
- **KFA v2 API:** ❌ 404 Not Found
- **Practitioner create:** ❌ 403 Forbidden (consent/privacy rules)
- **400-with-id:** Dianggap sukses — Satu Sehat kadang return 400 + `id` resource (validation warnings)

## Chain Dependency

```
KFA ──▶ Practitioner ──▶ Encounter ──▶ Condition
                  │                        Observation
                  │                        MedicationRequest
                  │
            Location ───▶ (independent, ✅ sudah sync)
```

## Database Changes (Sudah diimplementasi)

### 1. `users.nik`
```php
$table->string('nik', 20)->unique()->nullable()->after('name');
```
- Migration: `2026_06_18_083000_add_nik_to_users_table.php`
- 6 dokter user seed dengan NIK (3174010101900011–0016)
- Digunakan untuk Practitioner search by NIK di Satu Sehat

### 2. `medicines.kfa_code` + `medicines.kfa_name`
```php
$table->string('kfa_code', 20)->nullable()->after('code');
$table->string('kfa_name')->nullable()->after('kfa_code');
```
- Migration: `2026_06_18_083002_add_kfa_code_to_medicines_table.php`
- 35 obat di database, **0 punya KFA code** (menunggu mapping via UI)

### 3. `regions` table (master wilayah KEMENDAGRI)
```php
Schema::create('regions', function (Blueprint $table) {
    $table->id();
    $table->string('code', 20)->index();
    $table->string('name');
    $table->enum('level', ['province', 'city', 'district', 'village']);
    $table->foreignId('parent_id')->nullable()->constrained('regions');
    $table->timestamps();
});
```
- Migration: `2026_06_18_083001_create_regions_table.php`
- Model: `app/Models/Region.php`
- Seeder: `database/seeders/RegionSeeder.php` — fetch API SS → JSON fallback
- Data: `database/seeders/data/regions.json` — 38 provinsi, 10 kota, 10 kecamatan, 10 kelurahan
- Level: `province` → `city` → `district` → `village` dengan `parent_id` FK

### 4. `patients` — KD codes (denormalized)
```php
$table->string('province_kd', 10)->nullable();
$table->string('city_kd', 10)->nullable();
$table->string('district_kd', 10)->nullable();
$table->string('village_kd', 10)->nullable();
```
- Migration: `2026_06_18_081912_add_kd_codes_to_patients_table.php`
- Digunakan untuk FHIR Patient.address.extension (administrativeCode)
- Lookup: `PatientService::lookupKdCodes()` — 12 provinsi + 10 kota mapping, district/village otomatis dari city prefix

### 5. `satusehat_resources.version` — length increase
```php
$table->string('version', 50)->change(); // 20→50
```
- Migration: `2026_06_18_082425_increase_version_length_in_satusehat_resources.php`
- VersionId Satu Sehat bisa ~27 chars (base64 encoded)

## Services (Sudah diimplementasi)

| Service | File | Keterangan |
|---------|------|------------|
| AuthService | `app/Services/SatuSehat/AuthService.php` | OAuth2 `accesstent?grant_type=client_credentials`, caching token |
| SatuSehatClient | `app/Services/SatuSehat/SatuSehatClient.php` | HTTP client, error handling (400-with-id = success), logging |
| PatientService | `app/Services/SatuSehat/PatientService.php` | Build resource + KD codes + sync (search NIK dulu) |
| LocationService | `app/Services/SatuSehat/LocationService.php` | Create/get/search/syncLocation untuk FHIR Location |
| PractitionerService | `app/Services/SatuSehat/PractitionerService.php` | Sync by NIK, fallback create (blocked 403) |
| EncounterService | `app/Services/SatuSehat/EncounterService.php` | identifier, participant conditional, statusHistory, location |
| OrganizationService | `app/Services/SatuSehat/OrganizationService.php` | Validate organization di Satu Sehat |
| TerminologyService | `app/Services/SatuSehat/TerminologyService.php` | searchIcd10, searchLoinc, searchKfa, lookupIcd10, lookupLoinc |

### Key Implementation Details

**PatientService:**
- `buildPatientResource()` — nama.text, multipleBirthBoolean, address extensions (province/city/district/village KD codes)
- `resolveKdCodes()` — lookup dari map + district/village auto dari city prefix
- `syncPatient()` — search by NIK dulu → jika ada, simpan reference + skip update (hindari 403 consent)
- `lookupKdCodes()` — 12 provinsi (31,32,33,35,12,34,16,73,11) + 10 kota (3171,3273,3374,3578,1275,3471,3372,1671,7371,1171)

**EncounterService:**
- `buildEncounterResource()` — identifier (visit number), participant conditional (hanya jika practitioner exist), statusHistory, location via LocationService
- Participant mandatory di FHIR profile → butuh Practitioner IHS ID

**TerminologyService:**
- `searchKfa()` — GET `CodeSystem/$search?system=http://sys-ids.kemkes.go.id/kfa&filter=...`
- ❌ Gagal di staging: `bad_resource_id` — CodeSystem $search tidak didukung
- Fallback: return null, UI tampilkan "Tidak ada hasil"

## Commands

| Command | Keterangan |
|---------|------------|
| `php artisan satusehat:sync {type?} {--force}` | Sync batch ke Satu Sehat |
| | Type: `patients`, `practitioners`, `locations`, `encounters`, `conditions`, `all` |
| | `--force`: re-sync meski sudah pernah sync |

SyncSatusehat command upgrade:
- +tipe `practitioners`, `locations`
- Dependency checking: patient→practitioner→encounter→condition

## Menu "Satu Sehat" (Sudah diimplementasi)

```
Satu Sehat (admin only)
├── Dashboard           → ringkasan status sync per resource + org ID + token status
├── Organization        → tampilkan data faskes dari API SS (validateOrganization)
├── Location            → daftar poli + status sync + tombol sync AJAX
├── Practitioner        → daftar dokter + NIK + IHS ID + sync AJAX + catatan
├── Obat & Alkes        → mapping KFA code (search modal + manual entry)
└── Sync Log            → riwayat API calls (satusehat_logs) + pagination + payload viewer modal
```

- Controller: `app/Http/Controllers/Web/SatuSehat/SatuSehatController.php` (9 method)
- Views: `resources/views/satusehat/` (6 blade files)
- Routes: 9 route `satusehat.*` di `routes/web.php`
- Navigation: sidebar menu ditambahkan di `resources/views/layouts/navigation.blade.php`

### KFA Mapping UI
- Tabel obat dengan status mapping (badge Mapping/Belum)
- Tombol **"Cari KFA"** → modal search → `TerminologyService::searchKfa()`
- Tombol **"Hapus"** untuk menghapus mapping
- Auto-search dengan keyword nama obat saat modal dibuka
- Input live search (debounce 400ms, min 2 karakter)
- ❌ **Staging limitation:** KFA CodeSystem tidak punya data (0 entries), search selalu kosong
- ✅ **Manual entry:** Bisa input manual KFA code + name via updateKfa endpoint

## Observers (Auto-sync)

| Observer | Trigger | Service |
|----------|---------|---------|
| `PatientObserver` | create/update Patient | `PatientService::syncPatient()` |
| `MedicalRecordObserver` | create/update MedicalRecord | `EncounterService`, `ConditionService`, `ObservationService` |
| `PrescriptionObserver` | create Prescription | `MedicationRequestService::syncMedicationRequest()` |

Registered in `AppServiceProvider@boot`

## Known Gaps / Blockers

| Issue | Detail | Workaround |
|-------|--------|------------|
| Practitioner 403 | Staging tidak izinkan create Practitioner | Tunggu production (KFA registered practitioners) |
| KFA CodeSystem kosong | `total:0` di staging | Mapping manual via UI, atau seed KFA codes umum |
| KFA `$search`/$lookup | `bad_resource_id` di staging | Gunakan fallback manual entry |
| KFA v2 API | 404 di staging | Tidak ada alternatif staging |
| Encounter participant mandatory | Butuh Practitioner IHS ID | Skip sampai Practitioner tersedia |
| 35 medicines belum mapping | 0/35 punya KFA code | Perlu diisi via UI (search gagal → manual input) |

## Alur Mapping Obat (Current)

1. Buka menu **Obat & Alkes**
2. Lihat daftar obat yang belum punya `kfa_code` (35 obat)
3. Klik "Cari KFA" → `TerminologyService::searchKfa(keyword)`
4. **Jika API jalan:** modal menampilkan hasil dari Satu Sehat KFA → user pilih → simpan
5. **Jika API gagal (staging):** modal kosong → user bisa input manual KFA code
6. Update via `POST /satusehat/update-kfa/{medicine}` → simpan `kfa_code` + `kfa_name`
7. Setelah mapping, siap sync `MedicationRequest` via `MedicationRequestService`

## Alur Wilayah (Regions)

1. Seeder `RegionSeeder`: fetch dari Satu Sehat KEMENDAGRI CodeSystem
   - Jika API offline, fallback ke file JSON (`database/seeders/data/regions.json`)
2. PatientService: lookup codes dari `regions` table (belum di-integrasi, masih pakai hardcoded map)
3. Saat daftar pasien: pilih wilayah dari dropdown cascade (prov → city → district → village) — **belum diimplementasi**

## Prioritas Implementasi (Sudah dikerjakan semua)

| # | Task | Outcome | Status |
|---|------|---------|--------|
| 1 | Migration `nik` ke `users` + update seeder | Dokter punya NIK | ✅ Selesai |
| 2 | Migration `kfa_code` ke `medicines` | Obat punya mapping | ✅ Selesai |
| 3 | Migration + Seeder `regions` table | Master wilayah lokal | ✅ Selesai |
| 4 | Update PatientService pakai regions table | No hardcoded codes | ⏸️ Ditunda (masih hardcoded map) |
| 5 | Buat SatuSehatController + Blade views | Menu Satu Sehat siap | ✅ Selesai |
| 6 | Sub-menu Dashboard + Organization | Lihat status + org | ✅ Selesai |
| 7 | Sub-menu Location | Sync/lokasi poli | ✅ Selesai |
| 8 | Sub-menu Practitioner | Sync dokter (NIK + IHS ID) | ✅ Selesai (blocked 403) |
| 9 | Sub-menu Obat & Alkes + KFA search | Mapping obat | ✅ Selesai (search gagal staging) |
| 10 | Sync MedicationRequest | Data obat terkirim | ⏸️ Butuh KFA code + Practitioner |
| 11 | Sync Encounter + Condition + Observation | RME terkirim | ⏸️ Butuh Practitioner (production) |

## Files Referensi

- `app/Services/SatuSehat/AuthService.php` — OAuth2
- `app/Services/SatuSehat/SatuSehatClient.php` — HTTP client + error handling
- `app/Services/SatuSehat/PatientService.php` — Patient FHIR resource builder
- `app/Services/SatuSehat/EncounterService.php` — Encounter with participant conditional
- `app/Services/SatuSehat/LocationService.php` — Location FHIR resource
- `app/Services/SatuSehat/PractitionerService.php` — Practitioner sync (blocked)
- `app/Services/SatuSehat/OrganizationService.php` — Organization validation
- `app/Services/SatuSehat/TerminologyService.php` — Terminology search (KFA, ICD-10, LOINC)
- `app/Console/Commands/SyncSatusehat.php` — Batch sync command
- `app/Http/Controllers/Web/SatuSehat/SatuSehatController.php` — 9 method controller
- `app/Models/Region.php` — Region model
- `app/Models/SatusehatResource.php` — Resource sync tracker
- `app/Models/SatusehatLog.php` — API log
- `database/seeders/RegionSeeder.php` — Region data seeder
- `database/seeders/data/regions.json` — 68 region entries (fallback)
- `resources/views/satusehat/*.blade.php` — 6 blade views
- `routes/web.php` — 9 route `satusehat.*`
- `config/satusehat.php` — 7 config keys via env

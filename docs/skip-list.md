# Skip List — Fitur yang Ditunda karena Keterbatasan Credential

Dokumen ini mencatat fitur dan test yang sengaja dilewati (*skipped*) karena **belum memiliki akses/kredensial** ke sistem eksternal. Daftar ini akan dipakai sebagai acuan saat kredensial sudah tersedia.

---

## 1. BPJS Kesehatan — Trustmark Belum Tersedia

**Status:** ❌ Semua test & fitur bridging BPJS di-skip  
**Penyebab:** Belum mendapatkan akun Trustmark dari BPJS Kesehatan  
**Env vars yang dibutuhkan:**

| Variable | Keterangan |
|----------|-----------|
| `BPJS_CONS_ID` | Consumer ID dari BPJS |
| `BPJS_SECRET_KEY` | Secret Key dari BPJS |
| `BPJS_USER_KEY` | User Key dari BPJS |
| `BPJS_BASE_URL` | Base URL (default: `https://apijkn.bpjs-kesehatan.go.id`) |

### 1.1 VClaim API — SEP & Peserta

| Fitur | Service/Controller | Lokasi |
|-------|-------------------|--------|
| Verifikasi Peserta (NIK/noKartu) | `VClaimService::getPeserta()` | `app/Services/BPJS/VClaimService.php:18` |
| Insert SEP | `VClaimService::insertSep()` | `app/Services/BPJS/VClaimService.php:30` |
| Update SEP | `VClaimService::updateSep()` | `app/Services/BPJS/VClaimService.php:36` |
| Delete SEP | `VClaimService::deleteSep()` | `app/Services/BPJS/VClaimService.php:42` |
| SEP otomatis dari antrean | `BpjsSepService::createFromQueue()` | `app/Services/BpjsSepService.php:20` |
| SEP update via form | `BpjsSepService::updateSep()` | `app/Services/BpjsSepService.php:90` |
| SEP manual (WebController) | `BpjsSepController::store()` | `app/Http/Controllers/Web/BpjsSepController.php` |
| SEP API | `Api\BPJS\VClaimController` | `app/Http/Controllers/Api/BPJS/VClaimController.php` |
| Cek status BPJS pasien | `PatientService::checkBpjsStatus()` | `app/Services/PatientService.php:149` |
| Klaim (Insert/Status) | `VClaimService` | `app/Services/BPJS/VClaimService.php` |
| Rujukan (CRUD) | `VClaimService` | `app/Services/BPJS/VClaimService.php` |
| Aplicares (referensi faskes) | `AplicaresService` | `app/Services/BPJS/AplicaresService.php` |

### 1.2 Antrol API — Antrean BPJS

| Fitur | Service/Controller | Lokasi |
|-------|-------------------|--------|
| Add Antrean | `AntrolService::addAntrean()` | `app/Services/BPJS/AntrolService.php:19` |
| Update Antrean | `AntrolService::updateAntrean()` | `app/Services/BPJS/AntrolService.php:28` |
| Delete Antrean | `AntrolService::deleteAntrean()` | `app/Services/BPJS/AntrolService.php:37` |
| Sinkronasi antrean BPJS | `QueueService::syncToBpjs()` | `app/Services/QueueService.php:240` |
| Cancel antrean BPJS | `QueueService::cancelBpjsAntrean()` | `app/Services/QueueService.php:279` |
| Web Antrol Controller | `Web\BPJS\AntrolController` | `app/Http/Controllers/Web/BPJS/AntrolController.php` |
| API Antrol Controller | `Api\BPJS\AntrolController` | `app/Http/Controllers/Api/BPJS/AntrolController.php` |

### 1.3 BPJS Routes (Blade Views)

| Route | Fungsi |
|-------|--------|
| `bpjs-seps.*` (CRUD) | Manajemen SEP via Web UI |
| `bpjs.vclaim.*` | JSON endpoints VClaim (session auth) |
| `bpjs.antrol.*` | JSON endpoints Antrol (session auth) |
| `/api/v1/bpjs/vclaim/*` | API endpoint VClaim (Sanctum) |
| `/api/v1/bpjs/antrol/*` | API endpoint Antrol (Sanctum) |

---

## 2. Satu Sehat — Kredensial Sandbox Tersedia

**Status:** ✅ Sandbox key sudah ada (`SATUSEHAT_CLIENT_ID`, `SATUSEHAT_CLIENT_SECRET`, `SATUSEHAT_ORGANIZATION_ID`)  
**Catatan:** Test integration Satu Sehat bisa dijalankan di environment sandbox. Unit test tetap pakai mock.

### 2.1 FHIR Resource Sync

| Fitur | Service | Lokasi |
|-------|---------|--------|
| OAuth2 Token | `SatuSehat\AuthService` | `app/Services/SatuSehat/AuthService.php` |
| FHIR HTTP Client | `SatuSehatClient` | `app/Services/SatuSehat/SatuSehatClient.php` |
| Patient resource | `SatuSehat\PatientService` | `app/Services/SatuSehat/PatientService.php` |
| Encounter resource | `SatuSehat\EncounterService` | `app/Services/SatuSehat/EncounterService.php` |
| Condition resource | `SatuSehat\ConditionService` | `app/Services/SatuSehat/ConditionService.php` |
| Observation resource | `SatuSehat\ObservationService` | `app/Services/SatuSehat/ObservationService.php` |
| MedicationRequest | `SatuSehat\MedicationRequestService` | `app/Services/SatuSehat/MedicationRequestService.php` |
| Organization | `SatuSehat\OrganizationService` | `app/Services/SatuSehat/OrganizationService.php` |
| Practitioner | `SatuSehat\PractitionerService` | `app/Services/SatuSehat/PractitionerService.php` |
| Terminology | `SatuSehat\TerminologyService` | `app/Services/SatuSehat/TerminologyService.php` |

### 2.2 Observer Auto-Sync

| Observer | Memicu |
|----------|--------|
| `PatientObserver` | create/update Patient → sync ke Satu Sehat |
| `MedicalRecordObserver` | create/update MedicalRecord → sync Encounter, Condition, Observation |
| `PrescriptionObserver` | create Prescription → sync MedicationRequest |

---

## 3. Prioritas Eksekusi Saat Kredensial Tersedia

### Jika BPJS Trustmark tiba:

1. **Test manual dulu** — coba endpoint VClaim Peserta via route `bpjs.vclaim.peserta`
2. **Jalankan feature test BPJS** — hapus baris `$this->markTestSkipped()` dari `BpjsSepTest.php`
3. **Jalankan unit test BpjsSepService** — hapus skip dari `BpjsSepServiceTest.php`
4. **Integrasi Antrol** — test sync antrean dari QueueService
5. **SEP otomatis** — test `BpjsSepService::createFromQueue()` end-to-end
6. **Aktifkan observer BPJS** jika ada

### Jika Satu Sehat Production (bukan sandbox):

1. **Test OAuth2** — pastikan token bisa didapatkan
2. **Test FHIR Patient** — sync satu pasien
3. **Test FHIR Encounter** — sync satu kunjungan
4. **Test FHIR Condition + Observation** — sync diagnosa & hasil lab
5. **Aktifkan observer** — pastikan auto-sync bekerja tanpa error

---

## 4. Ringkasan

| Sistem | Credential | Test Unit | Test Feature | Integration Test |
|--------|-----------|-----------|-------------|-----------------|
| BPJS VClaim | ❌ | ✅ (mock) + skip real call | ✅ (mock) + skip real call | ❌ skip semua |
| BPJS Antrol | ❌ | ✅ (mock) + skip real call | ✅ (mock) + skip real call | ❌ skip semua |
| Satu Sehat FHIR | ✅ sandbox | ✅ (mock) | ✅ (mock) | ⏳ sandbox bisa running |
| TTS Google Cloud | ❌ | ✅ (mock) | ✅ (mock) | ❌ skip |

**Keterangan:**
- ✅ = sudah siap
- ❌ = tidak bisa dijalankan (belum ada credential)
- ⏳ = bisa tapi belum optimal

---

*Dokumen ini diperbarui otomatis. Update saat credential baru tersedia.*

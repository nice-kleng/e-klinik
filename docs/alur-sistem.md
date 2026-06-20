# Alur Sistem e-Klinik

Dokumen ini memetakan alur lengkap sistem dari hulu ke hilir — setiap modul,
state machine, trigger, dan integrasi.

---

## 1. State Machine — Service Status

Status kunjungan pasien dilacak di kolom `registrations.service_status`.

```
                 ┌─────────────────────────────────────┐
                 │            registered                │
                 │  (Pasien selesai daftar)             │
                 └────────────┬────────────────────────┘
                              │ TriageService::create()
                              ▼
                 ┌─────────────────────────────────────┐
                 │             triage                   │
                 │  (TTV & screening oleh perawat)      │
                 └────────────┬────────────────────────┘
                              │ QueueService::callAndProgress()
                              ▼
                 ┌─────────────────────────────────────┐
                 │         in_consultation              │
                 │  (Dokter periksa, isi RME)           │
                 └──────┬──────────────┬───────────────┘
                        │              │
          EducationService::create()   │ (langsung resume)
                        ▼              ▼
            ┌──────────────────┐  ┌──────────────────┐
            │    education     │  │    completed      │
            │  (Edukasi pasien)│  │  (Selesai)        │
            └────────┬─────────┘  └──────────────────┘
                     │ VisitSummaryService::create()
                     ▼
            ┌──────────────────┐
            │    completed      │
            │  (Selesai)        │
            └──────────────────┘

                    Setiap status bisa ke cancelled:
                    QueueService::cancel() — segala status → cancelled
```

| Status | Dicapai via | Penanggung Jawab |
|--------|-------------|-------------------|
| `registered` | `QueueService::registerQueue()` | Resepsionis |
| `triage` | `TriageService::create()` | Perawat |
| `in_consultation` | `QueueService::callAndProgress()` / `inProgress()` | Staff Poli (dokter/perawat) |
| `education` | `EducationService::create()` | Dokter/Perawat |
| `completed` | `QueueService::complete()` atau `VisitSummaryService::create()` | Staff Poli / Dokter |
| `cancelled` | `QueueService::cancel()` | Staff Poli / Resepsionis |

---

## 2. Alur Lengkap — Pasien Datang → Selesai

```
PASIEN DATANG
      │
      ▼
┌────────────────────────────────────────────────────────┐
│ 1. PENDAFTARAN                                         │
│    Route: /registration (role: admin|receptionist)     │
│    Controller: RegistrationController                  │
│    Service: QueueService::registerQueue()              │
│    Database: INSERT registrations + queues             │
│    Event: QueueUpdated (action: created) → Display TV  │
│    Output: Tiket antrean cetak                         │
├────────────────────────────────────────────────────────┤
│    Alur:                                               │
│      → Cari pasien (NIK/no RM) atau buat baru          │
│      → Pilih poli + dokter + sumber (walk-in/MJKN)     │
│      → Visit type: auto-detect (Baru/Lama/Kontrol/     │
│        Rujukan), bisa di-override receptionist         │
│      → System: INSERT registration (status=registered) │
│      → System: INSERT queue (status=waiting)           │
│      → System: Broadcast QueueUpdated                  │
│      → Cetak tiket antrean                             │
│      → Pasien ke poli tunggu                           │
└──────────────────────┬─────────────────────────────────┘
                       │
                       ▼
┌────────────────────────────────────────────────────────┐
│ 2. TRIAGE (Opsional, tergantung alur klinik)           │
│    Route: /triage/{registration}/create                │
│    Controller: TriageController                        │
│    Service: TriageService::create()                    │
│    Database: INSERT triage + UPDATE service_status     │
├────────────────────────────────────────────────────────┤
│    Alur:                                               │
│      → Perawat isi TTV (TD, Nadi, Suhu, RR, SpO2,     │
│        BB, TB, GCS, Gula Darah)                        │
│      → Screening (alergi, risiko jatuh, dll)           │
│      → System: INSERT triage                           │
│      → System: UPDATE service_status = 'triage'        │
│      → Tombol Panggil aktif di halaman antrean         │
│      → QueueService::callNext() cek triage sudah ada   │
│        (throw jika belum)                              │
└──────────────────────┬─────────────────────────────────┘
                       │
                       ▼
┌────────────────────────────────────────────────────────┐
│ 3. PEMANGGILAN ANTREAN                                 │
│    Route: /queues (role: admin|doctor|receptionist|    │
│            nurse)                                      │
│    Controller: QueueController                         │
│    Service: QueueService::callAndProgress()            │
│    Database: UPDATE queue + INSERT queue_calls         │
│    Event: QueueUpdated (action: called) → TTS + Banner │
├────────────────────────────────────────────────────────┤
│    Alur:                                               │
│      → Staff poli klik tombol "Panggil" (AJAX)         │
│      → System: callAndProgress()                       │
│        → Cek triage sudah ada (throw jika belum)       │
│        → INSERT queue_calls (call_sequence++)          │
│        → UPDATE queue status = in_progress             │
│        → UPDATE service_status = in_consultation       │
│        → Broadcast QueueUpdated (action: called)       │
│      → Display TV: banner + TTS otomatis               │
│        "Nomor antrean {number}, {nama},                │
│         silakan menuju {poli}"                         │
│      → Pasien masuk ke ruang poli                      │
└──────────────────────┬─────────────────────────────────┘
                       │
                       ▼
┌────────────────────────────────────────────────────────┐
│ 4. REKAM MEDIS ELEKTRONIK (RME) — SOAP                │
│    Route: /medical-records (role: admin|doctor)        │
│    Controller: MedicalRecordController                 │
│    Service: MedicalRecordService::createRecord()       │
│    Database: INSERT medical_records + pivots           │
│    Observer: MedicalRecordObserver → Satu Sehat sync   │
│    TTE: TteService::sign() + QR Code + verify publik   │
├────────────────────────────────────────────────────────┤
│    4a. Workspace (11 tab):                             │
│      Route: /medical-records/workspace/{queue}         │
│      Tab: SOAP | Triage | Lab | Resep | Tindakan |    │
│           Informed Consent | Edukasi | Resume |        │
│           Berkas | Riwayat | Audit Trail               │
│                                                        │
│    4b. SOAP Flow:                                      │
│      S → subjective_complaint, anamnesis, past_history │
│        medication_history                              │
│      O → objective_finding, physical_exam, vital_signs │
│        (pre-filled dari triage)                        │
│      A → assessment, diagnosis_primary (ICD-10),       │
│        diagnosis_secondary (ICD-10),                   │
│        diagnosis_differential (ICD-10)                 │
│      P → plan, procedure_ids (ICD-9-CM),               │
│        specialist_data (JSON per poli spesifik)        │
│      Form Spesialis: PDL, Anak, Saraf, Gigi, Radiologi │
│                                                        │
│    4c. Tindakan Medis:                                 │
│      Pivot: medical_record_procedures + icd9_cm        │
│      Status: ordered → in_progress → completed         │
│      Link: bisa dihubungkan ke Informed Consent        │
│                                                        │
│    4d. TTE (Tanda Tangan Elektronik):                  │
│      → Dokter klik "Tanda tangani"                     │
│      → Generate hash SHA-256 dari 23 field kritis      │
│        (termasuk pivot diagnosis + prosedur)           │
│      → Simpan signed_by, signed_at, signature_hash     │
│      → QR Code di PDF RME                              │
│      → Verifikasi publik: /verify/{hash}               │
│      → Integrity check: re-generate hash vs stored     │
│      → Proteksi: updateRecord() blokir jika signed     │
│                                                        │
│    4e. Diagnosis:                                      │
│      Primary (1) → type=primary                        │
│      Secondary (n) → type=secondary                    │
│      Banding (n) → type=differential                   │
│      Semua via pivot medical_record_diagnoses          │
│                                                        │
│    4f. Audit Trail:                                    │
│      Immutable (no updated_at)                         │
│      Log per-field: old_value, new_value, IP, UA       │
│      trackedFields: 18+ field termasuk TTE fields      │
│      Menu: /audit-trail (admin lihat semua, doctor     │
│            lihat polinya sendiri)                      │
└──────────────────────┬─────────────────────────────────┘
                       │
         ┌─────────────┼─────────────┬──────────────────┐
         ▼             ▼             ▼                  ▼
┌────────────────┐ ┌──────────┐ ┌──────────┐ ┌────────────────┐
│ 5. RESEP       │ │ 6. LAB   │ │ 7. IC    │ │ 8. EDUKASI     │
│                │ │          │ │          │ │                │
│ Controller:    │ │Control:  │ │Control:  │ │ Control:       │
│ Prescription-  │ │LabRequest│ │Informed- │ │ Patient-       │
│ Controller     │ │Controller│ │Consent-  │ │ Education-     │
│                │ │          │ │Controller│ │ Controller     │
│ Service:       │ │Service:  │ │Service:  │ │ Service:       │
│ MedicalRecord- │ │(direct   │ │Informed- │ │ EducationService│
│ Service::      │ │DB)       │ │Consent-  │ │                │
│ createPrescrip-│ │          │ │Service   │ │                │
│ tion()         │ │          │ │          │ │                │
│                │ │          │ │          │ │                │
│ Route:         │ │Route:    │ │Route:    │ │ Route:         │
│ /prescriptions │ │/lab-     │ │/informed-│ │ /medical-      │
│ (role:         │ │requests  │ │consents  │ │ records/{mr}/  │
│ admin|doctor|  │ │(role:    │ │(role:    │ │ education      │
│ pharmacist)    │ │admin|    │ │admin|    │ │ (role:         │
│                │ │doctor|   │ │doctor)   │ │ admin|doctor)  │
│                │ │laborant) │ │          │ │                │
│ Observer:      │ │          │ │Procedure │ │                │
│ Prescription-  │ │          │ │linked:   │ │                │
│ Observer →     │ │          │ │medical_  │ │                │
│ Sync Satu Sehat│ │          │ │record_   │ │                │
│ + Audit Log    │ │          │ │procedures│ │                │
│                │ │          │ │.informed_│ │                │
│                │ │          │ │consent_id│ │                │
│                │ │          │ │          │ │                │
│ Format nomor:  │ │Format:   │ │Flow:     │ │ CREATE: isi    │
│ RX-YYYYMMDD-   │ │LAB-      │ │ draft →  │ │ 5 field        │
│ XXXX           │ │YYYYMMDD- │ │ pasien   │ │ edukasi        │
│                │ │XXX       │ │ setuju → │ │                │
│                │ │          │ │ dokter   │ │ UPDATE:        │
│                │ │          │ │ tanda →  │ │ /edit          │
│                │ │          │ │ PDF QR   │ │                │
└───────┬────────┘ └────┬─────┘ └────┬─────┘ └────────┬───────┘
        │               │            │                │
        └───────┬───────┘            └───────┬────────┘
                │                            │
                ▼                            ▼
       ┌────────────────────────────────────────┐
       │ 9. RESUME KUNJUNGAN                    │
       │    Route: /registration/{reg}/summary   │
       │    Controller: VisitSummaryController    │
       │    Service: VisitSummaryService::create() │
       │    Database: INSERT visit_summaries      │
       │    → UPDATE service_status = completed   │
       │    → Trigger: Surat (DomPDF)             │
       ├─────────────────────────────────────────┤
       │    Alur:                                │
       │      → Diagnosis akhir                  │
       │      → Status pulang (sembuh/dirujuk/    │
       │        pulang_paksa/meninggal/lainnya)   │
       │      → Rencana kontrol                  │
       │      → Rujukan (jika ada)               │
       │      → Surat sakit (opsional)           │
       │      → System: INSERT visit_summaries   │
       │      → System: UPDATE status = completed │
       │      → Cetak surat (opsional)            │
       └────────────────────┬────────────────────┘
                            │
                            ▼
               ┌──────────────────────┐
               │ SELESAI (completed)  │
               └──────────────────────┘
```

---

## 3. Alur Informed Consent Digital

```
┌─────────────────────────────────────────────────────────┐
│ Inisiasi                                                │
│   → Dari RME tab Tindakan: klik "Buat Informed Consent" │
│   → Dari menu sidebar: Informed Consent → Baru          │
│   → Pre-filled: patient, medical_record, registration   │
│   → Pilih tindakan/prosedur (ICD-9-CM) via Select2     │
│   → Isi: diagnosis, purpose, risks, benefits,           │
│     alternatives                                        │
│   → System: INSERT informed_consents (status=draft)     │
│   → System: UPDATE procedures.informed_consent_id       │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│ Pasien Setuju                                           │
│   → Route: /informed-consents/{id}/sign-patient         │
│   → Input: nama pasien                                  │
│   → System: UPDATE patient_name, patient_agreed=true,   │
│     patient_signed_at=now                               │
│   → System: Generate patient_signature_hash (snapshot)  │
│   → Proteksi: cek cancelled, cek sudah sign             │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│ Dokter Tanda Tangan                                     │
│   → Route: /informed-consents/{id}/sign-doctor          │
│   → System: Cek pasien sudah tanda tangan dulu          │
│     (throw jika belum)                                  │
│   → System: Generate SHA-256 hash (14+ field)           │
│   → System: UPDATE signed_by, signed_at,                │
│     signature_hash, status=signed                       │
│   → System: UPDATE procedures.informed_consent=true     │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│ PDF + QR Code                                           │
│   → Route: /informed-consents/{id}/pdf                  │
│   → DomPDF: layout 3 kolom tanda tangan                 │
│   → QR Code: berisi link verifikasi                     │
│   → Hash + metadata ditampilkan di footer               │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│ Verifikasi Publik                                       │
│   → Route: /informed-consents/verify/{hash} (no auth)   │
│   → Cari by signature_hash                              │
│   → Tampilkan status sah/tidak                          │
│   → Integrity check (re-generate hash)                  │
└─────────────────────────────────────────────────────────┘
```

---

## 4. Alur Integrasi Satu Sehat (Auto-Sync)

Observer memicu sinkronasi otomatis saat data berubah:

```
┌───────────────────────────────────────────────────────────────┐
│ PATIENT OBSERVER                                              │
│   Trigger: Patient::created(), updated(), deleted()           │
│   Service: PatientService::syncPatient()                      │
│   Flow: create → search NIK → if found, skip (hindari 403)   │
│         → else POST /Patient → simpan IHS ID                 │
│   KD Codes: province_kd, city_kd, district_kd, village_kd    │
│             dari lookup map (12 provinsi + 10 kota)           │
│   Extensions: administrativeCode KEMENDAGRI                   │
└───────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────┐
│ MEDICAL RECORD OBSERVER                                       │
│   Trigger: MedicalRecord::created(), updated(), deleted()     │
│   Services: EncounterService, ConditionService,               │
│             ObservationService                                │
│                                                                   │
│   created():                                                   │
│     → syncEncounter() — POST /Encounter (identifier,          │
│       participant, statusHistory, location)                   │
│     → syncCondition() — POST /Condition                        │
│     → syncObservation() — POST /Observation (vital signs)      │
│                                                                   │
│   updated():                                                   │
│     → Jika wasChanged(['diagnosis_primary',                    │
│       'diagnosis_secondary', 'vital_signs']):                  │
│       → syncEncounter()                                        │
│       → syncCondition()                                        │
│       → syncObservation()                                      │
│                                                                   │
│   Audit log: immutable, per-field tracking                     │
└───────────────────────────────────────────────────────────────┘

┌───────────────────────────────────────────────────────────────┐
│ PRESCRIPTION OBSERVER                                         │
│   Trigger: Prescription::created()                            │
│   Service: MedicationRequestService::syncMedicationRequest()  │
│   Audit: log ke medical_record_audits                         │
│   (Tergantung KFA code mapping — masih blocked di staging)    │
└───────────────────────────────────────────────────────────────┘
```

### Satu Sehat Sync CLI

```
php artisan satusehat:sync {type?} {--force}

  type: patients | practitioners | locations | encounters |
        conditions | observations | all
  --force: re-sync meskipun sudah pernah
```

Dependency chain: `patient → practitioner → location → encounter → condition → observation`

---

## 5. Event & Real-Time

```
┌───────────────────────────────────────────────────────────────┐
│ QueueUpdated (event)                                          │
│   Channel: queue (public)                                     │
│   ShouldBroadcastNow → synchronous                            │
│   Properties:                                                 │
│     - queueId, polyclinicId, status, action                   │
│     - queueNumber, patientName, polyclinicName                │
│     - polyclinicQueueCount, actionPayload                     │
│                                                                   │
│   Triggered by:                                               │
│     registerQueue()   → action: created     → Display TV      │
│     callAjax()        → action: called      → TTS + banner    │
│     inProgress()      → action: in_progress → refresh tabel    │
│     complete()        → action: completed   → refresh tabel    │
│     cancel()          → action: cancelled   → refresh tabel    │
│                                                                   │
│   Consumers:                                                  │
│     - resources/js/app.js (Echo listener → toast + custom     │
│       event 'queue-updated')                                  │
│     - queues/index.blade.js (listener → auto-refresh tabel +  │
│       TTS otomatis)                                           │
│     - queues/display-tv.blade.php (Echo → filter by           │
│       polyclinicId → banner + TTS + re-render)                │
│     - queues/display.blade.php (polling 5s fallback)          │
│                                                                   │
│   TTS (Text-to-Speech):                                       │
│     - Browser SpeechSynthesis API (id-ID)                     │
│     - Auto-play saat action=called                            │
│     - Tombol "Dengarkan" untuk replay                         │
│     - Antrian 3 Promise untuk mencegah tabrakan suara         │
└───────────────────────────────────────────────────────────────┘
```

---

## 6. Route Tree per Role

```
PUBLIC (tanpa auth)
├── /queues/display                    → Display landing
├── /queues/display/tv/{polyclinic}    → Display TV per poli
├── /queues/display-json               → JSON display data
├── /medical-records/verify/{hash}     → TTE verifikasi publik
└── /informed-consents/verify/{hash}   → IC verifikasi publik

admin
├── /registration, /patients, /queues, /medical-records
├── /audit-trail, /informed-consents
├── /medicines, /prescriptions, /inventories
├── /bpjs-seps, /bpjs/vclaim, /bpjs/antrol
├── /satusehat (dashboard, sync, KFA mapping)
├── /polyclinics, /doctors (master)
├── /lab-*, /triage, /letters, /attachments
├── /diagnoses (ICD daftar)
├── /education, /visit-summary
├── /users (via Breeze default)
└── /dashboard

receptionist
├── /registration, /patients
├── /queues (index, call - terbatas)
└── /triage (create)

doctor
├── /queues (index — hanya polinya sendiri)
├── /medical-records (create/edit/workspace/sign/pdf)
├── /audit-trail (— hanya RME polinya sendiri)
├── /informed-consents
├── /prescriptions (create)
├── /lab-requests (create, lihat)
├── /triage (lihat, edit)
├── /education, /visit-summary, /letters
├── /satusehat (lihat)
├── /diagnoses
└── /attachments

nurse
├── /queues (index, call)
├── /triage (create, edit)
└── /medical-records (show only — via workspace)

pharmacist
├── /medicines, /prescriptions, /inventories
└── /bpjs/vclaim

cashier
├── /bpjs/vclaim
└── /bpjs/antrol

laborant
├── /lab-test-categories, /lab-tests
├── /lab-requests, /lab-results
```

---

## 7. Relasi Antar Model (Inti)

```
Patient
  └── Registration (1:N) — satu pasien banyak kunjungan
        ├── Triage (1:1) — per kunjungan
        ├── Queue (1:1) — antrean calling
        │     └── QueueCall (1:N) — riwayat panggilan
        └── MedicalRecord (1:N) — RME per kunjungan
              ├── MedicalRecordDiagnosis (pivot) — ICD-10
              ├── MedicalRecordProcedure (pivot) — ICD-9-CM + soft deletes
              ├── Prescription (1:N) — resep
              │     └── PrescriptionItem (1:N) — item obat
              ├── LabRequest (1:N)
              │     └── LabRequestItem (1:N) → LabResult
              ├── PatientEducation (1:1)
              ├── VisitSummary (1:N, via Registration)
              ├── InformedConsent (1:N)
              ├── Attachment (1:N) — upload berkas
              └── MedicalRecordAudit (1:N) — immutable log
```

---

## 8. Known Issues & Catatan

| Issue | Dampak | Status |
|-------|--------|--------|
| `service_status` ENUM `education` hilang di migration 083008 | Error runtime saat EducationService::create() | ✅ **FIXED** — ditambah kembali |
| `callBack()` method tidak dipanggil dari route/view manapun | Dead code ~20 baris | ⏳ Bisa dihapus atau diintegrasikan |
| Practitioner sync 403 di staging | Encounter gagal karena participant mandatory | ⏳ Menunggu KFA/Kemenkes |
| KFA CodeSystem 0 entries di staging | Tidak bisa mapping obat via API | ⏳ Production saja |
| SQlite test gagal (index + factory kolom lama) | `phpunit` tidak jalan | ⏳ Perlu perbaikan test |
| `resume` status dihapus dari ENUM (tidak dipakai kode) | Tidak ada dampak | ✅ Aman |

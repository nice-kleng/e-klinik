<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PatientController;
use App\Http\Controllers\Web\QueueController;
use App\Http\Controllers\Web\MedicalRecordController;
use App\Http\Controllers\Web\DiagnosisController;
use App\Http\Controllers\Web\MedicineController;
use App\Http\Controllers\Web\PrescriptionController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\PolyclinicController;
use App\Http\Controllers\Web\BpjsSepController;
use App\Http\Controllers\Web\LabTestCategoryController;
use App\Http\Controllers\Web\LabTestController;
use App\Http\Controllers\Web\LabRequestController;
use App\Http\Controllers\Web\LabResultController;
use App\Http\Controllers\Web\RegistrationController;
use App\Http\Controllers\Web\DoctorController;
use App\Http\Controllers\Web\TriageController;
use App\Http\Controllers\Web\PatientEducationController;
use App\Http\Controllers\Web\VisitSummaryController;
use App\Http\Controllers\Web\LetterController;
use App\Http\Controllers\Web\AuditTrailController;
use App\Http\Controllers\Web\AttachmentController;
use App\Http\Controllers\Web\InformedConsentController;
use App\Http\Controllers\Web\KasirController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Registration (Pendaftaran) — admin, receptionist
    Route::prefix('registration')->name('registration.')->middleware('role:admin|receptionist')->group(function () {
        Route::get('/', [RegistrationController::class, 'index'])->name('index');
        Route::get('/search', [RegistrationController::class, 'searchPatient'])->name('search');
        Route::post('/', [RegistrationController::class, 'store'])->name('store');
        Route::post('/checkin/{bpjsAntrean}', [RegistrationController::class, 'checkin'])->name('checkin');
        Route::get('/ticket/{queue}', [RegistrationController::class, 'printTicket'])->name('ticket');
    });

    // Patients — admin, doctor, receptionist
    Route::resource('patients', PatientController::class)
        ->except(['destroy'])
        ->middleware('role:admin|doctor|receptionist');
    Route::get('patients/{patient}/print-card', [PatientController::class, 'printCard'])
        ->name('patients.print-card')
        ->middleware('role:admin|receptionist');
    Route::delete('patients/{patient}', [PatientController::class, 'destroy'])
        ->name('patients.destroy')
        ->middleware('role:admin');

    // Queues — admin, doctor, receptionist, nurse
    Route::prefix('queues')->name('queues.')->middleware('role:admin|doctor|receptionist|nurse')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/create', [QueueController::class, 'create'])->name('create');
        Route::post('/', [QueueController::class, 'store'])->name('store');
        Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
        Route::post('/{queue}/call', [QueueController::class, 'call'])->name('call');
        Route::post('/{queue}/in-progress', [QueueController::class, 'inProgress'])->name('in-progress');
        Route::post('/{queue}/complete', [QueueController::class, 'complete'])->name('complete');
        Route::post('/{queue}/cancel', [QueueController::class, 'cancel'])->name('cancel');
        Route::post('/{queue}/call-ajax', [QueueController::class, 'callAjax'])->name('call-ajax');
        Route::get('/{queue}/data', [QueueController::class, 'queueData'])->name('data');
        Route::get('/history/{registration}', [QueueController::class, 'history'])->name('history');
    });

    Route::prefix('queues')->name('queues.')->group(function () {
        Route::get('/display/tv', [QueueController::class, 'display'])->name('display');
        Route::get('/display/tv/{polyclinic}', [QueueController::class, 'displayTv'])->name('display-tv');
        Route::get('/display-json', [QueueController::class, 'displayJson'])->name('display-json');
        Route::get('/display-json/{polyclinic}', [QueueController::class, 'displayJsonPoly'])->name('display-json-poly');
    });

    // Medical Records — admin, doctor
    Route::get('medical-records/icd10-search', [MedicalRecordController::class, 'icd10Search'])
        ->name('medical-records.icd10-search')
        ->middleware('role:admin|doctor');
    Route::get('medical-records/icd9-search', [MedicalRecordController::class, 'icd9Search'])
        ->name('medical-records.icd9-search')
        ->middleware('role:admin|doctor');
    Route::get('medical-records/workspace/{queue}', [MedicalRecordController::class, 'workspace'])
        ->name('medical-records.workspace')
        ->middleware('role:admin|doctor');
    Route::get('medical-records/workspace/{queue}/prescriptions', [MedicalRecordController::class, 'workspacePrescriptions'])
        ->name('workspace.prescriptions')
        ->middleware('role:admin|doctor');
    Route::post('medical-records/{queue}/service-status', [MedicalRecordController::class, 'updateServiceStatus'])
        ->name('medical-records.service-status')
        ->middleware('role:admin|doctor');
    Route::post('medical-records/{medicalRecord}/sign', [MedicalRecordController::class, 'sign'])
        ->name('medical-records.sign')
        ->middleware('role:admin|doctor');
    Route::get('medical-records/{medicalRecord}/pdf', [MedicalRecordController::class, 'downloadPdf'])
        ->name('medical-records.pdf')
        ->middleware('role:admin|doctor');
    Route::resource('medical-records', MedicalRecordController::class)
        ->except(['destroy'])
        ->middleware('role:admin|doctor');
    Route::delete('medical-records/{medical_record}', [MedicalRecordController::class, 'destroy'])
        ->name('medical-records.destroy')
        ->middleware('role:admin');

    // TTE verification — public (no auth, accessible via QR scan)
    Route::get('medical-records/verify/{hash}', [MedicalRecordController::class, 'verifyPdf'])
        ->name('medical-records.verify');

    // Audit Trail — admin, doctor
    Route::get('audit-trail', [AuditTrailController::class, 'index'])
        ->name('audit-trail.index')
        ->middleware('role:admin|doctor');

    // Informed Consent — admin, doctor (verify route before resource to avoid capture)
    Route::get('informed-consents/verify/{hash}', [InformedConsentController::class, 'verifyPdf'])
        ->name('informed-consents.verify');
    Route::get('informed-consents/procedures', [InformedConsentController::class, 'proceduresByMedicalRecord'])
        ->name('informed-consents.procedures')
        ->middleware('role:admin|doctor');
    Route::post('informed-consents/{informedConsent}/sign-patient', [InformedConsentController::class, 'signPatient'])
        ->name('informed-consents.sign-patient')
        ->middleware('role:admin|doctor');
    Route::post('informed-consents/{informedConsent}/sign-doctor', [InformedConsentController::class, 'signDoctor'])
        ->name('informed-consents.sign-doctor')
        ->middleware('role:admin|doctor');
    Route::get('informed-consents/{informedConsent}/pdf', [InformedConsentController::class, 'downloadPdf'])
        ->name('informed-consents.pdf')
        ->middleware('role:admin|doctor');
    Route::resource('informed-consents', InformedConsentController::class)
        ->except(['destroy'])
        ->middleware('role:admin|doctor');
    Route::delete('informed-consents/{informed_consent}', [InformedConsentController::class, 'destroy'])
        ->name('informed-consents.destroy')
        ->middleware('role:admin|doctor');

    // Diagnoses (ICD-10) — admin, doctor
    Route::get('diagnoses', [DiagnosisController::class, 'index'])
        ->name('diagnoses.index')
        ->middleware('role:admin|doctor');

    // Medicines — admin, pharmacist
    Route::resource('medicines', MedicineController::class)
        ->except(['destroy'])
        ->middleware('role:admin|pharmacist');

    // Prescriptions — admin, doctor, pharmacist
    Route::prefix('prescriptions')->name('prescriptions.')->middleware('role:admin|doctor|pharmacist')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/pending', [PrescriptionController::class, 'pending'])->name('pending');
        Route::get('/create', [PrescriptionController::class, 'create'])->name('create');
        Route::post('/', [PrescriptionController::class, 'store'])->name('store');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('edit');
        Route::put('/{prescription}', [PrescriptionController::class, 'update'])->name('update');
        Route::post('/{prescription}/dispense', [PrescriptionController::class, 'dispense'])->name('dispense');
        Route::post('/{prescription}/cancel', [PrescriptionController::class, 'cancel'])->name('cancel');
        Route::get('/print/{prescription}', [PrescriptionController::class, 'print'])->name('print');
        Route::get('/etiket/{prescription}', [PrescriptionController::class, 'etiket'])->name('etiket');
    });
    Route::get('prescriptions/last/{patient}', [PrescriptionController::class, 'lastByPatient'])
        ->name('prescriptions.last')
        ->middleware('role:admin|doctor|pharmacist');

    // Inventory — admin, pharmacist
    Route::prefix('inventories')->name('inventories.')->middleware('role:admin|pharmacist')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::match(['get', 'post'], '/opname', [InventoryController::class, 'opname'])->name('opname');
        Route::get('/reports/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/reports/expiring', [InventoryController::class, 'expiring'])->name('expiring');
        Route::get('/reports/expired', [InventoryController::class, 'expired'])->name('expired');
        Route::get('/{inventory}/edit', [InventoryController::class, 'edit'])->name('edit');
        Route::put('/{inventory}', [InventoryController::class, 'update'])->name('update');
        Route::delete('/{inventory}', [InventoryController::class, 'destroy'])->name('destroy');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
    });

    // Kasir — admin, cashier
    Route::prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [KasirController::class, 'index'])->name('index');
        Route::get('/create', [KasirController::class, 'create'])->name('create');
        Route::post('/', [KasirController::class, 'store'])->name('store');
        Route::get('/{invoice}', [KasirController::class, 'show'])->name('show');
        Route::post('/{invoice}/pay', [KasirController::class, 'pay'])->name('pay');
        Route::delete('/{invoice}', [KasirController::class, 'destroy'])->name('destroy');
        Route::get('/{invoice}/print', [KasirController::class, 'print'])->name('print');
    })->middleware('role:admin|cashier');

    // BPJS SEP — admin only
    Route::prefix('bpjs-seps')->name('bpjs-seps.')->middleware('role:admin')->group(function () {
        Route::get('/', [BpjsSepController::class, 'index'])->name('index');
        Route::get('/create', [BpjsSepController::class, 'create'])->name('create');
        Route::post('/', [BpjsSepController::class, 'store'])->name('store');
        Route::get('/{bpjsSep}', [BpjsSepController::class, 'show'])->name('show');
        Route::delete('/{bpjsSep}', [BpjsSepController::class, 'destroy'])->name('destroy');
    });

    // Polyclinics & Doctors — admin only
    Route::resource('polyclinics', PolyclinicController::class)
        ->except(['destroy'])
        ->middleware('role:admin');
    Route::resource('doctors', DoctorController::class)
        ->except(['destroy'])
        ->middleware('role:admin');

    // ─── Laboratorium ──────────────────────────────────

    // Lab Test Categories — admin, laborant
    Route::resource('lab-test-categories', LabTestCategoryController::class)
        ->except(['show'])
        ->middleware('role:admin|laborant');

    // Lab Tests — admin, laborant
    Route::resource('lab-tests', LabTestController::class)
        ->middleware('role:admin|laborant');

    // Lab Requests — admin, doctor, laborant
    Route::prefix('lab-requests')->name('lab-requests.')->middleware('role:admin|doctor|laborant')->group(function () {
        Route::get('/', [LabRequestController::class, 'index'])->name('index');
        Route::get('/create', [LabRequestController::class, 'create'])->name('create');
        Route::post('/', [LabRequestController::class, 'store'])->name('store');
        Route::get('/{labRequest}', [LabRequestController::class, 'show'])->name('show');
        Route::patch('/{labRequest}/status', [LabRequestController::class, 'updateStatus'])->name('update-status');
    });

    // Lab Results — admin, laborant, doctor
    Route::prefix('lab-results')->name('lab-results.')->middleware('role:admin|laborant|doctor')->group(function () {
        Route::get('/', [LabResultController::class, 'index'])->name('index');
        Route::get('/input/{labRequest}', [LabResultController::class, 'input'])->name('input');
        Route::post('/', [LabResultController::class, 'store'])->name('store');
        Route::get('/{labResult}/edit', [LabResultController::class, 'edit'])->name('edit');
        Route::put('/{labResult}', [LabResultController::class, 'update'])->name('update');
    });

    // ─── Triage (Asesmen Perawat) ──────────────────────────
    Route::prefix('triage')->name('triage.')->middleware('role:admin|receptionist|nurse')->group(function () {
        Route::get('/create/{registration}', [TriageController::class, 'create'])->name('create');
        Route::post('/', [TriageController::class, 'store'])->name('store');
        Route::get('/{triage}', [TriageController::class, 'show'])->name('show');
        Route::get('/{triage}/edit', [TriageController::class, 'edit'])->name('edit');
        Route::put('/{triage}', [TriageController::class, 'update'])->name('update');
    });

    // ─── Edukasi Pasien ───────────────────────────────────
    Route::prefix('medical-records/{medicalRecord}/education')->name('education.')->middleware('role:admin|doctor')->group(function () {
        Route::get('/create', [PatientEducationController::class, 'create'])->name('create');
        Route::post('/', [PatientEducationController::class, 'store'])->name('store');
        Route::get('/edit', [PatientEducationController::class, 'edit'])->name('edit');
        Route::put('/', [PatientEducationController::class, 'update'])->name('update');
    });

    // ─── Resume Kunjungan ─────────────────────────────────
    Route::prefix('registration/{registration}/summary')->name('visit-summary.')->middleware('role:admin|doctor')->group(function () {
        Route::get('/create', [VisitSummaryController::class, 'create'])->name('create');
        Route::post('/', [VisitSummaryController::class, 'store'])->name('store');
    });
    Route::middleware('role:admin|doctor')->group(function () {
        Route::get('/summary/{visitSummary}/edit', [VisitSummaryController::class, 'edit'])->name('visit-summary.edit');
        Route::put('/summary/{visitSummary}', [VisitSummaryController::class, 'update'])->name('visit-summary.update');
    });
    Route::get('/summary/{visitSummary}', [VisitSummaryController::class, 'show'])
        ->name('visit-summary.show')
        ->middleware('role:admin|doctor');

    // ─── Surat-surat ──────────────────────────────────────
    Route::prefix('letters')->name('letters.')->middleware('role:admin|doctor')->group(function () {
        Route::get('/sick-leave/{registration}', [LetterController::class, 'sickLeave'])->name('sick-leave');
        Route::get('/health-certificate/{registration}', [LetterController::class, 'healthCertificate'])->name('health-certificate');
        Route::get('/referral/{registration}', [LetterController::class, 'referral'])->name('referral');
        Route::get('/medical-certificate/{registration}', [LetterController::class, 'medicalCertificate'])->name('medical-certificate');
    });

    // ─── Berkas / Attachment ────────────────────────────
    Route::post('medical-records/{medical_record}/attachments', [AttachmentController::class, 'store'])
        ->name('attachments.store')
        ->middleware('role:admin|doctor');
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download'])
        ->name('attachments.download')
        ->middleware('auth');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])
        ->name('attachments.destroy')
        ->middleware('role:admin|doctor');

    // ─── BPJS JSON Endpoints (internal, via session auth) ──────
    Route::prefix('bpjs')->name('bpjs.')->group(function () {
        Route::prefix('vclaim')->name('vclaim.')->middleware('role:admin|doctor|cashier')->group(function () {
            Route::get('peserta', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'peserta'])->name('peserta');
            Route::post('sep', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'sepStore'])->name('sep.store');
            Route::get('sep/{noSep}', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'sepShow'])->name('sep.show');
            Route::put('sep', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'sepUpdate'])->name('sep.update');
            Route::delete('sep', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'sepDelete'])->name('sep.delete');
            Route::post('claim', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'claimStore'])->name('claim.store');
            Route::get('claim/{noSep}/status', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'claimStatus'])->name('claim.status');
            Route::get('referensi/diagnosa', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'referensiDiagnosa'])->name('referensi.diagnosa');
            Route::get('referensi/poli', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'referensiPoli'])->name('referensi.poli');
            Route::get('referensi/faskes', [\App\Http\Controllers\Web\BPJS\VClaimController::class, 'referensiFaskes'])->name('referensi.faskes');
        });

        Route::prefix('antrol')->name('antrol.')->middleware('role:admin|cashier')->group(function () {
            Route::post('antrean', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'addAntrean'])->name('antrean.store');
            Route::put('antrean', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'updateAntrean'])->name('antrean.update');
            Route::delete('antrean', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'deleteAntrean'])->name('antrean.destroy');
            Route::get('antrean/{kodePoli}/{tanggal}', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'getAntreanPoli'])->name('antrean.poli');
            Route::get('dashboard/tanggal/{tanggal}', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'dashboardTanggal'])->name('dashboard.tanggal');
            Route::get('dashboard/bulan/{bulan}/{tahun}', [\App\Http\Controllers\Web\BPJS\AntrolController::class, 'dashboardBulan'])->name('dashboard.bulan');
        });
    });

    // ─── Satu Sehat JSON Endpoints (internal, via session auth) ──
    Route::prefix('satusehat')->name('satusehat.')->middleware('role:admin|doctor')->group(function () {
        Route::post('sync/patient/{patient}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncPatient'])->name('sync.patient');
        Route::post('sync/encounter/{medical_record}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncEncounter'])->name('sync.encounter');
        Route::post('sync/condition/{medical_record}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncCondition'])->name('sync.condition');
        Route::post('sync/observation/{medical_record}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncObservation'])->name('sync.observation');
        Route::post('sync/medication-request/{prescription}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncMedicationRequest'])->name('sync.medication-request');
        Route::post('sync/all/{medical_record}', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'syncAll'])->name('sync.all');
        Route::get('search/patient', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'searchPatient'])->name('search.patient');
        Route::get('search/icd10', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'searchIcd10'])->name('search.icd10');
        Route::get('search/loinc', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'searchLoinc'])->name('search.loinc');
        Route::get('status', [\App\Http\Controllers\Web\SatuSehat\FHIRController::class, 'status'])->name('status');
    });

    // ─── Settings (Admin only) ───
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\SettingsController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Web\SettingsController::class, 'update'])->name('update');
    });
});

require __DIR__.'/auth.php';

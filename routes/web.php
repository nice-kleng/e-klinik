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

    // Queues — admin, doctor, receptionist
    Route::prefix('queues')->name('queues.')->middleware('role:admin|doctor|receptionist')->group(function () {
        Route::get('/', [QueueController::class, 'index'])->name('index');
        Route::get('/create', [QueueController::class, 'create'])->name('create');
        Route::post('/', [QueueController::class, 'store'])->name('store');
        Route::get('/{queue}', [QueueController::class, 'show'])->name('show');
        Route::post('/{queue}/call', [QueueController::class, 'call'])->name('call');
        Route::post('/{queue}/in-progress', [QueueController::class, 'inProgress'])->name('in-progress');
        Route::post('/{queue}/complete', [QueueController::class, 'complete'])->name('complete');
        Route::post('/{queue}/cancel', [QueueController::class, 'cancel'])->name('cancel');
        Route::get('/display/tv', [QueueController::class, 'display'])->name('display');
        Route::get('/history/{registration}', [QueueController::class, 'history'])->name('history');
    });

    // Medical Records — admin, doctor
    Route::resource('medical-records', MedicalRecordController::class)
        ->except(['destroy'])
        ->middleware('role:admin|doctor');
    Route::delete('medical-records/{medical_record}', [MedicalRecordController::class, 'destroy'])
        ->name('medical-records.destroy')
        ->middleware('role:admin');

    // Diagnoses (ICD-10) — admin, doctor
    Route::get('diagnoses', [DiagnosisController::class, 'index'])
        ->name('diagnoses.index')
        ->middleware('role:admin|doctor');

    // Medicines — admin, pharmacist
    Route::resource('medicines', MedicineController::class)
        ->except(['destroy'])
        ->middleware('role:admin|pharmacist');

    // Prescriptions — admin, doctor, pharmacist
    Route::resource('prescriptions', PrescriptionController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->middleware('role:admin|doctor|pharmacist');
    Route::get('prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])
        ->name('prescriptions.print')
        ->middleware('role:admin|pharmacist');

    // Inventory — admin, pharmacist
    Route::prefix('inventories')->name('inventories.')->middleware('role:admin|pharmacist')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/create', [InventoryController::class, 'create'])->name('create');
        Route::post('/', [InventoryController::class, 'store'])->name('store');
        Route::get('/{inventory}', [InventoryController::class, 'show'])->name('show');
        Route::get('/reports/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/reports/expiring', [InventoryController::class, 'expiring'])->name('expiring');
        Route::get('/reports/expired', [InventoryController::class, 'expired'])->name('expired');
    });

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
});

require __DIR__.'/auth.php';

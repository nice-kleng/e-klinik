<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BPJS\VClaimController;
use App\Http\Controllers\Api\BPJS\AntrolController;
use App\Http\Controllers\Api\SatuSehat\FHIRController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('bpjs')->group(function () {
        Route::prefix('vclaim')->group(function () {
            Route::get('peserta', [VClaimController::class, 'peserta']);
            Route::post('sep', [VClaimController::class, 'sepStore']);
            Route::get('sep/{noSep}', [VClaimController::class, 'sepShow']);
            Route::put('sep', [VClaimController::class, 'sepUpdate']);
            Route::delete('sep', [VClaimController::class, 'sepDelete']);
            Route::post('claim', [VClaimController::class, 'claimStore']);
            Route::get('claim/{noSep}/status', [VClaimController::class, 'claimStatus']);
            Route::get('referensi/diagnosa', [VClaimController::class, 'referensiDiagnosa']);
            Route::get('referensi/poli', [VClaimController::class, 'referensiPoli']);
            Route::get('referensi/faskes', [VClaimController::class, 'referensiFaskes']);
        });

        Route::prefix('antrol')->group(function () {
            Route::post('antrean', [AntrolController::class, 'addAntrean']);
            Route::put('antrean', [AntrolController::class, 'updateAntrean']);
            Route::delete('antrean', [AntrolController::class, 'deleteAntrean']);
            Route::get('antrean/{kodePoli}/{tanggal}', [AntrolController::class, 'getAntreanPoli']);
            Route::get('dashboard/tanggal/{tanggal}', [AntrolController::class, 'dashboardTanggal']);
            Route::get('dashboard/bulan/{bulan}/{tahun}', [AntrolController::class, 'dashboardBulan']);
        });
    });

    Route::prefix('satusehat')->group(function () {
        Route::post('sync/patient/{patient}', [FHIRController::class, 'syncPatient']);
        Route::post('sync/encounter/{medical_record}', [FHIRController::class, 'syncEncounter']);
        Route::post('sync/condition/{medical_record}', [FHIRController::class, 'syncCondition']);
        Route::post('sync/observation/{medical_record}', [FHIRController::class, 'syncObservation']);
        Route::post('sync/medication-request/{prescription}', [FHIRController::class, 'syncMedicationRequest']);
        Route::post('sync/all/{medical_record}', [FHIRController::class, 'syncAll']);
        Route::get('search/patient', [FHIRController::class, 'searchPatient']);
        Route::get('search/icd10', [FHIRController::class, 'searchIcd10']);
        Route::get('search/loinc', [FHIRController::class, 'searchLoinc']);
        Route::get('status', [FHIRController::class, 'status']);
    });
});

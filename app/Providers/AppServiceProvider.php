<?php

namespace App\Providers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Observers\MedicalRecordObserver;
use App\Observers\PatientObserver;
use App\Observers\PrescriptionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(IntegrationServiceProvider::class);
        $this->app->register(ConfigServiceProvider::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Patient::observe(PatientObserver::class);
        MedicalRecord::observe(MedicalRecordObserver::class);
        Prescription::observe(PrescriptionObserver::class);
    }
}

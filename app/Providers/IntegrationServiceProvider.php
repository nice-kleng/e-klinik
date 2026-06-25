<?php

namespace App\Providers;

use App\Services\BrowserTtsProvider;
use App\Services\GoogleCloudTtsProvider;
use App\Services\InformedConsentService;
use App\Services\TtsProvider;
use App\Services\BPJS\AntrolService;
use App\Services\BPJS\BPJSHttpClient;
use App\Services\BPJS\VClaimService;
use App\Services\SatuSehat\AuthService;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\MedicationRequestService;
use App\Services\SatuSehat\ObservationService;
use App\Services\SatuSehat\OrganizationService;
use App\Services\SatuSehat\PatientService;
use App\Services\SatuSehat\PractitionerService;
use App\Services\SatuSehat\SatuSehatClient;
use App\Services\SatuSehat\TerminologyService;
use App\Services\InventoryService;
use App\Services\PricingService;
use Illuminate\Support\ServiceProvider;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/bpjs.php', 'bpjs');
        $this->mergeConfigFrom(__DIR__ . '/../../config/satusehat.php', 'satusehat');

        $this->app->bind(TtsProvider::class, function ($app) {
            if (config('services.google_tts.api_key')) {
                return $app->make(GoogleCloudTtsProvider::class);
            }
            return $app->make(BrowserTtsProvider::class);
        });

        // BPJS Services
        $this->app->singleton(BPJSHttpClient::class, function ($app) {
            return new BPJSHttpClient();
        });

        $this->app->singleton(VClaimService::class, function ($app) {
            return new VClaimService($app->make(BPJSHttpClient::class));
        });

        $this->app->singleton(AntrolService::class, function ($app) {
            return new AntrolService($app->make(BPJSHttpClient::class));
        });

        // Satu Sehat Services
        $this->app->singleton(SatuSehatClient::class, function ($app) {
            return new SatuSehatClient();
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService();
        });

        $this->app->singleton(PatientService::class, function ($app) {
            return new PatientService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(EncounterService::class, function ($app) {
            return new EncounterService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(ConditionService::class, function ($app) {
            return new ConditionService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(ObservationService::class, function ($app) {
            return new ObservationService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(MedicationRequestService::class, function ($app) {
            return new MedicationRequestService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(OrganizationService::class, function ($app) {
            return new OrganizationService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(PractitionerService::class, function ($app) {
            return new PractitionerService($app->make(SatuSehatClient::class));
        });

        $this->app->singleton(TerminologyService::class, function ($app) {
            return new TerminologyService($app->make(SatuSehatClient::class));
        });

        // Informed Consent Service
        $this->app->singleton(InformedConsentService::class, function ($app) {
            return new InformedConsentService($app->make(\App\Services\TteService::class));
        });

        // Pharmacy Services
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(PricingService::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/bpjs.php' => config_path('bpjs.php'),
            ], 'bpjs-config');

            $this->publishes([
                __DIR__ . '/../../config/satusehat.php' => config_path('satusehat.php'),
            ], 'satusehat-config');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Services\SatuSehat\ConditionService;
use App\Services\SatuSehat\EncounterService;
use App\Services\SatuSehat\LocationService;
use App\Services\SatuSehat\ObservationService;
use App\Services\SatuSehat\PatientService;
use App\Services\SatuSehat\PractitionerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncSatusehat extends Command
{
    protected $signature = 'satusehat:sync {type? : Type of data to sync (patients, practitioners, locations, encounters, conditions, all)} {--force : Force sync even if already synced}';

    protected $description = 'Sync local data to Satu Sehat (Kemenkes FHIR)';

    protected array $results = [
        'patients' => ['synced' => 0, 'failed' => 0, 'errors' => []],
        'practitioners' => ['synced' => 0, 'failed' => 0, 'errors' => []],
        'locations' => ['synced' => 0, 'failed' => 0, 'errors' => []],
        'encounters' => ['synced' => 0, 'failed' => 0, 'errors' => []],
        'conditions' => ['synced' => 0, 'failed' => 0, 'errors' => []],
    ];

    public function handle(): int
    {
        $type = $this->argument('type') ?? 'all';
        $force = $this->option('force');

        $this->info('Starting Satu Sehat sync...');
        $this->newLine();

        match ($type) {
            'patients' => $this->syncPatients($force),
            'practitioners' => $this->syncPractitioners($force),
            'locations' => $this->syncLocations($force),
            'encounters' => $this->syncEncounters($force),
            'conditions' => $this->syncConditions($force),
            default => $this->syncAll($force),
        };

        $this->newLine();
        $this->displayResults();

        $hasFailures = collect($this->results)->sum('failed') > 0;

        if ($hasFailures) {
            $this->warn('Sync completed with errors. Check logs for details.');
        }

        return $hasFailures ? Command::FAILURE : Command::SUCCESS;
    }

    protected function syncAll(bool $force): void
    {
        $this->syncPatients($force);
        $this->syncLocations($force);
        $this->syncPractitioners($force);
        $this->syncEncounters($force);
        $this->syncConditions($force);
    }

    protected function syncPatients(bool $force): void
    {
        $this->info('Syncing patients...');

        $query = Patient::query();

        if (!$force) {
            $syncedIds = \App\Models\SatusehatResource::where('model_type', Patient::class)
                ->where('resource_type', 'Patient')
                ->where('status', 'synced')
                ->pluck('model_id');

            $query->whereNotIn('id', $syncedIds);
        }

        $patients = $query->get();
        $total = $patients->count();

        if ($total === 0) {
            $this->warn('No patients to sync.');

            return;
        }

        $this->output->progressStart($total);
        $service = app(PatientService::class);

        foreach ($patients as $patient) {
            try {
                $service->syncPatient($patient);
                $this->results['patients']['synced']++;
            } catch (\Exception $e) {
                $this->results['patients']['failed']++;
                $this->results['patients']['errors'][] = "Patient ID {$patient->id}: {$e->getMessage()}";
                Log::error('Failed to sync patient to Satu Sehat', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    protected function syncPractitioners(bool $force): void
    {
        $this->info('Syncing practitioners...');

        $query = Doctor::query();

        if (!$force) {
            $syncedIds = \App\Models\SatusehatResource::where('model_type', Doctor::class)
                ->where('resource_type', 'Practitioner')
                ->where('status', 'synced')
                ->pluck('model_id');

            $query->whereNotIn('id', $syncedIds);
        }

        $doctors = $query->get();
        $total = $doctors->count();

        if ($total === 0) {
            $this->warn('No practitioners to sync.');

            return;
        }

        $this->output->progressStart($total);
        $service = app(PractitionerService::class);

        foreach ($doctors as $doctor) {
            try {
                $service->syncPractitioner($doctor);
                $this->results['practitioners']['synced']++;
            } catch (\Exception $e) {
                $this->results['practitioners']['failed']++;
                $this->results['practitioners']['errors'][] = "Doctor ID {$doctor->id} ({$doctor->name}): {$e->getMessage()}";
                Log::error('Failed to sync practitioner to Satu Sehat', [
                    'doctor_id' => $doctor->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    protected function syncLocations(bool $force): void
    {
        $this->info('Syncing locations...');

        $query = Polyclinic::query();

        if (!$force) {
            $syncedIds = \App\Models\SatusehatResource::where('model_type', Polyclinic::class)
                ->where('resource_type', 'Location')
                ->where('status', 'synced')
                ->pluck('model_id');

            $query->whereNotIn('id', $syncedIds);
        }

        $polyclinics = $query->get();
        $total = $polyclinics->count();

        if ($total === 0) {
            $this->warn('No locations to sync.');

            return;
        }

        $this->output->progressStart($total);
        $service = app(LocationService::class);

        foreach ($polyclinics as $polyclinic) {
            try {
                $service->syncLocation($polyclinic);
                $this->results['locations']['synced']++;
            } catch (\Exception $e) {
                $this->results['locations']['failed']++;
                $this->results['locations']['errors'][] = "Polyclinic ID {$polyclinic->id} ({$polyclinic->name}): {$e->getMessage()}";
                Log::error('Failed to sync location to Satu Sehat', [
                    'polyclinic_id' => $polyclinic->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    protected function syncEncounters(bool $force): void
    {
        $this->info('Syncing encounters...');

        $query = MedicalRecord::with(['patient', 'doctor', 'polyclinic']);

        if (!$force) {
            $syncedIds = \App\Models\SatusehatResource::where('model_type', MedicalRecord::class)
                ->where('resource_type', 'Encounter')
                ->where('status', 'synced')
                ->pluck('model_id');

            $query->whereNotIn('id', $syncedIds);
        }

        $records = $query->get();
        $total = $records->count();

        if ($total === 0) {
            $this->warn('No encounters to sync.');

            return;
        }

        $this->output->progressStart($total);
        $encounterService = app(EncounterService::class);

        foreach ($records as $mr) {
            try {
                $patientSynced = \App\Models\SatusehatResource::where('model_type', Patient::class)
                    ->where('model_id', $mr->patient_id)
                    ->where('resource_type', 'Patient')
                    ->where('status', 'synced')
                    ->exists();

                if (!$patientSynced) {
                    $this->results['encounters']['failed']++;
                    $this->results['encounters']['errors'][] = "MedicalRecord ID {$mr->id}: Patient not synced yet";

                    $this->output->progressAdvance();
                    continue;
                }

                $practitionerSynced = \App\Models\SatusehatResource::where('model_type', Doctor::class)
                    ->where('model_id', $mr->doctor_id)
                    ->where('resource_type', 'Practitioner')
                    ->where('status', 'synced')
                    ->exists();

                if (!$practitionerSynced) {
                    $this->results['encounters']['failed']++;
                    $this->results['encounters']['errors'][] = "MedicalRecord ID {$mr->id}: Practitioner not synced yet (Doctor ID {$mr->doctor_id})";

                    $this->output->progressAdvance();
                    continue;
                }

                $encounterService->createEncounter($mr);
                $this->results['encounters']['synced']++;
            } catch (\Exception $e) {
                $this->results['encounters']['failed']++;
                $this->results['encounters']['errors'][] = "MedicalRecord ID {$mr->id}: {$e->getMessage()}";
                Log::error('Failed to sync encounter to Satu Sehat', [
                    'medical_record_id' => $mr->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    protected function syncConditions(bool $force): void
    {
        $this->info('Syncing conditions...');

        $query = MedicalRecord::with(['patient'])
            ->where(function ($q) {
                $q->whereNotNull('diagnosis_primary')
                  ->where('diagnosis_primary', '!=', '');
            });

        if (!$force) {
            $syncedIds = \App\Models\SatusehatResource::where('model_type', MedicalRecord::class)
                ->where('resource_type', 'Condition')
                ->where('status', 'synced')
                ->pluck('model_id');

            $query->whereNotIn('id', $syncedIds);
        }

        $records = $query->get();
        $total = $records->count();

        if ($total === 0) {
            $this->warn('No conditions to sync.');

            return;
        }

        $this->output->progressStart($total);
        $conditionService = app(ConditionService::class);

        foreach ($records as $mr) {
            try {
                $encounterSynced = \App\Models\SatusehatResource::where('model_type', MedicalRecord::class)
                    ->where('model_id', $mr->id)
                    ->where('resource_type', 'Encounter')
                    ->where('status', 'synced')
                    ->exists();

                if (!$encounterSynced) {
                    $this->results['conditions']['failed']++;
                    $this->results['conditions']['errors'][] = "MedicalRecord ID {$mr->id}: Encounter not synced yet";

                    $this->output->progressAdvance();
                    continue;
                }

                $conditionService->createCondition($mr);
                $this->results['conditions']['synced']++;
            } catch (\Exception $e) {
                $this->results['conditions']['failed']++;
                $this->results['conditions']['errors'][] = "MedicalRecord ID {$mr->id}: {$e->getMessage()}";
                Log::error('Failed to sync condition to Satu Sehat', [
                    'medical_record_id' => $mr->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    protected function displayResults(): void
    {
        $this->info('Sync Results:');
        $this->table(
            ['Type', 'Synced', 'Failed'],
            collect($this->results)->map(function ($result, $type) {
                return [ucfirst($type), $result['synced'], $result['failed']];
            })->values()->toArray()
        );

        foreach ($this->results as $type => $result) {
            if (!empty($result['errors'])) {
                $this->newLine();
                $this->warn(ucfirst($type) . ' errors:');
                foreach ($result['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }
        }
    }
}

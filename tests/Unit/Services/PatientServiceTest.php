<?php

namespace Tests\Unit\Services;

use App\Models\BpjsPatient;
use App\Models\Patient;
use App\Models\User;
use App\Services\BPJS\VClaimService;
use App\Services\PatientService;
use App\Services\SatuSehat\PatientService as SatuSehatPatientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class PatientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PatientService $service;
    protected VClaimService $vClaimMock;
    protected SatuSehatPatientService $satuSehatMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vClaimMock = Mockery::mock(VClaimService::class);
        $this->satuSehatMock = Mockery::mock(SatuSehatPatientService::class);

        $this->satuSehatMock->shouldReceive('syncPatient')
            ->byDefault()
            ->andReturnNull();

        $this->service = new PatientService($this->vClaimMock, $this->satuSehatMock);
    }

    public function test_generate_rm_number_creates_correct_format(): void
    {
        $rm = $this->service->generateRmNumber();

        $this->assertStringStartsWith('RM-' . now()->format('Ymd') . '-', $rm);
        $this->assertMatchesRegularExpression('/^RM-\d{8}-\d{4}$/', $rm);
    }

    public function test_generate_rm_number_increments_sequence(): void
    {
        $prefix = 'RM-' . now()->format('Ymd') . '-';

        Patient::factory()->create(['no_rm' => $prefix . '0001']);
        Patient::factory()->create(['no_rm' => $prefix . '0002']);

        $rm = $this->service->generateRmNumber();

        $this->assertSame($prefix . '0003', $rm);
    }

    public function test_register_creates_patient(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'nik' => '3201010101010001',
            'name' => 'Budi Santoso',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ];

        $patient = $this->service->register($data);

        $this->assertInstanceOf(Patient::class, $patient);
        $this->assertSame('3201010101010001', $patient->nik);
        $this->assertSame('Budi Santoso', $patient->name);
        $this->assertStringStartsWith('RM-' . now()->format('Ymd') . '-', $patient->no_rm);
    }

    public function test_register_creates_bpjs_patient_when_insurance_is_bpjs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'nik' => '3201010101010002',
            'name' => 'Siti Nurhaliza',
            'birth_date' => '1995-05-10',
            'gender' => 'P',
            'insurance_type' => 'BPJS',
            'insurance_number' => '0001234567890',
        ];

        $this->vClaimMock->shouldReceive('getPeserta')
            ->once()
            ->andReturn(null);

        $patient = $this->service->register($data);

        $this->assertDatabaseHas('bpjs_patients', [
            'patient_id' => $patient->id,
            'no_kartu' => '0001234567890',
        ]);
    }

    public function test_find_by_nik_returns_patient(): void
    {
        $patient = Patient::factory()->create(['nik' => '3201010101010003']);

        $found = $this->service->findByNik('3201010101010003');

        $this->assertInstanceOf(Patient::class, $found);
        $this->assertSame($patient->id, $found->id);
    }

    public function test_find_by_nik_returns_null_when_not_found(): void
    {
        $found = $this->service->findByNik('0000000000000000');

        $this->assertNull($found);
    }

    public function test_find_by_rm_number_returns_patient(): void
    {
        $patient = Patient::factory()->create(['no_rm' => 'RM-20260609-0001']);

        $found = $this->service->findByRmNumber('RM-20260609-0001');

        $this->assertInstanceOf(Patient::class, $found);
        $this->assertSame($patient->id, $found->id);
    }

    public function test_find_by_rm_number_returns_null_when_not_found(): void
    {
        $found = $this->service->findByRmNumber('RM-99999999-9999');

        $this->assertNull($found);
    }

    public function test_search_returns_paginated_results(): void
    {
        Patient::factory()->create(['name' => 'Ahmad Dhani']);
        Patient::factory()->create(['name' => 'Ahmad Albar']);

        $result = $this->service->search('Ahmad');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(2, $result->items());
    }

    public function test_search_returns_empty_when_no_match(): void
    {
        Patient::factory()->create(['name' => 'Budi']);

        $result = $this->service->search('ZZZZ');

        $this->assertCount(0, $result->items());
    }

    public function test_get_visit_history_returns_collection(): void
    {
        $patient = Patient::factory()->create();

        $history = $this->service->getVisitHistory($patient);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $history);
    }

    public function test_check_bpjs_status_returns_null_for_non_bpjs(): void
    {
        $patient = Patient::factory()->create([
            'insurance_type' => 'Umum',
            'insurance_number' => null,
        ]);

        $result = $this->service->checkBpjsStatus($patient);

        $this->assertNull($result);
    }

    public function test_sync_to_satusehat_calls_service(): void
    {
        $patient = Patient::factory()->create();

        $this->satuSehatMock->shouldReceive('syncPatient')
            ->once()
            ->with(Mockery::on(fn ($p) => $p->id === $patient->id))
            ->andReturn(['id' => 'ss-id-123']);

        $result = $this->service->syncToSatusehat($patient);

        $this->assertSame(['id' => 'ss-id-123'], $result);
    }

    public function test_register_handles_bpjs_failure_gracefully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->vClaimMock->shouldReceive('getPeserta')
            ->once()
            ->andThrow(new \Exception('BPJS timeout'));

        $data = [
            'nik' => '3201010101010004',
            'name' => 'Test BPJS Fail',
            'birth_date' => '1988-03-15',
            'gender' => 'L',
            'insurance_type' => 'BPJS',
            'insurance_number' => '9999999999',
        ];

        $patient = $this->service->register($data);

        $this->assertNotNull($patient);
        $this->assertDatabaseHas('bpjs_patients', [
            'patient_id' => $patient->id,
        ]);
    }
}

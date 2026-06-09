<?php

namespace Tests\Unit\Services;

use App\Models\BpjsSep;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Services\BPJS\VClaimService;
use App\Services\BpjsSepService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BpjsSepServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BpjsSepService $service;
    protected VClaimService $vClaimMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vClaimMock = Mockery::mock(VClaimService::class);
        $this->service = new BpjsSepService($this->vClaimMock);
    }

    public function test_create_from_queue_returns_null_for_non_bpjs_patient(): void
    {
        $this->skipIfNoBpjsCredentials();

        $patient = Patient::factory()->create(['insurance_type' => 'Umum']);
        $polyclinic = Polyclinic::factory()->create();
        $queue = Queue::factory()->create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
        ]);

        $result = $this->service->createFromQueue($queue);

        $this->assertNull($result);
    }

    public function test_create_from_queue_returns_null_when_no_kartu(): void
    {
        $this->skipIfNoBpjsCredentials();

        $patient = Patient::factory()->create(['insurance_type' => 'BPJS']);
        $polyclinic = Polyclinic::factory()->create();
        $queue = Queue::factory()->create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
        ]);

        $result = $this->service->createFromQueue($queue);

        $this->assertNull($result);
    }

    public function test_create_from_queue_calls_vclaim_and_creates_sep(): void
    {
        $this->skipIfNoBpjsCredentials();

        $patient = Patient::factory()->create(['insurance_type' => 'BPJS']);
        $patient->bpjsPatient()->create([
            'no_kartu' => '0001234567890',
            'status' => 'active',
        ]);

        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);
        $queue = Queue::factory()->create([
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
        ]);

        $this->vClaimMock->shouldReceive('insertSep')
            ->once()
            ->andReturn([
                'sep' => ['noSep' => '0123ABC'],
            ]);

        $sep = $this->service->createFromQueue($queue);

        $this->assertNotNull($sep);
        $this->assertInstanceOf(BpjsSep::class, $sep);
        $this->assertSame('0123ABC', $sep->no_sep);
        $this->assertDatabaseHas('bpjs_seps', [
            'patient_id' => $patient->id,
            'no_sep' => '0123ABC',
        ]);
    }

    public function test_delete_sep_calls_vclaim_and_updates_status(): void
    {
        $this->skipIfNoBpjsCredentials();

        $this->vClaimMock->shouldReceive('deleteSep')
            ->once()
            ->with('SEP001')
            ->andReturn(['metadata' => ['code' => 200]]);

        $sep = BpjsSep::factory()->create([
            'no_sep' => 'SEP001',
            'status' => 'active',
        ]);

        $result = $this->service->deleteSep($sep);

        $this->assertTrue($result);
        $this->assertSame('deleted', $sep->fresh()->status);
    }

    public function test_update_sep_calls_vclaim_and_updates_record(): void
    {
        $this->skipIfNoBpjsCredentials();

        $this->vClaimMock->shouldReceive('updateSep')
            ->once()
            ->andReturn(['metadata' => ['code' => 200]]);

        $sep = BpjsSep::factory()->create([
            'no_sep' => 'SEP002',
            'kode_poli' => 'UMUM',
        ]);

        $result = $this->service->updateSep($sep, [
            'kodePoli' => 'GIGI',
        ]);

        $this->assertNotNull($result);
        $this->assertSame('GIGI', $result->kode_poli);
    }

    public function test_get_sep_returns_null_on_failure(): void
    {
        $this->skipIfNoBpjsCredentials();

        $this->vClaimMock->shouldReceive('getSep')
            ->once()
            ->with('NONEXISTENT')
            ->andThrow(new \Exception('Not found'));

        $result = $this->service->getSep('NONEXISTENT');

        $this->assertNull($result);
    }
}

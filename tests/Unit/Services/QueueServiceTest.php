<?php

namespace Tests\Unit\Services;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use App\Services\BPJS\AntrolService;
use App\Services\BpjsSepService;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class QueueServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QueueService $service;
    protected AntrolService $antrolMock;
    protected BpjsSepService $bpjsSepMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->antrolMock = Mockery::mock(AntrolService::class);
        $this->bpjsSepMock = Mockery::mock(BpjsSepService::class);

        $this->bpjsSepMock->shouldReceive('createFromQueue')
            ->byDefault()
            ->andReturnNull();

        $this->service = new QueueService($this->antrolMock, $this->bpjsSepMock);
    }

    public function test_generate_queue_number_creates_correct_format(): void
    {
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $number = $this->service->generateQueueNumber($polyclinic, '2026-06-09');

        $this->assertMatchesRegularExpression('/^UMUM-\d{8}-\d{3}$/', $number);
    }

    public function test_generate_queue_number_increments_sequence(): void
    {
        $polyclinic = Polyclinic::factory()->create(['code' => 'GIGI']);

        $q = Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_number' => 'GIGI-20260609-001',
            'queue_date' => now()->toDateString(),
        ]);

        Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_number' => 'GIGI-20260609-002',
            'queue_date' => now()->toDateString(),
        ]);

        $number = $this->service->generateQueueNumber($polyclinic, now()->toDateString());

        $this->assertStringStartsWith('GIGI-', $number);
        $this->assertStringEndsWith('003', $number);
    }

    public function test_register_queue_creates_with_waiting_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = Patient::factory()->create();
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $queue = $this->service->registerQueue($patient, $polyclinic, null, 'umum');

        $this->assertInstanceOf(Queue::class, $queue);
        $this->assertSame('waiting', $queue->status);
        $this->assertSame($patient->id, $queue->patient_id);
        $this->assertSame($polyclinic->id, $queue->polyclinic_id);
        $this->assertStringStartsWith('UMUM-', $queue->queue_number);
        $this->assertNotNull($queue->estimated_wait_time);
    }

    public function test_register_queue_skips_bpjs_for_non_bpjs_patient(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $patient = Patient::factory()->create(['insurance_type' => 'Umum']);
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $this->antrolMock->shouldNotReceive('addAntrean');
        $this->bpjsSepMock->shouldNotReceive('createFromQueue');

        $queue = $this->service->registerQueue($patient, $polyclinic, null, 'umum');

        $this->assertSame('waiting', $queue->status);
    }

    public function test_call_next_returns_oldest_waiting_queue(): void
    {
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $queue1 = Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);

        $queue2 = Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);

        $called = $this->service->callNext('UMUM');

        $this->assertNotNull($called);
        $this->assertSame($queue1->id, $called->id);
        $this->assertSame('called', $called->status);
        $this->assertNotNull($called->called_at);
    }

    public function test_call_next_returns_null_when_no_waiting_queue(): void
    {
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $called = $this->service->callNext('UMUM');

        $this->assertNull($called);
    }

    public function test_in_progress_transitions_from_called(): void
    {
        $queue = Queue::factory()->create([
            'status' => 'called',
        ]);

        $result = $this->service->inProgress($queue);

        $this->assertSame('in_progress', $result->status);
    }

    public function test_in_progress_throws_on_invalid_status(): void
    {
        $this->expectException(\RuntimeException::class);

        $queue = Queue::factory()->create([
            'status' => 'waiting',
        ]);

        $this->service->inProgress($queue);
    }

    public function test_complete_transitions_from_in_progress(): void
    {
        $queue = Queue::factory()->create([
            'status' => 'in_progress',
        ]);

        $result = $this->service->complete($queue);

        $this->assertSame('completed', $result->status);
        $this->assertNotNull($result->completed_at);
    }

    public function test_complete_transitions_from_called(): void
    {
        $queue = Queue::factory()->create([
            'status' => 'called',
        ]);

        $result = $this->service->complete($queue);

        $this->assertSame('completed', $result->status);
    }

    public function test_complete_throws_on_invalid_status(): void
    {
        $this->expectException(\RuntimeException::class);

        $queue = Queue::factory()->create([
            'status' => 'waiting',
        ]);

        $this->service->complete($queue);
    }

    public function test_cancel_transitions_to_cancelled(): void
    {
        $queue = Queue::factory()->create([
            'status' => 'waiting',
        ]);

        $result = $this->service->cancel($queue);

        $this->assertSame('cancelled', $result->status);
    }

    public function test_cancel_throws_when_already_completed(): void
    {
        $this->expectException(\RuntimeException::class);

        $queue = Queue::factory()->create([
            'status' => 'completed',
        ]);

        $this->service->cancel($queue);
    }

    public function test_get_queue_stats_returns_correct_counts(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        Queue::factory()->count(3)->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);

        Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'completed',
        ]);

        $stats = $this->service->getQueueStats(now()->toDateString());

        $this->assertSame(4, $stats['total']);
        $this->assertSame(3, $stats['waiting']);
        $this->assertSame(1, $stats['completed']);
    }

    public function test_estimated_wait_time_returns_positive_integer(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        Queue::factory()->count(5)->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);

        $waitTime = $this->service->estimatedWaitTime($polyclinic);

        $this->assertIsInt($waitTime);
        $this->assertGreaterThan(0, $waitTime);
    }

    public function test_estimated_wait_time_returns_zero_when_no_queue(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        $waitTime = $this->service->estimatedWaitTime($polyclinic);

        $this->assertSame(0, $waitTime);
    }

    public function test_get_queue_by_polyclinic_returns_collection(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        Queue::factory()->count(2)->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
        ]);

        $queues = $this->service->getQueueByPolyclinic($polyclinic, now()->toDateString());

        $this->assertCount(2, $queues);
    }
}

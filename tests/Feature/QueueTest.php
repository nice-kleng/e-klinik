<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user);
    }

    public function test_index_renders_queue_list(): void
    {
        $polyclinic = Polyclinic::factory()->create();
        Queue::factory()->count(3)->create([
            'polyclinic_id' => $polyclinic->id,
        ]);

        $response = $this->get(route('queues.index'));

        $response->assertStatus(200);
    }

    public function test_create_renders_form(): void
    {
        $response = $this->get(route('queues.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_queue(): void
    {
        $patient = Patient::factory()->create();
        $polyclinic = Polyclinic::factory()->create();

        $response = $this->post(route('queues.store'), [
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
            'service_type' => 'umum',
        ]);

        $response->assertRedirect(route('queues.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
            'status' => 'waiting',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('queues.store'), []);

        $response->assertSessionHasErrors(['patient_id', 'polyclinic_id', 'service_type']);
    }

    public function test_show_displays_queue(): void
    {
        $queue = Queue::factory()->create();

        $response = $this->get(route('queues.show', $queue));

        $response->assertStatus(200);
    }

    public function test_call_transitions_to_called(): void
    {
        $polyclinic = Polyclinic::factory()->create(['code' => 'UMUM']);

        $queue = Queue::factory()->create([
            'polyclinic_id' => $polyclinic->id,
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);

        $response = $this->post(route('queues.call', $queue));

        $response->assertRedirect(route('queues.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'called',
        ]);
    }

    public function test_in_progress_transitions_from_called(): void
    {
        $queue = Queue::factory()->create(['status' => 'called']);

        $response = $this->post(route('queues.in-progress', $queue));

        $response->assertRedirect(route('queues.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_complete_transitions_from_in_progress(): void
    {
        $queue = Queue::factory()->create(['status' => 'in_progress']);

        $response = $this->post(route('queues.complete', $queue));

        $response->assertRedirect(route('queues.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'completed',
        ]);
    }

    public function test_cancel_transitions_to_cancelled(): void
    {
        $queue = Queue::factory()->create(['status' => 'waiting']);

        $response = $this->post(route('queues.cancel', $queue));

        $response->assertRedirect(route('queues.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_display_renders_tv_display(): void
    {
        $response = $this->get(route('queues.display'));

        $response->assertStatus(200);
    }

    public function test_index_is_forbidden_without_auth(): void
    {
        auth()->logout();

        $response = $this->get(route('queues.index'));

        $response->assertRedirect(route('login'));
    }
}

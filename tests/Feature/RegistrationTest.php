<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
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

    public function test_index_renders_registration_page(): void
    {
        $response = $this->get(route('registration.index'));

        $response->assertStatus(200);
    }

    public function test_search_patient_by_nik_returns_json(): void
    {
        $patient = Patient::factory()->create(['nik' => '1234567890123456']);

        $response = $this->get(route('registration.search', ['nik' => '1234567890123456']));

        $response->assertJson(['found' => true]);
        $response->assertJsonPath('data.id', $patient->id);
    }

    public function test_search_patient_returns_not_found(): void
    {
        $response = $this->get(route('registration.search', ['nik' => '9999999999999999']));

        $response->assertJson(['found' => false]);
    }

    public function test_store_creates_new_patient_and_queue(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        $response = $this->post(route('registration.store'), [
            'nik' => '1234567890123456',
            'name' => 'Pasien Baru',
            'birth_date' => '1990-01-15',
            'gender' => 'L',
            'insurance_type' => 'Umum',
            'polyclinic_id' => $polyclinic->id,
            'service_type' => 'umum',
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'nik' => '1234567890123456',
            'name' => 'Pasien Baru',
            'insurance_type' => 'Umum',
        ]);

        $this->assertDatabaseHas('queues', [
            'polyclinic_id' => $polyclinic->id,
        ]);
    }

    public function test_store_creates_bpjs_patient_with_insurance_number(): void
    {
        $polyclinic = Polyclinic::factory()->create();

        $response = $this->post(route('registration.store'), [
            'nik' => '1234567890123456',
            'name' => 'Pasien BPJS',
            'birth_date' => '1990-01-15',
            'gender' => 'P',
            'insurance_type' => 'BPJS',
            'insurance_number' => '0001234567890',
            'polyclinic_id' => $polyclinic->id,
            'service_type' => 'umum',
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'nik' => '1234567890123456',
            'insurance_type' => 'BPJS',
            'insurance_number' => '0001234567890',
        ]);

        $this->assertDatabaseHas('queues', [
            'service_type' => 'umum',
        ]);
    }

    public function test_store_uses_existing_patient(): void
    {
        $patient = Patient::factory()->create(['nik' => '1234567890123456']);
        $polyclinic = Polyclinic::factory()->create();

        $response = $this->post(route('registration.store'), [
            'patient_id' => $patient->id,
            'insurance_type' => 'Umum',
            'polyclinic_id' => $polyclinic->id,
            'service_type' => 'umum',
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('queues', [
            'patient_id' => $patient->id,
            'polyclinic_id' => $polyclinic->id,
        ]);
    }

    public function test_store_rejects_duplicate_nik(): void
    {
        Patient::factory()->create(['nik' => '1234567890123456', 'name' => 'Existing Patient']);
        $polyclinic = Polyclinic::factory()->create();

        $response = $this->post(route('registration.store'), [
            'nik' => '1234567890123456',
            'name' => 'Duplicate Name',
            'birth_date' => '1990-01-15',
            'gender' => 'L',
            'insurance_type' => 'Umum',
            'polyclinic_id' => $polyclinic->id,
            'service_type' => 'umum',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('registration.store'), []);

        $response->assertSessionHasErrors(['insurance_type', 'polyclinic_id', 'service_type']);
    }

    public function test_cashier_can_access_registration(): void
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        $this->actingAs($cashier);

        $response = $this->get(route('registration.index'));

        $response->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
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

    public function test_index_renders_patient_list(): void
    {
        Patient::factory()->count(3)->create();

        $response = $this->get(route('patients.index'));

        $response->assertStatus(200);
    }

    public function test_create_renders_form(): void
    {
        $response = $this->get(route('patients.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_patient(): void
    {
        $data = [
            'nik' => '3201010101010001',
            'name' => 'Budi Santoso',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
        ];

        $response = $this->post(route('patients.store'), $data);

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'nik' => '3201010101010001',
            'name' => 'Budi Santoso',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->post(route('patients.store'), []);

        $response->assertSessionHasErrors(['nik', 'name', 'birth_date', 'gender']);
    }

    public function test_store_validates_unique_nik(): void
    {
        Patient::factory()->create(['nik' => '3201010101010001']);

        $response = $this->post(route('patients.store'), [
            'nik' => '3201010101010001',
            'name' => 'Another',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
        ]);

        $response->assertSessionHasErrors(['nik']);
    }

    public function test_show_displays_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->get(route('patients.show', $patient));

        $response->assertStatus(200);
        $response->assertSee($patient->name);
    }

    public function test_edit_renders_form(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->get(route('patients.edit', $patient));

        $response->assertStatus(200);
    }

    public function test_update_patient(): void
    {
        $patient = Patient::factory()->create([
            'nik' => '3201010101010001',
        ]);

        $response = $this->put(route('patients.update', $patient), [
            'nik' => '3201010101010001',
            'name' => 'Updated Name',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
        ]);

        $response->assertRedirect(route('patients.show', $patient));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_patient(): void
    {
        $patient = Patient::factory()->create();

        $response = $this->delete(route('patients.destroy', $patient));

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted($patient);
    }

    public function test_search_filters_patients(): void
    {
        Patient::factory()->create(['name' => 'Budi Santoso']);
        Patient::factory()->create(['name' => 'Siti Nurhaliza']);

        $response = $this->get(route('patients.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
    }

    public function test_index_is_forbidden_without_auth(): void
    {
        auth()->logout();

        $response = $this->get(route('patients.index'));

        $response->assertRedirect(route('login'));
    }
}

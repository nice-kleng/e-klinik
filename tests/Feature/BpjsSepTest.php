<?php

namespace Tests\Feature;

use App\Models\BpjsSep;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BpjsSepTest extends TestCase
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

    public function test_index_renders_sep_list(): void
    {
        $this->skipIfNoBpjsCredentials();

        BpjsSep::factory()->count(2)->create();

        $response = $this->get(route('bpjs-seps.index'));

        $response->assertStatus(200);
    }

    public function test_create_renders_form(): void
    {
        $this->skipIfNoBpjsCredentials();

        $response = $this->get(route('bpjs-seps.create'));

        $response->assertStatus(200);
    }

    public function test_store_creates_sep(): void
    {
        $this->skipIfNoBpjsCredentials();

        $patient = Patient::factory()->create();

        $response = $this->post(route('bpjs-seps.store'), [
            'patient_id' => $patient->id,
            'no_sep' => '0123ABC',
            'no_kartu' => '0001234567890',
            'tgl_pelayanan' => now()->toDateString(),
            'kode_poli' => 'UMUM',
        ]);

        $response->assertRedirect(route('bpjs-seps.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bpjs_seps', [
            'no_sep' => '0123ABC',
            'patient_id' => $patient->id,
        ]);
    }

    public function test_show_displays_sep(): void
    {
        $this->skipIfNoBpjsCredentials();

        $sep = BpjsSep::factory()->create();

        $response = $this->get(route('bpjs-seps.show', $sep));

        $response->assertStatus(200);
    }

    public function test_destroy_deletes_sep(): void
    {
        $this->skipIfNoBpjsCredentials();

        $sep = BpjsSep::factory()->create();

        $response = $this->delete(route('bpjs-seps.destroy', $sep));

        $response->assertRedirect(route('bpjs-seps.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bpjs_seps', [
            'id' => $sep->id,
            'deleted_at' => null,
        ]);
    }

    public function test_index_is_forbidden_without_auth(): void
    {
        auth()->logout();

        $response = $this->get(route('bpjs-seps.index'));

        $response->assertRedirect(route('login'));
    }
}

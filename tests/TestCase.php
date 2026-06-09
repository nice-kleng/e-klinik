<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRoles();
    }

    protected function seedRoles(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        if (Role::count() > 0) {
            return;
        }

        collect(['admin', 'doctor', 'pharmacist', 'laborant', 'cashier', 'nurse'])
            ->each(fn ($role) => Role::create(['name' => $role, 'guard_name' => 'web']));
    }

    protected function skipIfNoBpjsCredentials(): void
    {
        if (empty(config('bpjs.cons_id')) || empty(config('bpjs.secret_key'))) {
            $this->markTestSkipped(
                'BPJS credentials tidak tersedia (belum ada akun Trustmark dari BPJS Kesehatan). '
                . 'Set BPJS_CONS_ID, BPJS_SECRET_KEY, dan BPJS_USER_KEY di .env untuk mengaktifkan test ini.'
            );
        }
    }
}

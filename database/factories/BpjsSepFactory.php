<?php

namespace Database\Factories;

use App\Models\BpjsSep;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BpjsSepFactory extends Factory
{
    protected $model = BpjsSep::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'no_sep' => fake()->unique()->bothify('####??##??##'),
            'no_kartu' => fake()->numerify('################'),
            'tgl_pelayanan' => now()->toDateString(),
            'kode_poli' => 'UMUM',
            'status' => 'active',
            'response_raw' => [],
            'created_by' => User::factory(),
        ];
    }
}

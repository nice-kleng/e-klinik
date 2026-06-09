<?php

namespace Database\Factories;

use App\Models\Polyclinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class PolyclinicFactory extends Factory
{
    protected $model = Polyclinic::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify(strtoupper('????')),
            'name' => fake()->unique()->word() . ' Poli',
            'is_active' => true,
        ];
    }
}

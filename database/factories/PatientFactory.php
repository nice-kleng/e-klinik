<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'no_rm' => 'RM-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'nik' => fake()->unique()->numerify('################'),
            'name' => fake()->name(),
            'birth_date' => fake()->date(max: now()->subYears(10)),
            'gender' => fake()->randomElement(['L', 'P']),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'insurance_type' => 'Umum',
            'created_by' => User::factory(),
        ];
    }
}

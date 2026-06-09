<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QueueFactory extends Factory
{
    protected $model = Queue::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'polyclinic_id' => Polyclinic::factory(),
            'doctor_id' => null,
            'queue_number' => 'UMUM-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'queue_date' => now()->toDateString(),
            'status' => 'waiting',
            'estimated_wait_time' => 15,
            'check_in_at' => now(),
            'created_by' => User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->startOfHour()->addDays(fake()->numberBetween(1, 20))->setTime(fake()->numberBetween(9, 13), 0);

        return [
            'patient_id' => Patient::factory(),
            'modality' => fake()->randomElement(['online', 'presencial']),
            'starts_at' => $start,
            'ends_at' => $start->copy()->addMinutes(50),
            'break_minutes' => 10,
            'status' => 'confirmada',
            'source' => fake()->randomElement(array_keys(Appointment::SOURCES)),
            'reason' => fake()->sentence(),
            'price' => fake()->randomElement([120000, 150000, 180000]),
        ];
    }
}

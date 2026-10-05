<?php

namespace Database\Factories;

use App\Models\ClinicalEntry;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicalEntry>
 */
class ClinicalEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'session_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'title' => fake()->randomElement(['Sesión de seguimiento', 'Primera entrevista', 'Revisión de objetivos', 'Trabajo con técnicas de relajación']),
            'content' => '<p>'.fake()->paragraph(4).'</p>',
        ];
    }
}

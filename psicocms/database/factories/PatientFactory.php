<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(['mujer', 'hombre']);

        return [
            'phone' => '6'.fake()->unique()->numerify('########'),
            'first_name' => $gender === 'mujer' ? fake()->firstNameFemale() : fake()->firstNameMale(),
            'last_name' => fake()->lastName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'birth_date' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $gender,
            'city' => fake()->city(),
            'preferred_modality' => fake()->randomElement(['online', 'presencial']),
            'status' => fake()->randomElement(['activo', 'activo', 'activo', 'pausado', 'alta']),
            'reason' => fake()->randomElement([
                'Ansiedad y dificultad para dormir.',
                'Estrés laboral y agotamiento.',
                'Problemas de pareja.',
                'Baja autoestima.',
                'Duelo por la pérdida de un familiar.',
            ]),
            'source' => fake()->randomElement(['web', 'manual']),
        ];
    }
}

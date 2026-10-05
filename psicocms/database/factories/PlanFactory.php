<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Sesión individual',
            'modality' => fake()->randomElement(['online', 'presencial']),
            'price' => 150000,
            'duration_label' => '50 minutos',
            'is_active' => true,
        ];
    }
}

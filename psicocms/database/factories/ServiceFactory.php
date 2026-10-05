<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $title = ucfirst(fake()->unique()->words(2, true));

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'icon' => 'fa-solid fa-heart',
            'excerpt' => fake()->sentence(12),
            'is_active' => true,
        ];
    }
}

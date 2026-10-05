<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(6), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(18),
            'content' => '<p>'.implode('</p><p>', fake()->paragraphs(4)).'</p>',
            'status' => 'publicado',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'meta_description' => fake()->sentence(15),
        ];
    }
}

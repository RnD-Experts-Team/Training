<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),
            'title' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'order' => 0,
        ];
    }
}

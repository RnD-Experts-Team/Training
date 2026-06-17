<?php

namespace Database\Factories;

use App\Enums\Importance;
use App\Models\Category;
use App\Models\ChecklistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistItem>
 */
class ChecklistItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'parent_id' => null,
            'title' => fake()->sentence(),
            'content' => fake()->optional()->paragraph(),
            'importance' => fake()->randomElement(Importance::cases()),
            'order' => 0,
        ];
    }

    /**
     * Make this item a sub-item of the given parent.
     */
    public function child(ChecklistItem $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $parent->category_id,
            'parent_id' => $parent->id,
        ]);
    }
}

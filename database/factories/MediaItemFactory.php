<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\ChecklistItem;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'checklist_item_id' => ChecklistItem::factory(),
            'type' => MediaType::Link,
            'url' => fake()->url(),
            'path' => null,
            'label' => fake()->words(3, true),
            'order' => 0,
        ];
    }

    public function link(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::Link,
            'url' => fake()->url(),
            'path' => null,
        ]);
    }

    public function file(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::File,
            'url' => null,
            'path' => 'training/media/'.fake()->uuid().'.pdf',
        ]);
    }
}

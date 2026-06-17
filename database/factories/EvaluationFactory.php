<?php

namespace Database\Factories;

use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Trainee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trainee_id' => Trainee::factory(),
            'checklist_item_id' => ChecklistItem::factory(),
            'completed' => false,
            'rating' => null,
            'notes' => null,
            'evaluated_by' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
            'completed_at' => now(),
        ]);
    }
}

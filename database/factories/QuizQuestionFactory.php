<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'prompt' => fake()->sentence().'?',
            'order' => 0,
        ];
    }

    /**
     * Attach 4 options (A–D), the first marked correct.
     */
    public function withOptions(): static
    {
        return $this->afterCreating(function (QuizQuestion $question): void {
            foreach (range(0, 3) as $index) {
                $question->options()->create([
                    'text' => fake()->words(3, true),
                    'is_correct' => $index === 0,
                    'order' => $index,
                ]);
            }
        });
    }
}

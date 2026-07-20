<?php

namespace Tests\Feature\Training;

use App\Actions\Training\RecordEvaluation;
use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sub-items can nest deeper than one level (the builder allows it), so the
 * completion cascade has to walk the whole tree in both directions.
 */
class DeepCascadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_parent_cascades_to_grandchildren(): void
    {
        $category = Category::factory()->create();
        $parent = ChecklistItem::factory()->create(['category_id' => $category->id]);
        $child = ChecklistItem::factory()->child($parent)->create();
        $grandchild = ChecklistItem::factory()->child($child)->create();

        $trainee = Trainee::factory()->create();
        $evaluator = User::factory()->create();

        app(RecordEvaluation::class)->handle(
            $trainee,
            $parent,
            ['completed' => true, 'rating' => null, 'notes' => null],
            $evaluator,
        );

        foreach ([$child, $grandchild] as $item) {
            $this->assertTrue(
                (bool) Evaluation::where('trainee_id', $trainee->id)
                    ->where('checklist_item_id', $item->id)
                    ->value('completed'),
                "Expected item {$item->id} to be completed by the cascade.",
            );
        }
    }

    public function test_completing_a_grandchild_recomputes_every_ancestor(): void
    {
        $category = Category::factory()->create();
        $parent = ChecklistItem::factory()->create(['category_id' => $category->id]);
        $child = ChecklistItem::factory()->child($parent)->create();
        $grandchild = ChecklistItem::factory()->child($child)->create();

        $trainee = Trainee::factory()->create();
        $evaluator = User::factory()->create();

        app(RecordEvaluation::class)->handle(
            $trainee,
            $grandchild,
            ['completed' => true, 'rating' => 90, 'notes' => 'Nailed it'],
            $evaluator,
        );

        // The only leaf is complete, so the child *and* the grandparent roll up.
        foreach ([$child, $parent] as $ancestor) {
            $this->assertTrue(
                (bool) Evaluation::where('trainee_id', $trainee->id)
                    ->where('checklist_item_id', $ancestor->id)
                    ->value('completed'),
                "Expected ancestor {$ancestor->id} to roll up to complete.",
            );
        }
    }

    public function test_uncompleting_a_grandchild_reopens_its_ancestors(): void
    {
        $category = Category::factory()->create();
        $parent = ChecklistItem::factory()->create(['category_id' => $category->id]);
        $child = ChecklistItem::factory()->child($parent)->create();
        $grandchild = ChecklistItem::factory()->child($child)->create();

        $trainee = Trainee::factory()->create();
        $evaluator = User::factory()->create();
        $action = app(RecordEvaluation::class);

        $action->handle($trainee, $grandchild, ['completed' => true], $evaluator);
        $action->handle($trainee, $grandchild, ['completed' => false], $evaluator);

        foreach ([$child, $parent] as $ancestor) {
            $this->assertFalse(
                (bool) Evaluation::where('trainee_id', $trainee->id)
                    ->where('checklist_item_id', $ancestor->id)
                    ->value('completed'),
                "Expected ancestor {$ancestor->id} to reopen.",
            );
        }
    }
}

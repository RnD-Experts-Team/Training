<?php

namespace Tests\Feature\Training;

use App\Actions\Training\RecordEvaluation;
use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationCascadeTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    private Trainee $trainee;

    private ChecklistItem $parent;

    private ChecklistItem $childA;

    private ChecklistItem $childB;

    protected function setUp(): void
    {
        parent::setUp();

        $store = Store::factory()->create();
        $this->manager = User::factory()->manager($store)->create();
        $this->trainee = Trainee::factory()->forStore($store)->create();
        $this->trainee->managers()->attach($this->manager);

        $category = Category::factory()->create();
        $this->parent = ChecklistItem::factory()->create(['category_id' => $category->id]);
        $this->childA = ChecklistItem::factory()->child($this->parent)->create();
        $this->childB = ChecklistItem::factory()->child($this->parent)->create();
    }

    private function record(ChecklistItem $item, bool $completed, ?int $rating = null): void
    {
        app(RecordEvaluation::class)->handle(
            $this->trainee,
            $item,
            ['completed' => $completed, 'rating' => $rating, 'notes' => null],
            $this->manager,
        );
    }

    private function completed(ChecklistItem $item): bool
    {
        return (bool) Evaluation::where('trainee_id', $this->trainee->id)
            ->where('checklist_item_id', $item->id)
            ->value('completed');
    }

    public function test_completing_a_parent_completes_all_children(): void
    {
        $this->record($this->parent, true);

        $this->assertTrue($this->completed($this->parent));
        $this->assertTrue($this->completed($this->childA));
        $this->assertTrue($this->completed($this->childB));
    }

    public function test_parent_auto_completes_when_all_children_complete(): void
    {
        $this->record($this->childA, true);
        $this->assertFalse($this->completed($this->parent));

        $this->record($this->childB, true);
        $this->assertTrue($this->completed($this->parent));
    }

    public function test_uncompleting_a_child_uncompletes_the_parent(): void
    {
        $this->record($this->parent, true);
        $this->assertTrue($this->completed($this->parent));

        $this->record($this->childA, false);

        $this->assertFalse($this->completed($this->childA));
        $this->assertFalse($this->completed($this->parent));
        $this->assertTrue($this->completed($this->childB));
    }

    public function test_evaluation_is_unique_per_trainee_and_item(): void
    {
        $this->record($this->childA, true, 4);
        $this->record($this->childA, true, 5);

        $this->assertSame(1, Evaluation::where('trainee_id', $this->trainee->id)
            ->where('checklist_item_id', $this->childA->id)
            ->count());
    }

    public function test_evaluator_and_timestamp_are_recorded(): void
    {
        $this->record($this->childA, true);

        $evaluation = Evaluation::where('trainee_id', $this->trainee->id)
            ->where('checklist_item_id', $this->childA->id)
            ->sole();

        $this->assertSame($this->manager->id, $evaluation->evaluated_by);
        $this->assertNotNull($evaluation->completed_at);
    }

    public function test_endpoint_rejects_out_of_range_rating(): void
    {
        foreach ([101, -1] as $rating) {
            $this->actingAs($this->manager)
                ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                    'completed' => true,
                    'rating' => $rating,
                    'notes' => 'note',
                ])
                ->assertSessionHasErrors('rating');
        }
    }

    public function test_completing_requires_a_rating(): void
    {
        $this->actingAs($this->manager)
            ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                'completed' => true,
                'notes' => 'A note but no score',
            ])
            ->assertSessionHasErrors('rating');

        $this->assertFalse($this->completed($this->childA));
    }

    public function test_completing_requires_notes(): void
    {
        $this->actingAs($this->manager)
            ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                'completed' => true,
                'rating' => 50,
            ])
            ->assertSessionHasErrors('notes');

        $this->assertFalse($this->completed($this->childA));
    }

    public function test_uncompleting_does_not_require_rating_or_notes(): void
    {
        $this->actingAs($this->manager)
            ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                'completed' => false,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_percentage_bounds_are_accepted(): void
    {
        foreach ([0, 100] as $rating) {
            $this->actingAs($this->manager)
                ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                    'completed' => true,
                    'rating' => $rating,
                    'notes' => 'Scored',
                ])
                ->assertSessionHasNoErrors();

            $this->assertSame($rating, Evaluation::where('checklist_item_id', $this->childA->id)->value('rating'));
        }
    }

    public function test_unassigned_manager_cannot_evaluate(): void
    {
        $other = User::factory()->manager()->create();

        $this->actingAs($other)
            ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                'completed' => true,
                'rating' => 80,
                'notes' => 'note',
            ])
            ->assertForbidden();
    }

    public function test_assigned_manager_can_evaluate_via_endpoint(): void
    {
        $this->actingAs($this->manager)
            ->put(route('trainees.evaluations.update', [$this->trainee, $this->childA]), [
                'completed' => true,
                'rating' => 90,
                'notes' => 'Great work',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($this->completed($this->childA));
        $this->assertSame(90, Evaluation::where('checklist_item_id', $this->childA->id)->value('rating'));
    }
}

<?php

namespace Tests\Feature\Training;

use App\Actions\Training\RecordEvaluation;
use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use App\Services\Training\TraineeProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentStepTest extends TestCase
{
    use RefreshDatabase;

    private function progress(): TraineeProgress
    {
        return app(TraineeProgress::class);
    }

    private function complete(Trainee $trainee, ChecklistItem $item): void
    {
        app(RecordEvaluation::class)->handle(
            $trainee,
            $item,
            ['completed' => true, 'rating' => null, 'notes' => null],
            User::factory()->create(),
        );
    }

    public function test_current_step_is_first_incomplete_item_in_order(): void
    {
        $section = Section::factory()->create(['order' => 0]);
        $category = Category::factory()->create(['section_id' => $section->id, 'order' => 0]);
        $first = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 0]);
        ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 1]);
        $trainee = Trainee::factory()->create();

        $this->assertSame($first->id, $this->progress()->detail($trainee)['currentStepId']);
    }

    public function test_current_step_advances_as_items_complete(): void
    {
        $section = Section::factory()->create(['order' => 0]);
        $category = Category::factory()->create(['section_id' => $section->id, 'order' => 0]);
        $first = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 0]);
        $second = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 1]);
        $trainee = Trainee::factory()->create();

        $this->complete($trainee, $first);

        $this->assertSame($second->id, $this->progress()->detail($trainee)['currentStepId']);

        $this->complete($trainee, $second);

        $this->assertNull($this->progress()->detail($trainee)['currentStepId']);
    }

    public function test_current_step_respects_section_order(): void
    {
        $later = Section::factory()->create(['order' => 1]);
        $earlier = Section::factory()->create(['order' => 0]);
        $laterCategory = Category::factory()->create(['section_id' => $later->id, 'order' => 0]);
        $earlierCategory = Category::factory()->create(['section_id' => $earlier->id, 'order' => 0]);
        ChecklistItem::factory()->create(['category_id' => $laterCategory->id, 'order' => 0]);
        $earliest = ChecklistItem::factory()->create(['category_id' => $earlierCategory->id, 'order' => 0]);
        $trainee = Trainee::factory()->create();

        $this->assertSame($earliest->id, $this->progress()->detail($trainee)['currentStepId']);
    }

    public function test_current_step_skips_parents_and_picks_first_child(): void
    {
        $section = Section::factory()->create(['order' => 0]);
        $category = Category::factory()->create(['section_id' => $section->id, 'order' => 0]);
        $parent = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 0]);
        $firstChild = ChecklistItem::factory()->child($parent)->create(['order' => 0]);
        ChecklistItem::factory()->child($parent)->create(['order' => 1]);
        $trainee = Trainee::factory()->create();

        $this->assertSame($firstChild->id, $this->progress()->detail($trainee)['currentStepId']);
    }
}

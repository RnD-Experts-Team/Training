<?php

namespace Tests\Feature\Training;

use App\Actions\Training\RecordEvaluation;
use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Section;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_roster_reports_completion_and_average_rating(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);

        $category = Category::factory()->create();
        $itemA = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 0]);
        ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 1]);

        app(RecordEvaluation::class)->handle(
            $trainee,
            $itemA,
            ['completed' => true, 'rating' => 4, 'notes' => null],
            $manager,
        );

        $this->actingAs($manager)
            ->get(route('trainees.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/trainees/index')
                ->has('trainees', 1)
                ->where('trainees.0.stats.completed', 1)
                ->where('trainees.0.stats.total', 2)
                ->where('trainees.0.stats.average_rating', fn ($value) => (float) $value === 4.0)
            );
    }

    public function test_trainee_without_evaluations_reports_zero(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);

        $category = Category::factory()->create();
        ChecklistItem::factory()->count(3)->create(['category_id' => $category->id]);

        $this->actingAs($manager)
            ->get(route('trainees.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('trainees.0.stats.completed', 0)
                ->where('trainees.0.stats.total', 3)
                ->where('trainees.0.stats.average_rating', null)
            );
    }

    public function test_drill_in_returns_progress_and_current_step(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);

        $section = Section::factory()->create(['order' => 0]);
        $category = Category::factory()->create(['section_id' => $section->id, 'order' => 0]);
        $first = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 0]);
        $second = ChecklistItem::factory()->create(['category_id' => $category->id, 'order' => 1]);

        app(RecordEvaluation::class)->handle(
            $trainee,
            $first,
            ['completed' => true, 'rating' => null, 'notes' => null],
            $manager,
        );

        $this->actingAs($manager)
            ->get(route('trainees.show', $trainee))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/trainees/show')
                ->where('progress.currentStepId', $second->id)
                ->where('progress.stats.completed', 1)
                ->has('progress.sections', 1)
            );
    }

    public function test_super_admin_can_filter_roster_by_store(): void
    {
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        Trainee::factory()->count(2)->forStore($storeA)->create();
        Trainee::factory()->forStore($storeB)->create();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('trainees.index', ['store' => $storeA->id]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('trainees', 2));

        $this->actingAs($admin)
            ->get(route('trainees.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('trainees', 3));
    }
}

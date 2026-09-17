<?php

namespace Tests\Feature\Training;

use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Section;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use App\Services\Training\TraineeProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DevelopmentZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_manager_can_flag_and_unflag_a_trainee(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);

        $this->actingAs($manager)
            ->patch(route('trainees.development.flag', $trainee))
            ->assertSessionHasNoErrors();
        $this->assertTrue($trainee->refresh()->needs_development);

        $this->actingAs($manager)
            ->patch(route('trainees.development.unflag', $trainee))
            ->assertSessionHasNoErrors();
        $this->assertFalse($trainee->refresh()->needs_development);
    }

    public function test_unassigned_manager_cannot_flag_a_trainee(): void
    {
        $manager = User::factory()->manager()->create();
        $trainee = Trainee::factory()->create();

        $this->actingAs($manager)
            ->patch(route('trainees.development.flag', $trainee))
            ->assertForbidden();
    }

    public function test_updating_the_plan_only_accepts_leaf_items_from_published_sections(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $trainee = Trainee::factory()->needsDevelopmentFlagged()->create();

        $publishedSection = Section::factory()->published()->create();
        $publishedCategory = Category::factory()->create(['section_id' => $publishedSection->id]);
        $validLeaf = ChecklistItem::factory()->create(['category_id' => $publishedCategory->id]);

        $draftSection = Section::factory()->draft()->create();
        $draftCategory = Category::factory()->create(['section_id' => $draftSection->id]);
        $itemInDraftSection = ChecklistItem::factory()->create(['category_id' => $draftCategory->id]);

        $parentWithChildren = ChecklistItem::factory()->create(['category_id' => $publishedCategory->id]);
        ChecklistItem::factory()->child($parentWithChildren)->create();

        $this->actingAs($admin)
            ->put(route('trainees.development.update', $trainee), [
                'item_ids' => [$validLeaf->id, $itemInDraftSection->id, $parentWithChildren->id, 999999],
            ])
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [$validLeaf->id],
            $trainee->developmentItems()->pluck('checklist_items.id')->all(),
        );
    }

    public function test_development_plan_reuses_the_standard_evaluation_and_reports_its_own_completion(): void
    {
        $trainee = Trainee::factory()->needsDevelopmentFlagged()->create();
        $itemA = ChecklistItem::factory()->create();
        $itemB = ChecklistItem::factory()->create();
        $trainee->developmentItems()->attach([$itemA->id, $itemB->id]);

        Evaluation::factory()->create([
            'trainee_id' => $trainee->id,
            'checklist_item_id' => $itemA->id,
            'completed' => true,
            'rating' => 80,
        ]);

        $plan = app(TraineeProgress::class)->developmentPlan($trainee);

        $this->assertSame(['completed' => 1, 'total' => 2], $plan['stats']);
        $this->assertCount(2, $plan['items']);
        $scoredItem = collect($plan['items'])->firstWhere('id', $itemA->id);
        $this->assertTrue($scoredItem['evaluation']['completed']);
        $this->assertSame(80, $scoredItem['evaluation']['rating']);
    }

    public function test_dashboard_development_zone_lists_flagged_trainees_with_progress(): void
    {
        $this->withoutVite();
        $admin = User::factory()->superAdmin()->create();

        $flagged = Trainee::factory()->needsDevelopmentFlagged()->create(['name' => 'Needs Help']);
        $item = ChecklistItem::factory()->create();
        $flagged->developmentItems()->attach($item->id);
        Evaluation::factory()->create([
            'trainee_id' => $flagged->id,
            'checklist_item_id' => $item->id,
            'completed' => true,
        ]);

        Trainee::factory()->create(['name' => 'Not Flagged']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('developmentZone', 1)
                ->where('developmentZone.0.name', 'Needs Help')
                ->where('developmentZone.0.stats.completed', 1)
                ->where('developmentZone.0.stats.total', 1)
            );
    }

    public function test_manager_only_sees_their_own_flagged_trainees_in_the_development_zone(): void
    {
        $this->withoutVite();
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        $mine = Trainee::factory()->forStore($store)->needsDevelopmentFlagged()->create();
        Trainee::factory()->needsDevelopmentFlagged()->create();

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('developmentZone', 1)
                ->where('developmentZone.0.id', $mine->id)
            );
    }

    public function test_guests_are_redirected_from_the_development_zone_page(): void
    {
        $this->get(route('development-zone.index'))->assertRedirect(route('login'));
    }

    public function test_the_development_zone_page_lists_flagged_trainees_and_respects_store_filter(): void
    {
        $this->withoutVite();
        $admin = User::factory()->superAdmin()->create();

        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $flaggedA = Trainee::factory()->forStore($storeA)->needsDevelopmentFlagged()->create();
        $flaggedB = Trainee::factory()->forStore($storeB)->needsDevelopmentFlagged()->create();
        Trainee::factory()->forStore($storeA)->create();

        $this->actingAs($admin)
            ->get(route('development-zone.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/development-zone/index')
                ->has('trainees', 2)
            );

        $this->actingAs($admin)
            ->get(route('development-zone.index', ['store' => $storeA->id]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('trainees', 1)
                ->where('trainees.0.id', $flaggedA->id)
            );

        $this->assertNotNull($flaggedB);
    }
}

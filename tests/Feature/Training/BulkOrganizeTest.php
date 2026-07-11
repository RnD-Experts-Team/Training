<?php

namespace Tests\Feature\Training;

use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\MediaItem;
use App\Models\Section;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkOrganizeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->superAdmin()->create();
    }

    public function test_items_are_bulk_moved_to_another_category(): void
    {
        $section = Section::factory()->create();
        $source = Category::factory()->create(['section_id' => $section->id]);
        $target = Category::factory()->create(['section_id' => $section->id]);
        Category::factory()->create(); // unrelated existing item to seed target order
        $existing = ChecklistItem::factory()->create(['category_id' => $target->id, 'order' => 3]);

        $one = ChecklistItem::factory()->create(['category_id' => $source->id, 'order' => 0]);
        $two = ChecklistItem::factory()->create(['category_id' => $source->id, 'order' => 1]);

        $this->actingAs($this->admin())
            ->post(route('training.items.move'), [
                'ids' => [$one->id, $two->id],
                'category_id' => $target->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($target->id, $one->refresh()->category_id);
        $this->assertSame($target->id, $two->refresh()->category_id);
        $this->assertNull($one->parent_id);
        // Appended after the existing item (order 3).
        $this->assertGreaterThan($existing->order, $one->order);
        $this->assertGreaterThan($one->order, $two->order);
    }

    public function test_moving_a_parent_item_carries_its_sub_items(): void
    {
        $source = Category::factory()->create();
        $target = Category::factory()->create();

        $parent = ChecklistItem::factory()->create(['category_id' => $source->id, 'order' => 0]);
        $child = ChecklistItem::factory()->child($parent)->create(['order' => 0]);

        $this->actingAs($this->admin())
            ->post(route('training.items.move'), [
                'ids' => [$parent->id],
                'category_id' => $target->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($target->id, $parent->refresh()->category_id);
        $this->assertNull($parent->parent_id);
        // The sub-item follows into the new category but stays under its parent.
        $this->assertSame($target->id, $child->refresh()->category_id);
        $this->assertSame($parent->id, $child->parent_id);
    }

    public function test_categories_are_bulk_moved_to_another_section(): void
    {
        $from = Section::factory()->create();
        $to = Section::factory()->create();
        Category::factory()->create(['section_id' => $to->id, 'order' => 2]); // seed order

        $category = Category::factory()->create(['section_id' => $from->id, 'order' => 0]);
        $item = ChecklistItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->post(route('training.categories.move'), [
                'ids' => [$category->id],
                'section_id' => $to->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($to->id, $category->refresh()->section_id);
        $this->assertGreaterThan(2, $category->order);
        // Items travel with their category (they key off category_id).
        $this->assertSame($category->id, $item->refresh()->category_id);
    }

    public function test_bulk_delete_items_cascades_children_media_and_evaluations(): void
    {
        $category = Category::factory()->create();
        $parent = ChecklistItem::factory()->create(['category_id' => $category->id]);
        $child = ChecklistItem::factory()->child($parent)->create();
        $media = MediaItem::factory()->create(['checklist_item_id' => $parent->id]);
        $trainee = Trainee::factory()->create();
        $evaluation = Evaluation::factory()->create([
            'trainee_id' => $trainee->id,
            'checklist_item_id' => $parent->id,
        ]);

        $this->actingAs($this->admin())
            ->post(route('training.items.bulk-destroy'), ['ids' => [$parent->id]])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('checklist_items', ['id' => $parent->id]);
        $this->assertDatabaseMissing('checklist_items', ['id' => $child->id]);
        $this->assertDatabaseMissing('media_items', ['id' => $media->id]);
        $this->assertDatabaseMissing('evaluations', ['id' => $evaluation->id]);
    }

    public function test_bulk_delete_categories_removes_their_items(): void
    {
        $category = Category::factory()->create();
        $item = ChecklistItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->post(route('training.categories.bulk-destroy'), ['ids' => [$category->id]])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('checklist_items', ['id' => $item->id]);
    }

    public function test_moving_items_requires_an_existing_target_category(): void
    {
        $item = ChecklistItem::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('training.items.move'), [
                'ids' => [$item->id],
                'category_id' => 999999,
            ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_moving_categories_requires_an_existing_target_section(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('training.categories.move'), [
                'ids' => [$category->id],
                'section_id' => 999999,
            ])
            ->assertSessionHasErrors('section_id');
    }

    public function test_managers_cannot_bulk_organize_content(): void
    {
        $manager = User::factory()->manager()->create();
        $category = Category::factory()->create();
        $item = ChecklistItem::factory()->create(['category_id' => $category->id]);

        $this->actingAs($manager)->post(route('training.items.move'), [
            'ids' => [$item->id], 'category_id' => $category->id,
        ])->assertForbidden();

        $this->actingAs($manager)->post(route('training.items.bulk-destroy'), [
            'ids' => [$item->id],
        ])->assertForbidden();

        $this->actingAs($manager)->post(route('training.categories.move'), [
            'ids' => [$category->id], 'section_id' => $category->section_id,
        ])->assertForbidden();

        $this->actingAs($manager)->post(route('training.categories.bulk-destroy'), [
            'ids' => [$category->id],
        ])->assertForbidden();
    }
}

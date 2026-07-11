<?php

namespace Tests\Feature\Training;

use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Section;
use App\Models\Trainee;
use App\Services\Training\TraineeProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionAverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_reports_scored_averages_per_category_and_section(): void
    {
        $section = Section::factory()->create(['order' => 0]);
        $catA = Category::factory()->create(['section_id' => $section->id, 'order' => 0]);
        $catB = Category::factory()->create(['section_id' => $section->id, 'order' => 1]);

        $a1 = ChecklistItem::factory()->create(['category_id' => $catA->id, 'order' => 0]);
        $a2 = ChecklistItem::factory()->create(['category_id' => $catA->id, 'order' => 1]);
        $b1 = ChecklistItem::factory()->create(['category_id' => $catB->id, 'order' => 0]);
        // An unrated item must not drag the averages down.
        ChecklistItem::factory()->create(['category_id' => $catB->id, 'order' => 1]);

        // A second section left entirely unrated.
        $emptySection = Section::factory()->create(['order' => 1]);
        $emptyCategory = Category::factory()->create(['section_id' => $emptySection->id, 'order' => 0]);
        ChecklistItem::factory()->create(['category_id' => $emptyCategory->id, 'order' => 0]);

        $trainee = Trainee::factory()->create();

        foreach ([$a1->id => 80, $a2->id => 100, $b1->id => 50] as $itemId => $rating) {
            Evaluation::factory()->create([
                'trainee_id' => $trainee->id,
                'checklist_item_id' => $itemId,
                'rating' => $rating,
            ]);
        }

        $detail = app(TraineeProgress::class)->detail($trainee);
        $scored = $detail['sections'][0];
        $empty = $detail['sections'][1];

        $this->assertSame(90.0, $scored['categories'][0]['average_rating']); // (80 + 100) / 2
        $this->assertSame(50.0, $scored['categories'][1]['average_rating']); // only b1 scored
        $this->assertEqualsWithDelta(76.7, $scored['average_rating'], 0.05); // (80 + 100 + 50) / 3

        // The unrated section and its category contribute nothing.
        $this->assertNull($empty['average_rating']);
        $this->assertNull($empty['categories'][0]['average_rating']);

        // Overall average is the mean of scored items only — unrated items and
        // the unrated section are excluded.
        $this->assertEqualsWithDelta(76.7, $detail['stats']['average_rating'], 0.05);
    }
}

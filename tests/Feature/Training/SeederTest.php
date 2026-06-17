<?php

namespace Tests\Feature\Training;

use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Section;
use Database\Seeders\TrainingContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_the_full_training_program(): void
    {
        $this->seed(TrainingContentSeeder::class);

        $this->assertSame(8, Section::count(), 'Expected all 8 stations.');
        $this->assertGreaterThanOrEqual(80, ChecklistItem::count(), 'Expected the bulk of the workbook items.');

        // Every section has at least one category, and every category has items.
        $this->assertFalse(Section::doesntHave('categories')->exists());
        $this->assertFalse(Category::doesntHave('items')->exists());

        // Importance casts resolve to the enum without error.
        ChecklistItem::query()->each(fn (ChecklistItem $item) => $this->assertNotNull($item->importance->label()));
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(TrainingContentSeeder::class);
        $sections = Section::count();
        $items = ChecklistItem::count();

        $this->seed(TrainingContentSeeder::class);

        $this->assertSame($sections, Section::count());
        $this->assertSame($items, ChecklistItem::count());
    }
}

<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChecklistItem;
use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TrainingContentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the standardized training program from the curated workbook fixture.
     * Idempotent: re-running upserts rather than duplicating.
     */
    public function run(): void
    {
        $path = database_path('data/training_content.json');

        if (! File::exists($path)) {
            return;
        }

        /** @var array{sections: array<int, array<string, mixed>>} $data */
        $data = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data): void {
            foreach ($data['sections'] as $sectionIndex => $sectionData) {
                $section = Section::updateOrCreate(
                    ['title' => $sectionData['title']],
                    [
                        'icon' => $sectionData['icon'] ?? null,
                        'order' => $sectionData['order'] ?? $sectionIndex,
                        'pie_content_review' => $sectionData['pie_content_review'] ?? null,
                        'screen_to_shoulder' => $sectionData['screen_to_shoulder'] ?? null,
                        'hands_on_shifts' => $sectionData['hands_on_shifts'] ?? null,
                    ],
                );

                foreach ($sectionData['categories'] as $categoryIndex => $categoryData) {
                    $category = Category::updateOrCreate(
                        ['section_id' => $section->id, 'title' => $categoryData['title']],
                        ['order' => $categoryData['order'] ?? $categoryIndex],
                    );

                    foreach ($categoryData['items'] as $itemIndex => $itemData) {
                        $this->seedItem($category, $itemData, $itemIndex, null);
                    }
                }
            }
        });
    }

    /**
     * @param  array<string, mixed>  $itemData
     */
    private function seedItem(Category $category, array $itemData, int $index, ?int $parentId): void
    {
        $item = ChecklistItem::updateOrCreate(
            [
                'category_id' => $category->id,
                'parent_id' => $parentId,
                'title' => $itemData['title'],
            ],
            [
                'content' => $itemData['content'] ?? null,
                'importance' => $itemData['importance'] ?? 'highly_important',
                'order' => $itemData['order'] ?? $index,
            ],
        );

        // Replace media wholesale to keep re-runs idempotent.
        $item->media()->delete();
        foreach ($itemData['media'] ?? [] as $mediaIndex => $mediaData) {
            $item->media()->create([
                'type' => $mediaData['type'],
                'url' => $mediaData['url'] ?? null,
                'path' => $mediaData['path'] ?? null,
                'label' => $mediaData['label'] ?? null,
                'order' => $mediaData['order'] ?? $mediaIndex,
            ]);
        }

        foreach ($itemData['children'] ?? [] as $childIndex => $childData) {
            $this->seedItem($category, $childData, $childIndex, $item->id);
        }
    }
}

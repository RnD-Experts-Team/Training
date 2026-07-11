<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\BulkDeleteRequest;
use App\Http\Requests\Training\ChecklistItemRequest;
use App\Http\Requests\Training\MoveItemsRequest;
use App\Http\Requests\Training\ReorderRequest;
use App\Models\Category;
use App\Models\ChecklistItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChecklistItemController extends Controller
{
    public function store(ChecklistItemRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $parentId = $data['parent_id'] ?? null;

        $category->checklistItems()->create([
            ...$data,
            'order' => (int) ChecklistItem::where('category_id', $category->id)
                ->where('parent_id', $parentId)
                ->max('order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Checklist item created.')]);

        return back();
    }

    public function update(ChecklistItemRequest $request, ChecklistItem $checklistItem): RedirectResponse
    {
        $checklistItem->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Checklist item updated.')]);

        return back();
    }

    public function destroy(ChecklistItem $checklistItem): RedirectResponse
    {
        $checklistItem->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Checklist item deleted.')]);

        return back();
    }

    public function reorder(ReorderRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category): void {
            foreach ($request->orderedItems() as $item) {
                $category->checklistItems()->whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return back();
    }

    public function move(MoveItemsRequest $request): RedirectResponse
    {
        $categoryId = (int) $request->validated('category_id');
        $ids = $request->ids();

        DB::transaction(function () use ($ids, $categoryId): void {
            $order = (int) ChecklistItem::where('category_id', $categoryId)
                ->whereNull('parent_id')
                ->max('order');

            // Selected items become top-level in the target category.
            foreach (ChecklistItem::whereIn('id', $ids)->get() as $item) {
                $item->update([
                    'category_id' => $categoryId,
                    'parent_id' => null,
                    'order' => ++$order,
                ]);
            }

            // Sub-items follow their moved ancestor into the new category.
            $descendants = $this->descendantIds($ids);

            if ($descendants !== []) {
                ChecklistItem::whereIn('id', $descendants)->update(['category_id' => $categoryId]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Items moved.')]);

        return back();
    }

    public function bulkDestroy(BulkDeleteRequest $request): RedirectResponse
    {
        ChecklistItem::whereIn('id', $request->ids())->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Items deleted.')]);

        return back();
    }

    /**
     * All descendant ids beneath the given item ids (excluding the ids themselves).
     *
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    private function descendantIds(array $ids): array
    {
        $collected = [];
        $frontier = $ids;

        while ($frontier !== []) {
            $children = ChecklistItem::whereIn('parent_id', $frontier)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->reject(fn (int $id): bool => in_array($id, $collected, true) || in_array($id, $ids, true))
                ->values()
                ->all();

            if ($children === []) {
                break;
            }

            $collected = array_merge($collected, $children);
            $frontier = $children;
        }

        return $collected;
    }
}

<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\ChecklistItemRequest;
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
}

<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\BulkDeleteRequest;
use App\Http\Requests\Training\CategoryRequest;
use App\Http\Requests\Training\MoveCategoriesRequest;
use App\Http\Requests\Training\ReorderRequest;
use App\Models\Category;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function store(CategoryRequest $request, Section $section): RedirectResponse
    {
        $section->categories()->create([
            ...$request->validated(),
            'order' => (int) $section->categories()->max('order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category created.')]);

        return back();
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category updated.')]);

        return back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Category deleted.')]);

        return back();
    }

    public function reorder(ReorderRequest $request, Section $section): RedirectResponse
    {
        DB::transaction(function () use ($request, $section): void {
            foreach ($request->orderedItems() as $item) {
                $section->categories()->whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return back();
    }

    public function move(MoveCategoriesRequest $request): RedirectResponse
    {
        $sectionId = (int) $request->validated('section_id');

        DB::transaction(function () use ($request, $sectionId): void {
            $order = (int) Category::where('section_id', $sectionId)->max('order');

            foreach (Category::whereIn('id', $request->ids())->get() as $category) {
                $category->update(['section_id' => $sectionId, 'order' => ++$order]);
            }
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categories moved.')]);

        return back();
    }

    public function bulkDestroy(BulkDeleteRequest $request): RedirectResponse
    {
        Category::whereIn('id', $request->ids())->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categories deleted.')]);

        return back();
    }
}

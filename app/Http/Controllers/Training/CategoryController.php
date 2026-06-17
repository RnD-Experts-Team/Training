<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\CategoryRequest;
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
}

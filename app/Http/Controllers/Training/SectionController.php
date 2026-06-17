<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\ReorderRequest;
use App\Http\Requests\Training\SectionRequest;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SectionController extends Controller
{
    /**
     * Builder home: every section with category/item counts.
     */
    public function index(): Response
    {
        $sections = Section::ordered()
            ->withCount(['categories', 'checklistItems'])
            ->get();

        return Inertia::render('training/builder/index', [
            'sections' => $sections,
        ]);
    }

    /**
     * Deep builder for a single section: its categories, items, sub-items, media.
     */
    public function edit(Section $section): Response
    {
        $section->load([
            'categories' => fn ($query) => $query->orderBy('order'),
            'categories.items.children.media',
            'categories.items.media',
        ]);

        return Inertia::render('training/builder/section', [
            'section' => $section,
        ]);
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        Section::create([
            ...$request->validated(),
            'order' => (int) Section::max('order') + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section created.')]);

        return back();
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section updated.')]);

        return back();
    }

    public function destroy(Section $section): RedirectResponse
    {
        $section->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Section deleted.')]);

        return to_route('training.sections.index');
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->orderedItems() as $item) {
                Section::whereKey($item['id'])->update(['order' => $item['order']]);
            }
        });

        return back();
    }
}

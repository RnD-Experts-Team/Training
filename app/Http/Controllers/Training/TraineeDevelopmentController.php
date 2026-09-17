<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\ChecklistItem;
use App\Models\Store;
use App\Models\Trainee;
use App\Services\Training\TraineeProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TraineeDevelopmentController extends Controller
{
    /**
     * The Development Zone — every trainee currently flagged for extra
     * coaching support, with progress against their own curated plan.
     */
    public function index(Request $request, TraineeProgress $progress): Response
    {
        $this->authorize('viewAny', Trainee::class);

        $user = $request->user();
        $canChooseStore = $user->canFilterByStore();
        $storeId = $user->resolveStoreFilter($request->integer('store') ?: null);

        $trainees = Trainee::visibleTo($user)
            ->inStore($storeId)
            ->active()
            ->needsDevelopment()
            ->with('store:id,name')
            ->orderBy('name')
            ->get();

        $stats = $progress->developmentStats($trainees->pluck('id'));

        return Inertia::render('training/development-zone/index', [
            'trainees' => $trainees->map(fn (Trainee $trainee): array => [
                'id' => $trainee->id,
                'name' => $trainee->name,
                'position' => $trainee->position,
                'store' => $trainee->store->only(['id', 'name']),
                'stats' => $stats[$trainee->id] ?? ['completed' => 0, 'total' => 0],
            ])->values(),
            'stores' => $canChooseStore
                ? ($user->isSuperAdmin() ? Store::orderBy('name')->get(['id', 'name']) : $user->stores()->orderBy('stores.name')->get(['stores.id', 'stores.name']))
                : [],
            'filters' => ['store' => $storeId],
            'canChooseStore' => $canChooseStore,
        ]);
    }

    /**
     * Flag a trainee as needing extra coaching support — surfaces them in
     * the Development Zone and unlocks their Development Plan tab.
     */
    public function flag(Trainee $trainee): RedirectResponse
    {
        $this->authorize('update', $trainee);

        $trainee->update(['needs_development' => true]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee flagged for development.')]);

        return back();
    }

    /**
     * Unflag a trainee. The plan's item selections are left in place (just
     * hidden) in case they're flagged again later.
     */
    public function unflag(Trainee $trainee): RedirectResponse
    {
        $this->authorize('update', $trainee);

        $trainee->update(['needs_development' => false]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee unflagged.')]);

        return back();
    }

    /**
     * Replace which existing checklist items make up this trainee's
     * Development Plan. Only real leaf items from published stations are
     * ever accepted — anything else submitted is silently dropped rather
     * than erroring, since the picker itself never offers them.
     */
    public function updatePlan(Request $request, Trainee $trainee): RedirectResponse
    {
        $this->authorize('update', $trainee);

        $requested = collect($request->input('item_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id);

        $validIds = ChecklistItem::query()
            ->whereDoesntHave('children')
            ->whereHas('category.section', fn ($query) => $query->published())
            ->whereIn('id', $requested)
            ->pluck('id');

        $trainee->developmentItems()->sync($validIds);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Development plan updated.')]);

        return back();
    }
}

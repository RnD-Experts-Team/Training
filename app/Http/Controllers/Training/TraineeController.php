<?php

namespace App\Http\Controllers\Training;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Training\TraineeRequest;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use App\Services\Training\TraineeProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TraineeController extends Controller
{
    /**
     * Roster report — scoped to the manager's assigned trainees (super admins
     * see all and may filter by store).
     */
    public function index(Request $request, TraineeProgress $progress): Response
    {
        $this->authorize('viewAny', Trainee::class);

        $user = $request->user();
        $storeId = $user->isSuperAdmin() ? $request->integer('store') ?: null : null;

        $trainees = Trainee::visibleTo($user)
            ->inStore($storeId)
            ->with('store')
            ->orderBy('name')
            ->get();

        $stats = $progress->rosterStats($trainees->pluck('id'));

        return Inertia::render('training/trainees/index', [
            'trainees' => $trainees->map(fn (Trainee $trainee): array => [
                'id' => $trainee->id,
                'name' => $trainee->name,
                'position' => $trainee->position,
                'store' => $trainee->store->only(['id', 'name']),
                'stats' => $stats[$trainee->id],
            ])->all(),
            'stores' => $user->isSuperAdmin() ? Store::orderBy('name')->get(['id', 'name']) : [],
            'filters' => ['store' => $storeId],
            'canChooseStore' => $user->isSuperAdmin(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Trainee::class);

        return Inertia::render('training/trainees/create', [
            'stores' => $request->user()->isSuperAdmin() ? Store::orderBy('name')->get(['id', 'name']) : [],
            'canChooseStore' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function store(TraineeRequest $request): RedirectResponse
    {
        $this->authorize('create', Trainee::class);

        $user = $request->user();
        $storeId = $user->isSuperAdmin() ? $request->integer('store_id') : $user->store_id;

        if (! $storeId) {
            throw ValidationException::withMessages(['store_id' => __('Please choose a store.')]);
        }

        $trainee = Trainee::create([
            'name' => $request->validated('name'),
            'position' => $request->validated('position'),
            'hired_at' => $request->validated('hired_at'),
            'store_id' => $storeId,
            'created_by' => $user->id,
        ]);

        // Managers automatically own the trainees they create.
        if ($user->isManager()) {
            $trainee->managers()->attach($user->id);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee added.')]);

        return to_route('trainees.show', $trainee);
    }

    public function show(Request $request, Trainee $trainee, TraineeProgress $progress): Response
    {
        $this->authorize('view', $trainee);

        $trainee->load('store', 'managers:id,name');
        $isSuperAdmin = $request->user()->isSuperAdmin();

        return Inertia::render('training/trainees/show', [
            'trainee' => [
                'id' => $trainee->id,
                'name' => $trainee->name,
                'position' => $trainee->position,
                'hired_at' => $trainee->hired_at?->toDateString(),
                'store' => $trainee->store->only(['id', 'name']),
                'managers' => $trainee->managers->map->only(['id', 'name'])->values(),
            ],
            'progress' => $progress->detail($trainee),
            'canAssignManagers' => $isSuperAdmin,
            'availableManagers' => $isSuperAdmin
                ? User::where('role', Role::Manager)
                    ->where('store_id', $trainee->store_id)
                    ->orderBy('name')
                    ->get(['id', 'name'])
                : [],
        ]);
    }

    public function edit(Request $request, Trainee $trainee): Response
    {
        $this->authorize('update', $trainee);

        return Inertia::render('training/trainees/edit', [
            'trainee' => $trainee->only(['id', 'name', 'position', 'hired_at', 'store_id']),
            'stores' => $request->user()->isSuperAdmin() ? Store::orderBy('name')->get(['id', 'name']) : [],
            'canChooseStore' => $request->user()->isSuperAdmin(),
        ]);
    }

    public function update(TraineeRequest $request, Trainee $trainee): RedirectResponse
    {
        $this->authorize('update', $trainee);

        $attributes = [
            'name' => $request->validated('name'),
            'position' => $request->validated('position'),
            'hired_at' => $request->validated('hired_at'),
        ];

        if ($request->user()->isSuperAdmin() && $request->integer('store_id')) {
            $attributes['store_id'] = $request->integer('store_id');
        }

        $trainee->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee updated.')]);

        return to_route('trainees.show', $trainee);
    }

    public function destroy(Trainee $trainee): RedirectResponse
    {
        $this->authorize('delete', $trainee);

        $trainee->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Trainee removed.')]);

        return to_route('trainees.index');
    }
}

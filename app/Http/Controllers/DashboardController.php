<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\ChecklistItem;
use App\Models\Section;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use App\Services\Training\TraineeProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, TraineeProgress $progress): Response
    {
        $user = $request->user();

        return $user->isSuperAdmin()
            ? $this->superAdminDashboard()
            : $this->managerDashboard($user, $progress);
    }

    private function superAdminDashboard(): Response
    {
        $users = User::with('store:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'store' => $user->store?->only(['id', 'name']),
                'joined' => $user->created_at?->toDateString(),
            ]);

        $stores = Store::withCount(['managers', 'trainees'])
            ->orderBy('name')
            ->get()
            ->map(fn (Store $store): array => [
                'id' => $store->id,
                'name' => $store->name,
                'address' => $store->address,
                'managers_count' => $store->managers_count,
                'trainees_count' => $store->trainees_count,
            ]);

        return Inertia::render('dashboard', [
            'isSuperAdmin' => true,
            'currentUserId' => auth()->id(),
            'users' => $users,
            'stores' => $stores,
            'roleOptions' => collect(Role::cases())->map(fn (Role $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
            'stats' => [
                'users' => $users->count(),
                'stores' => $stores->count(),
                'trainees' => Trainee::count(),
                'sections' => Section::count(),
                'items' => ChecklistItem::count(),
            ],
        ]);
    }

    private function managerDashboard(User $user, TraineeProgress $progress): Response
    {
        $trainees = Trainee::visibleTo($user)->with('store:id,name')->orderBy('name')->get();
        $stats = $progress->rosterStats($trainees->pluck('id'));

        $leafTotal = $trainees->isNotEmpty() ? ($stats[$trainees->first()->id]['total'] ?? 0) : 0;
        $completedSum = array_sum(array_column($stats, 'completed'));
        $ratings = array_filter(array_column($stats, 'average_rating'), fn ($value) => $value !== null);

        return Inertia::render('dashboard', [
            'isSuperAdmin' => false,
            'managerStats' => [
                'trainees' => $trainees->count(),
                'completion' => $trainees->count() * $leafTotal > 0
                    ? (int) round(($completedSum / ($trainees->count() * $leafTotal)) * 100)
                    : 0,
                'average_rating' => $ratings !== [] ? round(array_sum($ratings) / count($ratings), 1) : null,
            ],
            'trainees' => $trainees->map(fn (Trainee $trainee): array => [
                'id' => $trainee->id,
                'name' => $trainee->name,
                'position' => $trainee->position,
                'store' => $trainee->store->only(['id', 'name']),
                'stats' => $stats[$trainee->id],
            ])->values(),
        ]);
    }
}

<?php

namespace App\Policies;

use App\Models\Trainee;
use App\Models\User;

class TraineePolicy
{
    /**
     * Any authenticated manager may view their roster.
     */
    public function viewAny(User $user): bool
    {
        return $user->isManager();
    }

    /**
     * A manager may view a trainee only if assigned to them.
     */
    public function view(User $user, Trainee $trainee): bool
    {
        return $this->isAssigned($user, $trainee);
    }

    /**
     * Any manager may create trainees (auto-assigned to themselves).
     */
    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Trainee $trainee): bool
    {
        return $this->isAssigned($user, $trainee);
    }

    public function delete(User $user, Trainee $trainee): bool
    {
        return $this->isAssigned($user, $trainee);
    }

    /**
     * A manager may record evaluations for trainees assigned to them.
     */
    public function evaluate(User $user, Trainee $trainee): bool
    {
        return $this->isAssigned($user, $trainee);
    }

    /**
     * Reassigning managers is reserved for super admins (handled by Gate::before).
     */
    public function assignManagers(User $user, Trainee $trainee): bool
    {
        return false;
    }

    private function isAssigned(User $user, Trainee $trainee): bool
    {
        return $user->isManager()
            && $user->assignedTrainees()->whereKey($trainee->getKey())->exists();
    }
}

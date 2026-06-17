<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Http\Requests\Training\AssignManagersRequest;
use App\Models\Trainee;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TraineeManagerController extends Controller
{
    /**
     * Sync the managers assigned to a trainee. Super admins only.
     */
    public function update(AssignManagersRequest $request, Trainee $trainee): RedirectResponse
    {
        $this->authorize('assignManagers', $trainee);

        $trainee->managers()->sync($request->managerIds());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Assigned managers updated.')]);

        return back();
    }
}

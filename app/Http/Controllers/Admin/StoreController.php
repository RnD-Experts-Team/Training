<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function store(StoreRequest $request): RedirectResponse
    {
        Store::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store created.')]);

        return back();
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        $store->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store updated.')]);

        return back();
    }

    public function destroy(Store $store): RedirectResponse
    {
        // Deleting a store cascade-deletes its trainees, so require it be empty first.
        if ($store->trainees()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('Move or remove this store\'s trainees before deleting it.'),
            ]);

            return back();
        }

        $store->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store removed.')]);

        return back();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        // Deleting a store cascade-deletes its trainees and every manager_store
        // link, so refuse while either would lose data. Checked inside a
        // transaction so a concurrent write can't slip between check and delete.
        $refusal = DB::transaction(function () use ($store): ?string {
            $store = $store->newQuery()->lockForUpdate()->find($store->id);

            if (! $store) {
                return __('That store no longer exists.');
            }

            if ($store->trainees()->exists()) {
                return __('Move or remove this store\'s trainees before deleting it.');
            }

            if ($store->managers()->exists()) {
                return __('Unassign this store\'s managers before deleting it.');
            }

            $store->delete();

            return null;
        });

        if ($refusal !== null) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $refusal]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Store removed.')]);

        return back();
    }
}

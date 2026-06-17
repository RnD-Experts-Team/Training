<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class UserController extends Controller
{
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $role = Role::from($request->validated('role'));

        User::forceCreate([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => $role,
            'store_id' => $role === Role::Manager ? $request->validated('store_id') : null,
            'email_verified_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created.')]);

        return back();
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // Guard against locking yourself out of the super admin role.
        if ($user->is($request->user()) && $request->validated('role') !== $user->role->value) {
            throw ValidationException::withMessages(['role' => __('You cannot change your own role.')]);
        }

        $role = Role::from($request->validated('role'));
        $user->role = $role;
        $user->store_id = $role === Role::Manager ? $request->validated('store_id') : null;
        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated.')]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(request()->user())) {
            throw ValidationException::withMessages(['user' => __('You cannot delete your own account.')]);
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User removed.')]);

        return back();
    }
}

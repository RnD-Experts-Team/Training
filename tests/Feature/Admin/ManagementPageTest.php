<?php

namespace Tests\Feature\Admin;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ManagementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $this->get(route('admin.management'))->assertRedirect(route('login'));
    }

    public function test_managers_cannot_open_the_management_page(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->get(route('admin.management'))->assertForbidden();
    }

    public function test_super_admin_sees_users_and_stores(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $store = Store::factory()->create();
        User::factory()->manager($store)->create();

        $this->actingAs($admin)
            ->get(route('admin.management'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('admin/management')
                ->has('users.data')
                ->has('stores.data')
                ->has('storeOptions')
                ->has('roleOptions')
                ->has('currentUserId')
            );
    }

    public function test_users_are_paginated(): void
    {
        $admin = User::factory()->superAdmin()->create();
        User::factory()->count(15)->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.management'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users.data', 10)          // first page holds 10 of 16
                ->where('users.total', 16)
                ->where('users.last_page', 2)
            );

        $this->actingAs($admin)
            ->get(route('admin.management', ['users_page' => 2]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->has('users.data', 6)           // second page holds the remaining 6
                ->where('users.current_page', 2)
            );
    }
}

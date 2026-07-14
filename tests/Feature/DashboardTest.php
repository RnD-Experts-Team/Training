<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_managers_see_their_overview()
    {
        $this->withoutVite();
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('dashboard')
                ->where('isSuperAdmin', false)
                ->has('managerStats')
            );
    }

    public function test_super_admins_see_the_control_center()
    {
        $this->withoutVite();
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('dashboard')
                ->where('isSuperAdmin', true)
                ->has('stats')
                // User + store management moved to the dedicated Management page.
                ->missing('users')
                ->missing('stores')
            );
    }
}

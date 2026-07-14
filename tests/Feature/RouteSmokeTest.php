<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_index_redirects_to_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/settings')
            ->assertRedirect('/settings/profile');
    }

    public function test_appearance_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('appearance.edit'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('settings/appearance')
            );
    }

    public function test_passkey_discovery_endpoint_is_public(): void
    {
        $this->get(route('well-known.passkeys'))->assertOk();
    }
}

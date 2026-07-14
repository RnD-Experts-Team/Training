<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_forbidden_renders_the_styled_error_page(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('admin.management'))
            ->assertStatus(403)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('errors/error')
                ->where('status', 403)
            );
    }

    public function test_missing_record_renders_the_styled_error_page(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('trainees.show', 999999))
            ->assertStatus(404)
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('errors/error')
                ->where('status', 404)
            );
    }
}

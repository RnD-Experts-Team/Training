<?php

namespace Tests\Feature\Admin;

use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ManagerStoreAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_store_grants_the_manager_its_trainees(): void
    {
        $this->withoutVite();

        $admin = User::factory()->superAdmin()->create();
        $targetStore = Store::factory()->create();
        $trainee = Trainee::factory()->forStore($targetStore)->create(['name' => 'Store Crew']);

        // The manager starts in a different, empty store — nothing to see.
        $manager = User::factory()->manager()->create();
        $this->assertCount(0, Trainee::visibleTo($manager)->get());

        // The super admin assigns the manager to the store that has the trainee.
        $this->actingAs($admin)
            ->patch(route('admin.users.update', $manager), [
                'role' => 'manager',
                'store_ids' => [$targetStore->id],
            ])
            ->assertSessionHasNoErrors();

        $manager = $manager->fresh();

        // The manager now sees the store's trainee — model scope and roster page.
        $this->assertTrue(Trainee::visibleTo($manager)->get()->contains($trainee));
        $this->assertTrue($manager->can('view', $trainee));

        $this->actingAs($manager)
            ->get(route('trainees.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/trainees/index')
                ->has('trainees', 1)
                ->where('trainees.0.name', 'Store Crew')
            );
    }
}

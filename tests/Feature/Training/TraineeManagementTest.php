<?php

namespace Tests\Feature\Training;

use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraineeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $this->get(route('trainees.index'))->assertRedirect(route('login'));
    }

    public function test_manager_creating_a_trainee_auto_assigns_and_sets_store(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        $this->actingAs($manager)
            ->post(route('trainees.store'), ['name' => 'Sam Crew', 'position' => 'Cook'])
            ->assertSessionHasNoErrors();

        $trainee = Trainee::firstWhere('name', 'Sam Crew');
        $this->assertNotNull($trainee);
        $this->assertSame($store->id, $trainee->store_id);
        $this->assertSame($manager->id, $trainee->created_by);
        $this->assertTrue($trainee->managers()->whereKey($manager->id)->exists());
    }

    public function test_super_admin_must_choose_a_store(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('trainees.store'), ['name' => 'No Store'])
            ->assertSessionHasErrors('store_id');
    }

    public function test_manager_cannot_view_an_unassigned_trainee(): void
    {
        $manager = User::factory()->manager()->create();
        $trainee = Trainee::factory()->create();

        $this->actingAs($manager)->get(route('trainees.show', $trainee))->assertForbidden();
    }

    public function test_assigned_manager_can_view_a_trainee(): void
    {
        $this->withoutVite();
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($manager);

        $this->actingAs($manager)->get(route('trainees.show', $trainee))->assertOk();
    }

    public function test_manager_cannot_update_or_delete_unassigned_trainee(): void
    {
        $manager = User::factory()->manager()->create();
        $trainee = Trainee::factory()->create();

        $this->actingAs($manager)
            ->put(route('trainees.update', $trainee), ['name' => 'Hacked'])
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('trainees.destroy', $trainee))
            ->assertForbidden();
    }

    public function test_only_super_admin_can_assign_managers(): void
    {
        $store = Store::factory()->create();
        $managerA = User::factory()->manager($store)->create();
        $managerB = User::factory()->manager($store)->create();
        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach($managerA);

        // An assigned manager may not reassign.
        $this->actingAs($managerA)
            ->put(route('trainees.managers.update', $trainee), ['manager_ids' => [$managerB->id]])
            ->assertForbidden();

        // Super admin can.
        $admin = User::factory()->superAdmin()->create();
        $this->actingAs($admin)
            ->put(route('trainees.managers.update', $trainee), [
                'manager_ids' => [$managerA->id, $managerB->id],
            ])
            ->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing(
            [$managerA->id, $managerB->id],
            $trainee->managers()->pluck('users.id')->all(),
        );
    }
}

<?php

namespace Tests\Feature\Training;

use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class TraineeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected(): void
    {
        $this->get(route('trainees.index'))->assertRedirect(route('login'));
    }

    public function test_create_and_edit_pages_render(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $trainee = Trainee::factory()->create();

        $this->actingAs($admin)
            ->get(route('trainees.create'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/trainees/create')
                ->has('stores')
            );

        $this->actingAs($admin)
            ->get(route('trainees.edit', $trainee))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/trainees/edit')
                ->where('trainee.id', $trainee->id)
            );
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

    public function test_multi_store_manager_picks_a_store_for_a_new_trainee(): void
    {
        [$storeA, $storeB] = Store::factory()->count(2)->create();
        $manager = User::factory()->manager($storeA)->create();
        $manager->stores()->attach($storeB->id);

        // Two stores → must choose.
        $this->actingAs($manager->fresh())
            ->post(route('trainees.store'), ['name' => 'Ambiguous'])
            ->assertSessionHasErrors('store_id');

        // Picking one of their stores works and lands the trainee there.
        $this->actingAs($manager->fresh())
            ->post(route('trainees.store'), ['name' => 'Picked', 'store_id' => $storeB->id])
            ->assertSessionHasNoErrors();
        $this->assertSame($storeB->id, Trainee::firstWhere('name', 'Picked')->store_id);
    }

    public function test_manager_cannot_create_a_trainee_in_a_foreign_store(): void
    {
        $manager = User::factory()->manager()->create();
        $foreign = Store::factory()->create();

        $this->actingAs($manager)
            ->post(route('trainees.store'), ['name' => 'Sneaky', 'store_id' => $foreign->id])
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

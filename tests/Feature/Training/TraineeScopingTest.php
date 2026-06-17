<?php

namespace Tests\Feature\Training;

use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraineeScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_only_sees_assigned_trainees(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        $assigned = Trainee::factory()->forStore($store)->create();
        $assigned->managers()->attach($manager);
        Trainee::factory()->forStore($store)->create(); // unassigned

        $visible = Trainee::visibleTo($manager)->get();

        $this->assertCount(1, $visible);
        $this->assertTrue($visible->contains($assigned));
    }

    public function test_trainee_assigned_to_two_managers_is_visible_to_both(): void
    {
        $store = Store::factory()->create();
        $managerA = User::factory()->manager($store)->create();
        $managerB = User::factory()->manager($store)->create();

        $trainee = Trainee::factory()->forStore($store)->create();
        $trainee->managers()->attach([$managerA->id, $managerB->id]);

        $this->assertTrue(Trainee::visibleTo($managerA)->get()->contains($trainee));
        $this->assertTrue(Trainee::visibleTo($managerB)->get()->contains($trainee));
    }

    public function test_super_admin_sees_all_trainees_across_stores(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        Trainee::factory()->count(2)->create();
        Trainee::factory()->count(3)->create();

        $this->assertCount(5, Trainee::visibleTo($superAdmin)->get());
    }

    public function test_in_store_scope_filters_by_store(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();

        Trainee::factory()->count(2)->forStore($storeA)->create();
        Trainee::factory()->count(4)->forStore($storeB)->create();

        $this->assertCount(2, Trainee::visibleTo($superAdmin)->inStore($storeA->id)->get());
        $this->assertCount(6, Trainee::visibleTo($superAdmin)->inStore(null)->get());
    }

    public function test_policy_blocks_manager_from_unassigned_trainee(): void
    {
        $store = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();
        $unassigned = Trainee::factory()->forStore($store)->create();

        $this->assertFalse($manager->can('view', $unassigned));
        $this->assertFalse($manager->can('evaluate', $unassigned));

        $assigned = Trainee::factory()->forStore($store)->create();
        $assigned->managers()->attach($manager);

        $this->assertTrue($manager->can('view', $assigned));
        $this->assertTrue($manager->can('evaluate', $assigned));
    }

    public function test_super_admin_can_act_on_any_trainee_via_gate_before(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $trainee = Trainee::factory()->create();

        $this->assertTrue($superAdmin->can('view', $trainee));
        $this->assertTrue($superAdmin->can('evaluate', $trainee));
        $this->assertTrue($superAdmin->can('assignManagers', $trainee));
    }
}

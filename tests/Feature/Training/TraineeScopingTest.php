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

    public function test_manager_sees_all_trainees_in_their_store(): void
    {
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        // Both belong to the manager's store — even the one they never touched.
        $mine = Trainee::factory()->forStore($store)->create();
        $alsoMine = Trainee::factory()->forStore($store)->create();
        $elsewhere = Trainee::factory()->forStore($otherStore)->create();

        $visible = Trainee::visibleTo($manager)->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains($mine));
        $this->assertTrue($visible->contains($alsoMine));
        $this->assertFalse($visible->contains($elsewhere));
    }

    public function test_manager_in_multiple_stores_sees_all_their_stores_trainees(): void
    {
        $storeA = Store::factory()->create();
        $storeB = Store::factory()->create();
        $otherStore = Store::factory()->create();

        $manager = User::factory()->manager($storeA)->create();
        $manager->stores()->attach($storeB->id);

        $inA = Trainee::factory()->forStore($storeA)->create();
        $inB = Trainee::factory()->forStore($storeB)->create();
        $elsewhere = Trainee::factory()->forStore($otherStore)->create();

        $visible = Trainee::visibleTo($manager->fresh())->get();

        $this->assertCount(2, $visible);
        $this->assertTrue($visible->contains($inA));
        $this->assertTrue($visible->contains($inB));
        $this->assertFalse($visible->contains($elsewhere));
    }

    public function test_manager_sees_explicitly_assigned_cross_store_trainee(): void
    {
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        // A trainee in another store, granted via the pivot.
        $crossStore = Trainee::factory()->forStore($otherStore)->create();
        $crossStore->managers()->attach($manager);

        $this->assertTrue(Trainee::visibleTo($manager)->get()->contains($crossStore));
        $this->assertTrue($manager->can('view', $crossStore));
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

    public function test_policy_allows_same_store_and_blocks_other_store(): void
    {
        $store = Store::factory()->create();
        $otherStore = Store::factory()->create();
        $manager = User::factory()->manager($store)->create();

        // Same store — allowed without any explicit assignment.
        $sameStore = Trainee::factory()->forStore($store)->create();
        $this->assertTrue($manager->can('view', $sameStore));
        $this->assertTrue($manager->can('evaluate', $sameStore));

        // Other store — blocked.
        $otherStoreTrainee = Trainee::factory()->forStore($otherStore)->create();
        $this->assertFalse($manager->can('view', $otherStoreTrainee));
        $this->assertFalse($manager->can('evaluate', $otherStoreTrainee));
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

<?php

namespace Tests\Feature\Admin;

use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_managers_cannot_manage_stores(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)->post(route('admin.stores.store'), [
            'name' => 'Sneaky Store',
        ])->assertForbidden();
    }

    public function test_super_admin_can_create_and_update_a_store(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->post(route('admin.stores.store'), [
            'name' => 'Riverside Pizza',
            'address' => '88 River Rd',
        ])->assertSessionHasNoErrors();

        $store = Store::firstWhere('name', 'Riverside Pizza');
        $this->assertNotNull($store);

        $this->actingAs($admin)->put(route('admin.stores.update', $store), [
            'name' => 'Riverside Pizza Co.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Riverside Pizza Co.', $store->refresh()->name);
    }

    public function test_super_admin_can_delete_an_empty_store(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $store = Store::factory()->create();

        $this->actingAs($admin)->delete(route('admin.stores.destroy', $store))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('stores', ['id' => $store->id]);
    }

    public function test_store_with_trainees_cannot_be_deleted(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $store = Store::factory()->create();
        Trainee::factory()->forStore($store)->create();

        $this->actingAs($admin)->delete(route('admin.stores.destroy', $store))
            ->assertSessionHasErrors('store');

        $this->assertDatabaseHas('stores', ['id' => $store->id]);
    }
}

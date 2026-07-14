<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Store;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Global super admin who builds the training program.
        User::factory()->superAdmin()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
        ]);

        // Two stores, each with a manager and a few assigned trainees.
        $stores = Store::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Downtown Pizza'],
                ['name' => 'Lakeside Pizza'],
            )
            ->create();

        $stores->each(function (Store $store, int $index): void {
            $manager = User::factory()->create([
                'name' => "Manager {$store->name}",
                'email' => 'manager'.($index + 1).'@example.com',
                'role' => Role::Manager,
            ]);
            $manager->stores()->attach($store->id);

            Trainee::factory()
                ->count(3)
                ->forStore($store)
                ->create(['created_by' => $manager->id])
                ->each(fn (Trainee $trainee) => $trainee->managers()->attach($manager->id));
        });

        // A regional manager assigned to BOTH stores (exercises multi-store access).
        User::factory()->create([
            'name' => 'Regional Manager',
            'email' => 'regional@example.com',
            'role' => Role::Manager,
        ])->stores()->attach($stores->pluck('id'));

        $this->call(TrainingContentSeeder::class);
    }
}

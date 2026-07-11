<?php

namespace Tests\Feature\Training;

use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandsOnMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_relabels_shifts_as_hours_and_back(): void
    {
        $plural = Section::factory()->create(['hands_on_shifts' => '2 Shifts']);
        $singular = Section::factory()->create(['hands_on_shifts' => '1 Shift']);
        $blank = Section::factory()->create(['hands_on_shifts' => null]);
        $other = Section::factory()->create(['hands_on_shifts' => 'Ongoing']);

        $migration = require glob(database_path('migrations/*_convert_hands_on_shifts_to_hours.php'))[0];
        $migration->up();

        $this->assertSame('2 hours', $plural->refresh()->hands_on_shifts);
        $this->assertSame('1 hour', $singular->refresh()->hands_on_shifts);
        $this->assertNull($blank->refresh()->hands_on_shifts);
        $this->assertSame('Ongoing', $other->refresh()->hands_on_shifts);

        $migration->down();

        $this->assertSame('2 Shifts', $plural->refresh()->hands_on_shifts);
        $this->assertSame('1 Shift', $singular->refresh()->hands_on_shifts);
        $this->assertSame('Ongoing', $other->refresh()->hands_on_shifts);
    }
}

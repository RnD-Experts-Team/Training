<?php

namespace Tests\Feature\Training;

use App\Models\Evaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversion_migration_scales_star_ratings_to_percentages(): void
    {
        $stars = Evaluation::factory()->create(['rating' => 3]);
        $alreadyPercent = Evaluation::factory()->create(['rating' => 80]);
        $unrated = Evaluation::factory()->create(['rating' => null]);

        $path = glob(database_path('migrations/*_convert_evaluation_ratings_to_percentage.php'));
        $migration = require $path[0];
        $migration->up();

        $this->assertSame(60, $stars->refresh()->rating);      // 3 → 60%
        $this->assertSame(80, $alreadyPercent->refresh()->rating); // untouched (> 5)
        $this->assertNull($unrated->refresh()->rating);
    }
}

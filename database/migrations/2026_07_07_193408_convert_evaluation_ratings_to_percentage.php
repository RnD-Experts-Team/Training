<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ratings were stored as 1–5 stars; they are now a 0–100 percentage.
     * Convert existing star values (×20 → 5 = 100%). The `<= 5` guard keeps
     * this safe if the migration is ever re-applied against converted data.
     */
    public function up(): void
    {
        DB::table('evaluations')
            ->whereNotNull('rating')
            ->where('rating', '<=', 5)
            ->update(['rating' => DB::raw('rating * 20')]);
    }

    /**
     * Best-effort reverse: only the clean multiples of 20 that this migration
     * could have produced are converted back to stars.
     */
    public function down(): void
    {
        DB::table('evaluations')
            ->whereNotNull('rating')
            ->where('rating', '>', 5)
            ->whereRaw('rating % 20 = 0')
            ->update(['rating' => DB::raw('rating / 20')]);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hands-on time is now expressed in hours. Existing free-text values used
     * "Shift(s)"; swap the word, keeping the number ("2 Shifts" -> "2 hours",
     * "1 Shift" -> "1 hour"). Replacing "Shift" covers both (the trailing "s"
     * in "Shifts" is preserved).
     */
    public function up(): void
    {
        DB::table('sections')
            ->where('hands_on_shifts', 'like', '%Shift%')
            ->update(['hands_on_shifts' => DB::raw("REPLACE(hands_on_shifts, 'Shift', 'hour')")]);
    }

    public function down(): void
    {
        DB::table('sections')
            ->where('hands_on_shifts', 'like', '%hour%')
            ->update(['hands_on_shifts' => DB::raw("REPLACE(hands_on_shifts, 'hour', 'Shift')")]);
    }
};

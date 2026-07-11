<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes that speed up the reporting rollups: completion-trend queries
     * filter/sort on completed_at, and manager-activity groups by evaluated_by.
     */
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            $table->index('completed_at');
            $table->index('evaluated_by');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table): void {
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['evaluated_by']);
        });
    }
};

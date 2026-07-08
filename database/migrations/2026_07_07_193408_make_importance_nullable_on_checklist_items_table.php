<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            $table->string('importance')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Restore the not-null default: backfill existing nulls first.
        DB::table('checklist_items')->whereNull('importance')->update(['importance' => 'highly_important']);

        Schema::table('checklist_items', function (Blueprint $table) {
            $table->string('importance')->default('highly_important')->nullable(false)->change();
        });
    }
};

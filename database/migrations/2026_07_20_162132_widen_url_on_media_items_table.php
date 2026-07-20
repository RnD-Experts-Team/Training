<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link URLs validate up to 2048 characters but the column was VARCHAR(255),
     * so a long link passed validation and then blew up on insert.
     */
    public function up(): void
    {
        Schema::table('media_items', function (Blueprint $table): void {
            $table->string('url', 2048)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table): void {
            $table->string('url')->nullable()->change();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('checklist_items')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('importance')->default('highly_important');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['category_id', 'order']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};

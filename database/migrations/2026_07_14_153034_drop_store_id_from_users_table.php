<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A manager's single store moves into the `manager_store` pivot. Copy the
     * existing values in, then drop the column.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('store_id')
            ->orderBy('id')
            ->get(['id', 'store_id'])
            ->each(function (object $user): void {
                DB::table('manager_store')->updateOrInsert(
                    ['user_id' => $user->id, 'store_id' => $user->store_id],
                    ['created_at' => now(), 'updated_at' => now()],
                );
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('store_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('store_id')->nullable()->after('role')
                ->constrained()->nullOnDelete();
        });

        // Restore a single store per manager (the first assigned).
        DB::table('manager_store')
            ->orderBy('user_id')
            ->orderBy('store_id')
            ->get(['user_id', 'store_id'])
            ->groupBy('user_id')
            ->each(function ($rows, int $userId): void {
                DB::table('users')->where('id', $userId)->update([
                    'store_id' => $rows->first()->store_id,
                ]);
            });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the block_user pivot table (many-to-many)
        Schema::create('block_user', function (Blueprint $table) {
            $table->uuid('block_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->primary(['block_id', 'user_id']);
            $table->foreign('block_id')->references('id')->on('blocks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 2. Migrate existing block_id → pivot rows before dropping the column
        DB::table('users')
            ->whereNotNull('block_id')
            ->get(['id', 'block_id'])
            ->each(function ($user) {
                // Only insert if the block still exists
                $exists = DB::table('blocks')->where('id', $user->block_id)->exists();
                if ($exists) {
                    DB::table('block_user')->insertOrIgnore([
                        'block_id'   => $user->block_id,
                        'user_id'    => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        // 3. Drop the old block_id column from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['block_id']);
            $table->dropColumn('block_id');
        });
    }

    public function down(): void
    {
        // Re-add block_id (best-effort, data loss expected)
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('block_id')->nullable()->after('role_id');
            $table->foreign('block_id')->references('id')->on('blocks')->onDelete('set null');
        });

        Schema::dropIfExists('block_user');
    }
};

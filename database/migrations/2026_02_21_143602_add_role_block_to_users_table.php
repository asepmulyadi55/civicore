<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role — references the configurable roles table (default = 4 = resident)
            $table->foreignId('role_id')
                ->nullable()
                ->after('is_active')
                ->constrained('roles')
                ->nullOnDelete();

            // Block — only populated for block_coordinator role
            $table->foreignId('block_id')
                ->nullable()
                ->after('role_id')
                ->constrained('blocks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['block_id']);
            $table->dropColumn(['role_id', 'block_id']);
        });
    }
};

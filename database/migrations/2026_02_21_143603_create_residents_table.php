<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('residents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Optional link to a system user account
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('block_id')
                ->constrained('blocks');

            $table->string('unit_number');             // e.g. A-101, B-203
            $table->string('fullname');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // A unit number must be unique within a block
            $table->unique(['block_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};

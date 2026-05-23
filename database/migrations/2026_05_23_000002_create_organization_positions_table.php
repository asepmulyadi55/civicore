<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_period_id')
                  ->constrained('organization_periods')
                  ->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->foreignUuid('resident_id')
                  ->nullable()
                  ->constrained('residents')
                  ->nullOnDelete();
            $table->foreignUuid('family_member_id')
                  ->nullable()
                  ->constrained('family_members')
                  ->nullOnDelete();
            $table->string('position_name', 100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            // Self-referential FK: if parent is deleted, children become root (parent_id = null)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('organization_positions')
                  ->nullOnDelete();

            $table->index('parent_id');
            $table->index(['organization_period_id', 'sort_order']);
            $table->index(['organization_period_id', 'resident_id']);
            $table->index(['organization_period_id', 'family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_positions');
    }
};

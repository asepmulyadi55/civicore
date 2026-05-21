<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fee_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('resident_id')->constrained('residents')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('effective_from');            // Fee applies from this month onward
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_histories');
    }
};

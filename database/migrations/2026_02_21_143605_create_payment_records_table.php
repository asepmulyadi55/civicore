<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('resident_id')->constrained('residents')->cascadeOnDelete();

            // Stored as first day of month: 2024-01-01, 2024-02-01, etc.
            $table->date('payment_month');

            // Snapshot of the fee amount at time of payment (immutable)
            $table->decimal('amount', 12, 2);

            $table->foreignUuid('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();

            // File path to uploaded proof (receipt photo)
            $table->string('proof_path')->nullable();

            // Payment lifecycle status
            $table->enum('status', ['unpaid', 'pending', 'approved', 'rejected'])
                ->default('unpaid');

            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();

            // Audit trail
            $table->foreignUuid('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // One payment record per resident per month
            $table->unique(['resident_id', 'payment_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};

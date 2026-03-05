<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing indexes to high-traffic columns.
 * Improves query performance for the payment index, report, and dashboard pages.
 */
return new class extends Migration {
  public function up(): void
  {
    Schema::table('payment_records', function (Blueprint $table) {
      $table->index('status');                          // Filtered on every payment query
      $table->index('payment_month');                  // Year/month date filters
      $table->index(['resident_id', 'status']);        // Composite: look up a resident's pending/approved payments
    });

    Schema::table('residents', function (Blueprint $table) {
      $table->index('email');                          // Auto-link lookup by email
      $table->index('is_active');                      // Active resident filters
    });

    Schema::table('fee_histories', function (Blueprint $table) {
      $table->index(['resident_id', 'effective_from']); // currentFee() query
    });
  }

  public function down(): void
  {
    Schema::table('payment_records', function (Blueprint $table) {
      $table->dropIndex(['status']);
      $table->dropIndex(['payment_month']);
      $table->dropIndex(['resident_id', 'status']);
    });

    Schema::table('residents', function (Blueprint $table) {
      $table->dropIndex(['email']);
      $table->dropIndex(['is_active']);
    });

    Schema::table('fee_histories', function (Blueprint $table) {
      $table->dropIndex(['resident_id', 'effective_from']);
    });
  }
};

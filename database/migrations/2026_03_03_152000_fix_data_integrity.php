<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix data integrity: fee_histories.created_by should be nullable + nullOnDelete
 * so that deleting a user who created fee histories doesn't throw a FK constraint error.
 *
 * Note: settings.key already has a UNIQUE constraint from the original migration.
 */
return new class extends Migration {
  public function up(): void
  {
    Schema::table('fee_histories', function (Blueprint $table) {
      // Drop old FK
      $table->dropForeign(['created_by']);
      // Re-add as nullable char(36) UUID with nullOnDelete
      $table->char('created_by', 36)->nullable()->change();
      $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::table('fee_histories', function (Blueprint $table) {
      $table->dropForeign(['created_by']);
      $table->char('created_by', 36)->nullable(false)->change();
      $table->foreign('created_by')->references('id')->on('users');
    });
  }
};

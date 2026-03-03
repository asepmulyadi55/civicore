<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      // Single-session enforcement — stores the active session ID
      $table->string('session_token', 100)->nullable()->after('language');

      // Activity tracking
      $table->timestamp('last_login_at')->nullable()->after('session_token');
      $table->timestamp('last_active_at')->nullable()->after('last_login_at');
    });
  }

  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn(['session_token', 'last_login_at', 'last_active_at']);
    });
  }
};

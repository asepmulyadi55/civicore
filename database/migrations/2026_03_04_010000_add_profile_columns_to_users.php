<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('avatar')->nullable()->after('name');          // relative path under storage/app/public
      $table->string('language', 5)->default('en')->after('avatar'); // 'en' | 'id'
    });
  }

  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn(['avatar', 'language']);
    });
  }
};

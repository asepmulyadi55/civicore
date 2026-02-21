<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();           // e.g. app_name, support_email, default_due_day
            $table->text('value')->nullable();
            $table->string('label');                   // Human-readable name shown in Settings UI
            $table->string('group')->default('general'); // general, payment, notification, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

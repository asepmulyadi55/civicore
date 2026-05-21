<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // varchar(20) is too small for AES-256-CBC encrypted payloads (~100+ chars)
        Schema::table('family_members', function (Blueprint $table) {
            $table->text('nik')->nullable()->change();
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->text('family_card_number')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('family_members', function (Blueprint $table) {
            $table->string('nik', 20)->nullable()->change();
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->string('family_card_number', 20)->nullable()->change();
        });
    }
};

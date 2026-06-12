<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL ALTER TABLE to add 'developer' to ENUM
        DB::statement("ALTER TABLE units MODIFY house_status ENUM('owner_occupied', 'vacant', 'rented', 'public_facility', 'developer') DEFAULT 'owner_occupied'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert any 'developer' back to 'vacant' before removing the enum option
        DB::statement("UPDATE units SET house_status = 'vacant' WHERE house_status = 'developer'");
        DB::statement("ALTER TABLE units MODIFY house_status ENUM('owner_occupied', 'vacant', 'rented', 'public_facility') DEFAULT 'owner_occupied'");
    }
};

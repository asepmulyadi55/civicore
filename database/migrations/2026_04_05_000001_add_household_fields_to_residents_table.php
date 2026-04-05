<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->string('family_card_number', 20)->nullable()->after('email');
            $table->enum('house_status', ['owner_occupied', 'vacant', 'rented'])
                ->default('owner_occupied')->after('family_card_number');
            $table->text('notes')->nullable()->after('house_status');
        });
    }

    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['family_card_number', 'house_status', 'notes']);
        });
    }
};

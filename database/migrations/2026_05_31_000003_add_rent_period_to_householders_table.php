<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('householders', function (Blueprint $table) {
            $table->date('rent_start')->nullable()->after('notes');
            $table->date('rent_end')->nullable()->after('rent_start');
        });
    }

    public function down(): void
    {
        Schema::table('householders', function (Blueprint $table) {
            $table->dropColumn(['rent_start', 'rent_end']);
        });
    }
};

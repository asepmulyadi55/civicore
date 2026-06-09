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
        Schema::table('residents', function (Blueprint $table) {
            // Change column type to text to support Eloquent's encrypted casting (which is long)
            $table->text('birth_date')->nullable()->change();
            $table->text('nik')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->change();
            $table->string('nik', 20)->nullable()->change();
        });
    }
};

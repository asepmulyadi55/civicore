<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Material Icon name, e.g. 'security', 'account_balance'
            $table->string('icon')->default('person')->after('description');
            // Tailwind bg classes, e.g. 'bg-purple-100 dark:bg-purple-500/10'
            $table->string('bg_class')->default('bg-slate-100 dark:bg-slate-700')->after('icon');
            // Tailwind text classes, e.g. 'text-purple-600'
            $table->string('text_class')->default('text-slate-500')->after('bg_class');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['icon', 'bg_class', 'text_class']);
        });
    }
};

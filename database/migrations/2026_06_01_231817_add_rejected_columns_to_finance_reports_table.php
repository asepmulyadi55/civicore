<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the status ENUM to include 'rejected'
        DB::statement("ALTER TABLE finance_reports MODIFY COLUMN status ENUM('draft','submitted','approved','revised','rejected') NOT NULL DEFAULT 'draft'");

        Schema::table('finance_reports', function (Blueprint $table) {
            $table->string('rejected_by')->nullable()->after('revised_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_notes')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('finance_reports', function (Blueprint $table) {
            $table->dropColumn(['rejected_by', 'rejected_at', 'rejection_notes']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'rejected' to the finance_reports status ENUM.
     * This is a standalone migration so it runs correctly on VPS
     * even if the previous add_rejected_columns migration already ran.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE finance_reports
             MODIFY COLUMN status
             ENUM('draft','submitted','approved','revised','rejected')
             NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        // Only revert if no rows currently use 'rejected'
        $hasRejected = DB::table('finance_reports')
            ->where('status', 'rejected')
            ->exists();

        if (!$hasRejected) {
            DB::statement(
                "ALTER TABLE finance_reports
                 MODIFY COLUMN status
                 ENUM('draft','submitted','approved','revised')
                 NOT NULL DEFAULT 'draft'"
            );
        }
    }
};

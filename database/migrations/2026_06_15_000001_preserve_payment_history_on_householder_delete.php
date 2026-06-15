<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Change payment_records and fee_histories FK from CASCADE to SET NULL.
 *
 * Previously, deleting a householder cascaded and wiped all their payment
 * records and fee history. Now the householder_id is set to NULL when the
 * householder is deleted, preserving the records as historical documentation.
 *
 * Also stores a snapshot of the householder's name so reports/payments still
 * show who the record belonged to even after deletion.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add a nullable name snapshot column to payment_records ──────────
        Schema::table('payment_records', function (Blueprint $table) {
            $table->string('householder_name')->nullable()->after('householder_id');
        });

        // ── 2. Backfill existing rows with the householder name ────────────────
        DB::statement('
            UPDATE payment_records pr
            JOIN householders h ON h.id = pr.householder_id
            SET pr.householder_name = h.fullname
        ');

        // ── 3. Drop cascade FK on payment_records and make column nullable ─────
        // MySQL requires dropping the FK constraint before altering nullability.
        $this->dropForeignIfExists('payment_records', 'payment_records_householder_id_foreign');
        $this->dropForeignIfExists('payment_records', 'payment_records_resident_id_foreign');

        Schema::table('payment_records', function (Blueprint $table) {
            $table->uuid('householder_id')->nullable()->change();
            $table->foreign('householder_id')
                ->references('id')
                ->on('householders')
                ->nullOnDelete();
        });

        // ── 4. Add a nullable name snapshot column to fee_histories ───────────
        Schema::table('fee_histories', function (Blueprint $table) {
            $table->string('householder_name')->nullable()->after('householder_id');
        });

        // ── 5. Backfill fee_histories ──────────────────────────────────────────
        DB::statement('
            UPDATE fee_histories fh
            JOIN householders h ON h.id = fh.householder_id
            SET fh.householder_name = h.fullname
        ');

        // ── 6. Drop cascade FK on fee_histories and make column nullable ───────
        $this->dropForeignIfExists('fee_histories', 'fee_histories_householder_id_foreign');
        $this->dropForeignIfExists('fee_histories', 'fee_histories_resident_id_foreign');

        Schema::table('fee_histories', function (Blueprint $table) {
            $table->uuid('householder_id')->nullable()->change();
            $table->foreign('householder_id')
                ->references('id')
                ->on('householders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Restore cascade on payment_records
        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropForeign(['householder_id']);
            $table->dropColumn('householder_name');
        });
        Schema::table('payment_records', function (Blueprint $table) {
            $table->uuid('householder_id')->nullable(false)->change();
            $table->foreign('householder_id')
                ->references('id')
                ->on('householders')
                ->cascadeOnDelete();
        });

        // Restore cascade on fee_histories
        Schema::table('fee_histories', function (Blueprint $table) {
            $table->dropForeign(['householder_id']);
            $table->dropColumn('householder_name');
        });
        Schema::table('fee_histories', function (Blueprint $table) {
            $table->uuid('householder_id')->nullable(false)->change();
            $table->foreign('householder_id')
                ->references('id')
                ->on('householders')
                ->cascadeOnDelete();
        });
    }

    /**
     * Try to drop a named FK constraint, silently skipping if it doesn't exist.
     */
    private function dropForeignIfExists(string $table, string $constraintName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($constraintName) {
                $t->dropForeign($constraintName);
            });
        } catch (\Throwable $e) {
            // Constraint may already have a different name (e.g. after rename migration)
            // Try the column-based drop as a fallback
            try {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['householder_id']);
                });
            } catch (\Throwable) {
                // Nothing more to do
            }
        }
    }
};

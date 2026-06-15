<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->foreignUuid('block_id')->nullable()->after('householder_id')->constrained('blocks')->nullOnDelete();
            $table->string('unit_number')->nullable()->after('householder_name');
        });

        // Backfill block_id and unit_number from householders and units
        DB::statement('
            UPDATE payment_records pr
            JOIN householders h ON h.id = pr.householder_id
            LEFT JOIN units u ON u.id = h.unit_id
            SET 
                pr.block_id = h.block_id,
                pr.unit_number = u.unit_number
        ');
    }

    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropForeign(['block_id']);
            $table->dropColumn(['block_id', 'unit_number']);
        });
    }
};

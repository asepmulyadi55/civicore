<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        // 1 — Add nullable unit_id FK to residents (skip if already added by a partial run)
        if (!Schema::hasColumn('residents', 'unit_id')) {
            Schema::table('residents', function (Blueprint $table) {
                $table->foreignUuid('unit_id')
                    ->nullable()
                    ->after('block_id')
                    ->constrained('units')
                    ->nullOnDelete();
            });
        }

        // 2 — Migrate existing data: create a unit row for every resident that still has unit_number
        if (Schema::hasColumn('residents', 'unit_number')) {
            $residents = DB::table('residents')->get();
            foreach ($residents as $resident) {
                if (empty($resident->unit_number)) {
                    continue;
                }
                // Check if a unit already exists for this block + unit_number combo
                $existing = DB::table('units')
                    ->where('block_id', $resident->block_id)
                    ->where('unit_number', $resident->unit_number)
                    ->first();

                if ($existing) {
                    $unitId = $existing->id;
                } else {
                    $unitId = Str::uuid()->toString();
                    DB::table('units')->insert([
                        'id'           => $unitId,
                        'block_id'     => $resident->block_id,
                        'unit_number'  => $resident->unit_number,
                        'house_status' => $resident->house_status ?? 'owner_occupied',
                        'is_active'    => true,
                        'notes'        => null,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                DB::table('residents')
                    ->where('id', $resident->id)
                    ->update(['unit_id' => $unitId]);
            }
        }

        // 3 — Drop the old columns from residents (only if they still exist)
        if (Schema::hasColumn('residents', 'unit_number')) {
            // MySQL uses (block_id, unit_number) composite unique index as the backing index
            // for the block_id FK (since block_id is leftmost). We must add a plain block_id
            // index first so MySQL can transfer the FK, then drop the composite unique index.
            $indexes = DB::select(
                "SHOW INDEX FROM `residents` WHERE Key_name = 'residents_block_id_idx'"
            );
            if (empty($indexes)) {
                Schema::table('residents', function (Blueprint $table) {
                    $table->index('block_id', 'residents_block_id_idx');
                });
            }

            // Temporarily drop unit_id FK so we can drop the composite unique index freely
            $unitIdFkExists = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'residents'
                 AND CONSTRAINT_NAME = 'residents_unit_id_foreign'"
            );
            if (!empty($unitIdFkExists)) {
                Schema::table('residents', function (Blueprint $table) {
                    $table->dropForeign(['unit_id']);
                });
            }

            Schema::table('residents', function (Blueprint $table) {
                $table->dropUnique(['block_id', 'unit_number']);
                $table->dropColumn(['unit_number', 'house_status']);
            });

            // Re-add unit_id FK now that the conflicting index is removed
            Schema::table('residents', function (Blueprint $table) {
                $table->foreign('unit_id')
                    ->references('id')
                    ->on('units')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Restore old columns
        Schema::table('residents', function (Blueprint $table) {
            $table->string('unit_number')->nullable()->after('block_id');
            $table->enum('house_status', ['owner_occupied', 'vacant', 'rented'])
                ->default('owner_occupied')->after('unit_number');
        });

        // Restore data from units table
        $residents = DB::table('residents')->whereNotNull('unit_id')->get();
        foreach ($residents as $resident) {
            $unit = DB::table('units')->find($resident->unit_id);
            if ($unit) {
                DB::table('residents')->where('id', $resident->id)->update([
                    'unit_number'  => $unit->unit_number,
                    'house_status' => $unit->house_status,
                ]);
            }
        }

        // Re-add unique constraint
        Schema::table('residents', function (Blueprint $table) {
            $table->unique(['block_id', 'unit_number']);
            $table->dropForeign(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};

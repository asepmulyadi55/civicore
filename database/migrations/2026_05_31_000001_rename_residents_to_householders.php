<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Rename residents → householders ───────────────────────────────
        Schema::rename('residents', 'householders');

        // ── 2. Rename family_members → residents ────────────────────────────
        Schema::rename('family_members', 'residents');

        // ── 3. Rename resident_id → householder_id in the new residents table
        Schema::table('residents', function (Blueprint $table) {
            $table->renameColumn('resident_id', 'householder_id');
        });

        // ── 4. Rename resident_id → householder_id in payment_records ────────
        Schema::table('payment_records', function (Blueprint $table) {
            $table->renameColumn('resident_id', 'householder_id');
        });

        // ── 5. Rename resident_id → householder_id in fee_histories ──────────
        Schema::table('fee_histories', function (Blueprint $table) {
            $table->renameColumn('resident_id', 'householder_id');
        });

        // ── 6. Rename resident_id → householder_id and
        //           family_member_id → resident_id in organization_positions ──
        Schema::table('organization_positions', function (Blueprint $table) {
            $table->renameColumn('resident_id', 'householder_id');
            $table->renameColumn('family_member_id', 'resident_id');
        });

        // ── 7. Rename resident_id → householder_id in media_files (if exists)
        if (Schema::hasColumn('media_files', 'resident_id')) {
            Schema::table('media_files', function (Blueprint $table) {
                $table->renameColumn('resident_id', 'householder_id');
            });
        }

        // ── 8. Update role name: resident → householder ───────────────────────
        DB::table('roles')->where('name', 'resident')->update(['name' => 'householder']);

        // ── 9. Update permission keys: residents.* → householders.* ──────────
        DB::table('roles')->get()->each(function ($role) {
            $permissions = json_decode($role->permissions, true) ?? [];
            if (empty($permissions)) return;

            $updated = [];
            foreach ($permissions as $key => $value) {
                $newKey = str_replace('residents.', 'householders.', $key);
                $updated[$newKey] = $value;
            }
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($updated)]);
        });
    }

    public function down(): void
    {
        // ── Reverse permission key rename ──────────────────────────────────────
        DB::table('roles')->get()->each(function ($role) {
            $permissions = json_decode($role->permissions, true) ?? [];
            if (empty($permissions)) return;

            $updated = [];
            foreach ($permissions as $key => $value) {
                $newKey = str_replace('householders.', 'residents.', $key);
                $updated[$newKey] = $value;
            }
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($updated)]);
        });

        // ── Reverse role name rename ──────────────────────────────────────────
        DB::table('roles')->where('name', 'householder')->update(['name' => 'resident']);

        // ── Reverse column renames ────────────────────────────────────────────
        if (Schema::hasColumn('media_files', 'householder_id')) {
            Schema::table('media_files', function (Blueprint $table) {
                $table->renameColumn('householder_id', 'resident_id');
            });
        }

        Schema::table('organization_positions', function (Blueprint $table) {
            $table->renameColumn('resident_id', 'family_member_id');
            $table->renameColumn('householder_id', 'resident_id');
        });

        Schema::table('fee_histories', function (Blueprint $table) {
            $table->renameColumn('householder_id', 'resident_id');
        });

        Schema::table('payment_records', function (Blueprint $table) {
            $table->renameColumn('householder_id', 'resident_id');
        });

        Schema::table('residents', function (Blueprint $table) {
            $table->renameColumn('householder_id', 'resident_id');
        });

        // ── Reverse table renames ─────────────────────────────────────────────
        Schema::rename('residents', 'family_members');
        Schema::rename('householders', 'residents');
    }
};

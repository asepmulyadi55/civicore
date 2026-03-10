<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('text_class');
        });

        // Seed default permissions for existing roles
        $defaults = [
            'treasurer' => [
                'dashboard.view' => true,
                'residents.view' => true,
                'blocks.view' => true,
                'payments.view' => true,
                'payments.approve' => true,
                'reports.view' => true,
            ],
            'block_coordinator' => [
                'dashboard.view' => true,
                'residents.view' => true,
                'blocks.view' => true,
                'payments.view' => true,
                'payments.create' => true,
                'payments.edit' => true,
            ],
            'resident' => [
                'dashboard.view' => true,
            ],
        ];

        foreach ($defaults as $roleName => $permissions) {
            DB::table('roles')->where('name', $roleName)->update([
                'permissions' => json_encode($permissions),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};

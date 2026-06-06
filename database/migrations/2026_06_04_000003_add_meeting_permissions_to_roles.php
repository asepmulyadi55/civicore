<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add meetings permissions to all existing roles.
     * Admin role gets all permissions automatically via hasPermission().
     * Other roles start with no meetings permissions (admin can grant via UI).
     */
    public function up(): void
    {
        // No-op: the meetings permission keys are registered in Role::$availablePermissions.
        // The Roles UI will automatically expose the new checkboxes.
        // Admin role bypasses the permission check entirely (name === 'admin').
        // Non-admin roles have no meetings permissions by default — grant via the Roles page.
    }

    public function down(): void
    {
        // Remove meetings.* from every non-admin role's permissions JSON
        DB::table('roles')->where('name', '!=', 'admin')->get()->each(function ($role) {
            $perms = json_decode($role->permissions ?? '{}', true) ?: [];
            foreach (['meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete'] as $key) {
                unset($perms[$key]);
            }
            DB::table('roles')->where('id', $role->id)->update(['permissions' => json_encode($perms)]);
        });
    }
};

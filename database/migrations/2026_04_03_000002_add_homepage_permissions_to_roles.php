<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Grant homepage.view + homepage.edit to all existing non-admin roles so that
     * existing users retain access to the Homepage CMS after it is added to the
     * permission matrix.  Admins always have full access via Role::hasPermission().
     */
    public function up(): void
    {
        $roles = DB::table('roles')->where('name', '!=', 'admin')->get();

        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions ?? '{}', true) ?? [];

            // Grant homepage view/edit; leave create/delete unset (admin can toggle via UI)
            $permissions['homepage.view'] = true;
            $permissions['homepage.edit'] = true;

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['permissions' => json_encode($permissions)]);
        }
    }

    public function down(): void
    {
        $roles = DB::table('roles')->where('name', '!=', 'admin')->get();

        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions ?? '{}', true) ?? [];
            unset($permissions['homepage.view'], $permissions['homepage.edit'],
                  $permissions['homepage.create'], $permissions['homepage.delete'],
                  $permissions['media.view'], $permissions['media.delete']);

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['permissions' => json_encode($permissions)]);
        }
    }
};

<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ── Treasurer ────────────────────────────────────────────────────────
        // Can view most modules; full access to payments including approve.
        Role::where('name', 'treasurer')->update([
            'permissions' => [
                'dashboard.view'    => true,
                'residents.view'    => true,
                'blocks.view'       => true,
                'payments.view'     => true,
                'payments.create'   => true,
                'payments.edit'     => true,
                'payments.delete'   => true,
                'payments.approve'  => true,
                'reports.view'      => true,
            ],
        ]);

        // ── Block Coordinator ─────────────────────────────────────────────────
        // Can view residents, manage payments (no approve), view reports.
        Role::where('name', 'block_coordinator')->update([
            'permissions' => [
                'dashboard.view'    => true,
                'residents.view'    => true,
                'payments.view'     => true,
                'payments.create'   => true,
                'payments.edit'     => true,
                'payments.delete'   => true,
                'reports.view'      => true,
            ],
        ]);

        // ── Resident ──────────────────────────────────────────────────────────
        // Overview only — no admin modules.
        Role::where('name', 'resident')->update([
            'permissions' => [
                'overview.view' => true,
            ],
        ]);

        // ── Posyandu ──────────────────────────────────────────────────────────
        // Overview + community health data.
        Role::where('name', 'posyandu')->update([
            'permissions' => [
                'overview.view' => true,
                'posyandu.view' => true,
            ],
        ]);
    }
}

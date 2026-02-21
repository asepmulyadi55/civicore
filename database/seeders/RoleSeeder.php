<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  public function run(): void
  {
    $roles = [
      ['name' => 'admin', 'label' => 'Admin', 'description' => 'Full system access. Also acts as Treasurer.'],
      ['name' => 'treasurer', 'label' => 'Treasurer', 'description' => 'Reviews and approves/rejects payment submissions.'],
      ['name' => 'block_coordinator', 'label' => 'Block Coordinator', 'description' => 'Inputs payments for residents in their assigned block.'],
      ['name' => 'resident', 'label' => 'Resident', 'description' => 'Read-only access to personal payment history.'],
    ];

    foreach ($roles as $role) {
      Role::firstOrCreate(['name' => $role['name']], $role);
    }
  }
}

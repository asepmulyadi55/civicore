<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
  public function run(): void
  {
    $roles = [
      [
        'name' => 'admin',
        'label' => 'Admin',
        'description' => 'Full system access. Also acts as Treasurer.',
        'icon' => 'security',
        'bg_class' => 'bg-purple-100 dark:bg-purple-500/10',
        'text_class' => 'text-purple-600',
      ],
      [
        'name' => 'treasurer',
        'label' => 'Treasurer',
        'description' => 'Reviews and approves/rejects payment submissions.',
        'icon' => 'account_balance',
        'bg_class' => 'bg-amber-100 dark:bg-amber-500/10',
        'text_class' => 'text-amber-600',
      ],
      [
        'name' => 'block_coordinator',
        'label' => 'Block Coordinator',
        'description' => 'Inputs payments for residents in their assigned block.',
        'icon' => 'supervised_user_circle',
        'bg_class' => 'bg-indigo-100 dark:bg-indigo-500/10',
        'text_class' => 'text-indigo-600',
      ],
      [
        'name' => 'resident',
        'label' => 'Resident',
        'description' => 'Read-only access to personal payment history.',
        'icon' => 'person',
        'bg_class' => 'bg-sky-100 dark:bg-sky-500/10',
        'text_class' => 'text-sky-600',
      ],
    ];

    foreach ($roles as $role) {
      Role::updateOrCreate(
        ['name' => $role['name']],
        $role
      );
    }
  }
}

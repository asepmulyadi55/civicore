<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  use WithoutModelEvents;

  public function run(): void
  {
    $this->call([
      // Core lookups (no dependencies)
      RoleSeeder::class,
      RolePermissionSeeder::class,
      PaymentMethodSeeder::class,
      SettingSeeder::class,

      // Admin user (depends on roles)
      UserSeeder::class,
    ]);
  }
}

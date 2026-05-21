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
        // Lookup tables first (no dependencies)
      RoleSeeder::class,
      RolePermissionSeeder::class,
      BlockSeeder::class,
      PaymentMethodSeeder::class,
      SettingSeeder::class,

        // Users depend on roles + blocks
      UserSeeder::class,

        // Residents depend on blocks + users; creates fee histories inline
      ResidentSeeder::class,

        // Payment records depend on residents, payment methods, users
      PaymentRecordSeeder::class,
    ]);
  }
}

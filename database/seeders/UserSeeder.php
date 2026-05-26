<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    $adminRole = Role::where('name', 'admin')->first();
    $treasurerRole = Role::where('name', 'treasurer')->first();
    $coordinatorRole = Role::where('name', 'block_coordinator')->first();
    $residentRole = Role::where('name', 'resident')->first();
    $posyanduRole = Role::where('name', 'posyandu')->first();

    $blockA = Block::where('name', 'Block A')->first();

    // Admin
    User::firstOrCreate(['email' => 'admin@civicore.test'], [
      'name' => 'Super Admin',
      'username' => 'admin',
      'password' => Hash::make('password'),
      'is_active' => true,
      'role_id' => $adminRole?->id,
    ]);

    // Treasurer
    User::firstOrCreate(['email' => 'treasurer@civicore.test'], [
      'name' => 'Budi Santoso',
      'username' => 'treasurer',
      'password' => Hash::make('password'),
      'is_active' => true,
      'role_id' => $treasurerRole?->id,
    ]);

    // Block Coordinator (assigned to Block A)
    User::firstOrCreate(['email' => 'coordinator@civicore.test'], [
      'name' => 'Dewi Rahayu',
      'username' => 'coordinator',
      'password' => Hash::make('password'),
      'is_active' => true,
      'role_id' => $coordinatorRole?->id,
      'block_id' => $blockA?->id,
    ]);

    // Resident
    User::firstOrCreate(['email' => 'resident@civicore.test'], [
      'name' => 'Julian Rivera',
      'username' => 'resident',
      'password' => Hash::make('password'),
      'is_active' => true,
      'role_id' => $residentRole?->id,
    ]);

    // Posyandu officer
    User::firstOrCreate(['email' => 'posyandu@civicore.test'], [
      'name' => 'Siti Rahayu',
      'username' => 'posyandu',
      'password' => Hash::make('password'),
      'is_active' => true,
      'role_id' => $posyanduRole?->id,
    ]);
  }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@civicore.com',
            'password' => Hash::make('admin123'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}

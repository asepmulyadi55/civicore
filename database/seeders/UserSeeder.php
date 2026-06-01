<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the initial super-admin account.
 *
 * Credentials are read from environment variables so you can set them
 * before deploying without editing any code:
 *
 *   ADMIN_NAME="Your Name"
 *   ADMIN_EMAIL="admin@yourdomain.com"
 *   ADMIN_USERNAME="admin"
 *   ADMIN_PASSWORD="ChangeMe123!"
 *
 * If the variables are not set, sensible defaults are used and the
 * generated password is printed to the console.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $name     = env('ADMIN_NAME',     'Super Admin');
        $email    = env('ADMIN_EMAIL',    'admin@civicore.local');
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD');

        // If no password set in .env, generate a random one and show it once.
        $generated = false;
        if (empty($password)) {
            $password  = \Illuminate\Support\Str::password(16);
            $generated = true;
        }

        User::updateOrCreate(
            ['username' => $username],
            [
                'name'      => $name,
                'email'     => $email,
                'password'  => Hash::make($password),
                'is_active' => true,
                'role_id'   => $adminRole?->id,
            ]
        );

        // Always show credentials after seeding so they aren't lost.
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║         Admin Account Created            ║');
        $this->command->info('╠══════════════════════════════════════════╣');
        $this->command->info("║  Name     : {$name}");
        $this->command->info("║  Email    : {$email}");
        $this->command->info("║  Username : {$username}");
        if ($generated) {
            $this->command->warn("║  Password : {$password}  ← SAVE THIS NOW");
            $this->command->warn('║  (set ADMIN_PASSWORD in .env to choose your own)');
        } else {
            $this->command->info("║  Password : (set via ADMIN_PASSWORD in .env)");
        }
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->warn('  ⚠  Change the password after first login!');
        $this->command->info('');
    }
}

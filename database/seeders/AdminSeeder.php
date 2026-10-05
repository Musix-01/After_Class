<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Creates (or resets) the admin account.
     *
     *   php artisan db:seed --class=AdminSeeder
     *
     * Default login:  admin@afterclass.test  /  ChangeMe!2026
     * Override in .env with ADMIN_EMAIL / ADMIN_PASSWORD, and change the password after first login.
     */
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@afterclass.test');
        $password = env('ADMIN_PASSWORD', 'ChangeMe!2026');

        $admin = User::firstOrNew(['email' => $email]);

        $admin->forceFill([
            'name'            => $admin->name ?: 'After Class Admin',
            'password'        => Hash::make($password),
            'role'            => 'admin',
            'suspended_at'    => null,
            'suspended_until' => null,
        ])->save();

        $this->command?->info("Admin ready: {$email}");
    }
}

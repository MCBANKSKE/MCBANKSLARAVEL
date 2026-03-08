<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'admin',
            'host',
            'guest'
            
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create admin user only if credentials are provided
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminName = env('ADMIN_NAME', 'Administrator');

        if ($adminEmail && $adminPassword) {
            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'is_superadmin' => true,
                    'email_verified_at' => now(),
                ]
            );

            // Assign admin role
            $admin->assignRole('admin');

            $this->command->info('✅ Admin user created successfully!');
            $this->command->info('📧 Email: ' . $adminEmail);
            $this->command->info('👤 Role: admin');
        } else {
            $this->command->warn('⚠️  Admin credentials not provided in environment. Skipping admin user creation.');
            $this->command->info('To create admin user, set these environment variables:');
            $this->command->info('  ADMIN_EMAIL=mcbankske@gmail.com');
            $this->command->info('  ADMIN_PASSWORD=password');
            $this->command->info('  ADMIN_NAME=Mark Clinton');
        }
    }
}

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
            //add as many roles as you want
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'youremail@example.com'],
            [
                'name' => 'Your Name',
                'email' => 'youremail@example.com',
                'password' => Hash::make('password'),
                'is_superadmin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $admin->assignRole('admin');

        $this->command->info('✅ Admin user created successfully!');
        $this->command->info('📧 Email: youremail@example.com');
        $this->command->info('🔑 Password: password');
        $this->command->info('👤 Role: admin');
    }
}

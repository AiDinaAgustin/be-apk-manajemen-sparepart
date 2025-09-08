<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            // User Management
            'user.create',
            'user.read',
            'user.update',
            'user.delete',
            'user.set_role_permission',

            // Sparepart Management
            'sparepart.create',
            'sparepart.read',
            'sparepart.update',
            'sparepart.delete',

            // Transaction Management
            'transaction.read_all',
            'transaction.read_detail',
            'transaction.approve',
            'transaction.reject',

            // Reporting
            'report.read',
            'report.export',

            // Staff permissions
            'sparepart.read_list',
            'transaction.create',
            'transaction.read_own',
            'transaction.update_own',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff']);

        // Assign permissions to roles
        $adminRole->syncPermissions([
            'user.create',
            'user.read',
            'user.update',
            'user.delete',
            'user.set_role_permission',
            'sparepart.create',
            'sparepart.read',
            'sparepart.update',
            'sparepart.delete',
            'transaction.read_all',
            'transaction.read_detail',
            'transaction.approve',
            'transaction.reject',
            'report.read',
            'report.export',
        ]);

        $staffRole->syncPermissions([
            'sparepart.read',
            'sparepart.read_list',
            'transaction.create',
            'transaction.read_own',
            'transaction.update_own',
        ]);

        // Create example users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        $admin->assignRole($adminRole);

        $staff = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff', 'password' => bcrypt('password')]
        );
        $staff->assignRole($staffRole);
    }
}
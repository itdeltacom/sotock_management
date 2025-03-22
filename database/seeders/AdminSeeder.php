<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'manage admins',
            'view admins',
            'create admins',
            'edit admins',
            'delete admins',
            'manage roles',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'manage settings',
            'access dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::create(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdminRole->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'admin']);
        $adminRole->givePermissionTo([
            'access dashboard',
            'view admins',
            'view roles',
            'manage settings',
        ]);

        $managerRole = Role::create(['name' => 'Manager', 'guard_name' => 'admin']);
        $managerRole->givePermissionTo([
            'access dashboard',
            'view admins',
        ]);

        // Create super admin
        $superAdmin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
            'position' => 'CTO',
            'department' => 'IT',
            'is_active' => true,
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create normal admin
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'position' => 'Administrator',
            'department' => 'Operations',
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        // Create manager
        $manager = Admin::create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'position' => 'Manager',
            'department' => 'Sales',
            'is_active' => true,
        ]);
        $manager->assignRole($managerRole);
    }
}
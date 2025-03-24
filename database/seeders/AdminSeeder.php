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
            // Existing admin/role permissions
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
            'manage permissions',
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'manage settings',
            'access dashboard',
            
            // Vehicle management permissions
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view cars',
            'create cars',
            'edit cars',
            'delete cars',
            'manage categories',
            'manage cars',
            'manage brands',    
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',
            'manage vehicles',
            'view vehicles',
            
            // Booking permissions
            'manage bookings',
            'view bookings',
            'create bookings',
            'edit bookings',
            'delete bookings',
            'update booking status',
            'update payment status',
            'export bookings',
            'view booking calendar',
            'view booking reports',
            
            // Customer permissions
            'manage customers',
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Blog management permissions
            'manage blog categories',
            'view blog categories',
            'create blog categories',
            'edit blog categories',
            'delete blog categories',
            'manage blog posts',
            'view blog posts',
            'create blog posts',
            'edit blog posts',
            'delete blog posts',
            'manage blog tags',
            'view blog tags',
            'create blog tags',
            'edit blog tags',
            'delete blog tags',
            'manage blog comments',
            'view blog comments',
            'create blog comments',
            'edit blog comments',
            'delete blog comments',
            
            // Report permissions
            'manage reports',
            'view reports',
            'export reports',

            // Activity Log permissions
            'manage activities',
            'view activities',
            'delete activities',
            'clear activities'
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
            'manage bookings',
            'view bookings',
            'create bookings',
            'edit bookings',
            'update booking status',
            'update payment status',
            'export bookings',
            'view booking calendar',
            'view booking reports',
            'view customers',
            'view cars',
            'view categories',
            'view brands',
        ]);

        $managerRole = Role::create(['name' => 'Manager', 'guard_name' => 'admin']);
        $managerRole->givePermissionTo([
            'access dashboard',
            'view admins',
            'view bookings',
            'create bookings',
            'update booking status',
            'view booking calendar',
            'view customers',
            'view cars',
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
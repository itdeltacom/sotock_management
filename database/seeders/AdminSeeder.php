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
            
            // Profile and password permissions
            'view profile',
            'edit profile',
            'change password',
            
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

            // Reviews
            'manage reviews',
            'create reviews',
            'edit reviews',
            'delete reviews',
            'approve reviews',
            
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
            'export customers',
            
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
            'publish blog posts',
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
            'approve blog comments',
            
            // Report permissions
            'manage reports',
            'view reports',
            'generate reports',
            'export reports',
            'view revenue reports',
            'view booking reports',
            'view vehicle reports',

            // Activity Log permissions
            'manage activities',
            'view activities',
            'delete activities',
            'clear activities',
            
            // Testimonials
            'view testimonials',
            'create testimonials',
            'edit testimonials',
            'delete testimonials',
            'manage testimonials',
            'approve testimonials',
            'feature testimonials',
            
            // Newsletter permissions
            'manage newsletters',
            'view newsletters',
            'create newsletters',
            'edit newsletters',
            'delete newsletters',
            'send newsletters',
            'manage newsletter subscribers',
            'view newsletter subscribers',
            'edit newsletter subscribers',
            'delete newsletter subscribers',
            'import newsletter subscribers',
            'export newsletter subscribers',
            
            // Document management permissions
            'manage documents',
            'view documents',
            'upload documents',
            'edit documents',
            'delete documents',
            'view expiring documents',
            'renew documents',
            'export documents',
            'manage car documents',
            'upload car documents',
            'edit car documents',
            'delete car documents',
            'download car documents',
            'view car documents',
            
            // Maintenance
            'manage maintenance',
            'view maintenance',
            'create maintenance',
            'edit maintenance',
            'delete maintenance',
            
            // Contracts
            'manage contracts',
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            'approve contracts',
            
            // Two-factor authentication
            'manage two-factor',
            'enable two-factor',
            'disable two-factor',
            
            // System
            'clear cache',
            'view system info',
            'manage backups',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        }

        // Create roles and assign permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'admin']);
        $adminRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
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
            'view newsletters',
            'create newsletters',
            'edit newsletters',
            'send newsletters',
            'view newsletter subscribers',
            'view reports',
            'generate reports',
            'view activities',
            'manage reviews',
            'manage testimonials',
            // Document management permissions for Admin
            'manage documents',
            'view documents',
            'upload documents',
            'edit documents',
            'manage car documents',
            'upload car documents',
            'edit car documents',
            'view car documents',
            'view expiring documents',
            'renew documents',
        ]);

        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'admin']);
        $managerRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
            'view admins',
            'view bookings',
            'create bookings',
            'update booking status',
            'view booking calendar',
            'view customers',
            'view cars',
            'view reports',
            'view activities',
            'manage reviews',
            // Document management permissions for Manager
            'view documents',
            'view car documents',
            'view expiring documents',
        ]);

        // Content Editor Role
        $editorRole = Role::firstOrCreate(['name' => 'Content Editor', 'guard_name' => 'admin']);
        $editorRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
            'manage blog posts',
            'view blog posts',
            'create blog posts',
            'edit blog posts',
            'publish blog posts',
            'manage blog categories',
            'view blog categories',
            'manage blog tags',
            'view blog tags',
            'manage blog comments',
            'view blog comments',
            'approve blog comments',
            'manage testimonials',
            'view testimonials',
            'create testimonials',
            'edit testimonials',
        ]);

        // Fleet Manager Role (New)
        $fleetManagerRole = Role::firstOrCreate(['name' => 'Fleet Manager', 'guard_name' => 'admin']);
        $fleetManagerRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
            'manage cars',
            'view cars',
            'create cars',
            'edit cars',
            'manage brands',
            'view brands',
            'manage categories',
            'view categories',
            'view bookings',
            'view booking calendar',
            // Document management permissions for Fleet Manager
            'manage documents',
            'view documents',
            'upload documents',
            'edit documents',
            'manage car documents',
            'upload car documents',
            'edit car documents',
            'view car documents',
            'view expiring documents',
            'renew documents',
            'export documents',
        ]);

        // Create super admin
        $superAdmin = Admin::firstOrCreate([
            'email' => 'superadmin@example.com'
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'position' => 'CTO',
            'department' => 'IT',
            'is_active' => true,
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create normal admin
        $admin = Admin::firstOrCreate([
            'email' => 'admin@example.com'
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'position' => 'Administrator',
            'department' => 'Operations',
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        // Create manager
        $manager = Admin::firstOrCreate([
            'email' => 'manager@example.com'
        ], [
            'name' => 'Manager User',
            'password' => Hash::make('password'),
            'position' => 'Manager',
            'department' => 'Sales',
            'is_active' => true,
        ]);
        $manager->assignRole($managerRole);

        // Create content editor
        $editor = Admin::firstOrCreate([
            'email' => 'editor@example.com'
        ], [
            'name' => 'Content Editor',
            'password' => Hash::make('password'),
            'position' => 'Content Editor',
            'department' => 'Marketing',
            'is_active' => true,
        ]);
        $editor->assignRole($editorRole);

        // Create fleet manager
        $fleetManager = Admin::firstOrCreate([
            'email' => 'fleet@example.com'
        ], [
            'name' => 'Fleet Manager',
            'password' => Hash::make('password'),
            'position' => 'Fleet Manager',
            'department' => 'Operations',
            'is_active' => true,
        ]);
        $fleetManager->assignRole($fleetManagerRole);
    }
}
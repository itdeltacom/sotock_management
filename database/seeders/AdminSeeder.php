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
            // System & Admin Management
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
            'clear cache',
            'view system info',
            'manage backups',
            
            // Profile
            'view profile',
            'edit profile',
            'change password',
            
            // Two-Factor Authentication
            'manage two-factor',
            'enable two-factor',
            'disable two-factor',
            
            // Activity Logs
            'manage activities',
            'view activities',
            'delete activities',
            'clear activities',
            
            // Vehicle Management
            'manage categories',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'manage brands',
            'view brands',
            'create brands',
            'edit brands',
            'delete brands',
            'manage cars',
            'view cars',
            'create cars',
            'edit cars',
            'delete cars',
            'check vehicle availability',
            
            // Documents
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
            'export maintenance',
            'print maintenance',
            'view maintenance history',
            'view due maintenance',
            'view overdue maintenance',
            'schedule maintenance',
            'update maintenance status',
            'manage maintenance types',
            'add maintenance parts',
            'edit maintenance parts',
            'delete maintenance parts',
            'manage oil changes',
            'manage tire rotations',
            'manage brake services',
            'manage general services',
            'view maintenance reports',
            'generate maintenance reports',
            'export maintenance reports',
            'manage maintenance notifications',
            'manage maintenance reminders',
            'view maintenance calendar',
            
            // Bookings
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
            
            // Contracts
            'manage contracts',
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            'complete contracts',
            'cancel contracts',
            'extend contracts',
            'view ending contracts',
            'view overdue contracts',
            'export contracts',
            'print contracts',
            'upload contract documents',
            'delete contract documents',
            'manage contract templates',
            'approve contracts',
            'reject contracts',
            'view contract reports',
            'generate contract reports',
            'export contract reports',
            'manage contract terms',
            'edit contract terms',
            'view contract history',
            'manage contract payments',
            'add contract payments',
            'edit contract payments',
            'delete contract payments',
            'view contract payments',
            'export contract payments',
            'print contract payments',
            'manage contract signatures',
            'verify contract signatures',
            'manage contract reminders',
            'send contract reminders',
            'manage contract notifications',
            'view contract calendar',
            
            // Customers
            'manage customers',
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'export customers',
            'view client contracts',
            'view client details',
            'manage client documents',
            'upload client documents',
            'delete client documents',
            'verify client identity',
            'manage client notes',
            'add client notes',
            'edit client notes',
            'delete client notes',
            'view client history',
            'manage client blacklist',
            'add to blacklist',
            'remove from blacklist',
            'view blacklisted clients',
            'manage client ratings',
            'view client ratings',
            'update client ratings',
            'manage client licenses',
            'verify client licenses',
            'manage client insurance',
            'verify client insurance',
            
            // Reviews & Testimonials
            'manage reviews',
            'create reviews',
            'edit reviews',
            'delete reviews',
            'approve reviews',
            'manage testimonials',
            'view testimonials',
            'create testimonials',
            'edit testimonials',
            'delete testimonials',
            'approve testimonials',
            'feature testimonials',
            
            // Blog
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
            
            // Newsletter
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
            
            // Reports
            'manage reports',
            'view reports',
            'generate reports',
            'export reports',
            'view revenue reports',
            'view booking reports',
            'view vehicle reports',
            'view contract reports',
            'view maintenance reports',
            'view customer reports',
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
            'manage contracts',
            'view contracts',
            'create contracts',
            'edit contracts',
            'complete contracts',
            'cancel contracts',
            'extend contracts',
            'view ending contracts',
            'view overdue contracts',
            'manage customers',
            'view customers',
            'create customers',
            'edit customers',
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
            'view maintenance',
            'view maintenance history',
            'view due maintenance',
            'view overdue maintenance',
            'view maintenance reports',
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
            'view contracts',
            'create contracts',
            'edit contracts',
            'view customers',
            'view cars',
            'view reports',
            'view activities',
            'manage reviews',
            'view documents',
            'view car documents',
            'view expiring documents',
            'view maintenance',
            'view maintenance history',
            'view due maintenance',
            'view maintenance reports',
        ]);

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
            'manage maintenance',
            'view maintenance',
            'create maintenance',
            'edit maintenance',
            'delete maintenance',
            'export maintenance',
            'print maintenance',
            'view maintenance history',
            'view due maintenance',
            'view overdue maintenance',
            'schedule maintenance',
            'update maintenance status',
            'add maintenance parts',
            'edit maintenance parts',
            'delete maintenance parts',
            'manage oil changes',
            'manage tire rotations',
            'manage brake services',
            'manage general services',
            'view maintenance reports',
            'export maintenance reports',
            'manage maintenance reminders',
            'view maintenance calendar',
        ]);

        $maintenanceManagerRole = Role::firstOrCreate(['name' => 'Maintenance Manager', 'guard_name' => 'admin']);
        $maintenanceManagerRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
            'view cars',
            'manage maintenance',
            'view maintenance',
            'create maintenance',
            'edit maintenance',
            'delete maintenance',
            'export maintenance',
            'print maintenance',
            'view maintenance history',
            'view due maintenance',
            'view overdue maintenance',
            'schedule maintenance',
            'update maintenance status',
            'manage maintenance types',
            'add maintenance parts',
            'edit maintenance parts',
            'delete maintenance parts',
            'manage oil changes',
            'manage tire rotations',
            'manage brake services',
            'manage general services',
            'view maintenance reports',
            'generate maintenance reports',
            'export maintenance reports',
            'manage maintenance notifications',
            'manage maintenance reminders',
            'view maintenance calendar',
            'view car documents',
            'view expiring documents',
        ]);

        $contractManagerRole = Role::firstOrCreate(['name' => 'Contract Manager', 'guard_name' => 'admin']);
        $contractManagerRole->syncPermissions([
            'access dashboard',
            'view profile',
            'edit profile',
            'change password',
            'manage contracts',
            'view contracts',
            'create contracts',
            'edit contracts',
            'delete contracts',
            'complete contracts',
            'cancel contracts',
            'extend contracts',
            'view ending contracts',
            'view overdue contracts',
            'export contracts',
            'print contracts',
            'upload contract documents',
            'delete contract documents',
            'approve contracts',
            'view contract reports',
            'manage customers',
            'view customers',
            'create customers',
            'edit customers',
            'view cars',
            'view booking calendar',
            'view reports',
            'manage clients',
            'create clients',
            'edit clients',
            'delete clients',
            'view client contracts',
            'view client details',
            'manage client documents',
            'upload client documents',
            'delete client documents',
            'verify client identity',
            'manage client notes',
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
        
        // Create maintenance manager
        $maintenanceManager = Admin::firstOrCreate([
            'email' => 'maintenance@example.com'
        ], [
            'name' => 'Maintenance Manager',
            'password' => Hash::make('password'),
            'position' => 'Maintenance Manager',
            'department' => 'Operations',
            'is_active' => true,
        ]);
        $maintenanceManager->assignRole($maintenanceManagerRole);
        
        // Create contract manager
        $contractManager = Admin::firstOrCreate([
            'email' => 'contracts@example.com'
        ], [
            'name' => 'Contract Manager',
            'password' => Hash::make('password'),
            'position' => 'Contract Manager',
            'department' => 'Legal',
            'is_active' => true,
        ]);
        $contractManager->assignRole($contractManagerRole);
    }
}
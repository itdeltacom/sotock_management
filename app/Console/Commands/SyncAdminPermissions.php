<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize admin permissions and ensure the Super Admin role has all permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permission synchronization...');

        // Step 1: Find or create the Super Admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super admin',
            'guard_name' => 'admin'
        ]);

        $this->info('Ensuring Super Admin role exists: Done');

        // Step 2: Define the core system permissions
        $systemPermissions = [
            // Admin Management
            'manage admins' => 'Full control over admin users',
            'view admins' => 'View admin users',
            'create admins' => 'Create new admin users',
            'edit admins' => 'Edit existing admin users',
            'delete admins' => 'Delete admin users',
            
            // Role Management
            'manage roles' => 'Full control over admin roles',
            'view roles' => 'View admin roles',
            'create roles' => 'Create new admin roles',
            'edit roles' => 'Edit existing admin roles',
            'delete roles' => 'Delete admin roles',
            
            // Permission Management
            'manage permissions' => 'Full control over permissions',
            'view permissions' => 'View permissions',
            'create permissions' => 'Create new permissions',
            'edit permissions' => 'Edit existing permissions',
            'delete permissions' => 'Delete permissions',
            
            // Miscellaneous
            'manage settings' => 'Manage system settings',
            'access dashboard' => 'Access admin dashboard',
        ];

        $this->info('Creating or updating system permissions...');
        $bar = $this->output->createProgressBar(count($systemPermissions));
        $bar->start();

        $createdCount = 0;
        $updatedCount = 0;

        // Step 3: Create or update the permissions and assign to Super Admin
        foreach ($systemPermissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                [
                    'name' => $name,
                    'guard_name' => 'admin'
                ],
                [
                    'description' => $description
                ]
            );

            // Update description if different
            if ($permission->description !== $description) {
                $permission->description = $description;
                $permission->save();
                $updatedCount++;
            } else if ($permission->wasRecentlyCreated) {
                $createdCount++;
            }

            // Make sure Super Admin has this permission
            if (!$superAdminRole->hasPermissionTo($permission)) {
                $superAdminRole->givePermissionTo($permission);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Step 4: Ensure Super Admin has ALL permissions (even custom ones)
        $this->info('Ensuring Super Admin has all permissions...');
        $allPermissions = Permission::where('guard_name', 'admin')->get();
        $superAdminRole->syncPermissions($allPermissions);

        $this->info('Successfully synchronized permissions:');
        $this->info("- Created: $createdCount");
        $this->info("- Updated: $updatedCount");
        $this->info("- Total permissions: " . $allPermissions->count());
        
        return Command::SUCCESS;
    }
}
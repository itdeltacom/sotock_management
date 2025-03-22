<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncSuperAdminPermissions extends Command
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
    protected $description = 'Sync all permissions to the Super Admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdminRole = Role::where('name', 'Super Admin')
                              ->where('guard_name', 'admin')
                              ->first();
        
        if (!$superAdminRole) {
            $this->error('Super Admin role not found!');
            return 1;
        }

        $allPermissions = Permission::where('guard_name', 'admin')->get();
        
        if ($allPermissions->isEmpty()) {
            $this->warn('No permissions found in the system.');
            return 0;
        }

        $superAdminRole->syncPermissions($allPermissions);
        
        $this->info('Successfully synced ' . $allPermissions->count() . ' permissions to the Super Admin role.');
        
        return 0;
    }
}
<?php

namespace App\Observers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return void
     */
    public function created(Permission $permission)
    {
        // Auto-assign new permissions to Super Admin role
        if ($permission->guard_name === 'admin') {
            $superAdminRole = Role::where('name', 'super admin')
                ->where('guard_name', 'admin')
                ->first();
                
            if ($superAdminRole) {
                $superAdminRole->givePermissionTo($permission);
                
                // Log this action if activity logging is implemented
                if (function_exists('activity')) {
                    activity()
                        ->performedOn($permission)
                        ->withProperties([
                            'role' => $superAdminRole->name,
                            'permission' => $permission->name
                        ])
                        ->log('Permission automatically assigned to Super Admin role');
                }
            }
        }
    }

    /**
     * Handle the Permission "updated" event.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return void
     */
    public function updated(Permission $permission)
    {
        // Optional: You could log permission updates here
        if (function_exists('activity')) {
            activity()
                ->performedOn($permission)
                ->withProperties([
                    'old_name' => $permission->getOriginal('name'),
                    'new_name' => $permission->name
                ])
                ->log('Permission updated');
        }
    }

    /**
     * Handle the Permission "deleted" event.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return void
     */
    public function deleted(Permission $permission)
    {
        // Optional: You could log permission deletions here
        if (function_exists('activity')) {
            activity()
                ->performedOn($permission)
                ->withProperties([
                    'name' => $permission->name,
                    'guard_name' => $permission->guard_name
                ])
                ->log('Permission deleted');
        }
    }

    /**
     * Handle the Permission "restored" event.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return void
     */
    public function restored(Permission $permission)
    {
        // When a permission is restored (from soft-delete), re-assign to Super Admin
        if ($permission->guard_name === 'admin') {
            $superAdminRole = Role::where('name', 'super admin')
                ->where('guard_name', 'admin')
                ->first();
                
            if ($superAdminRole) {
                $superAdminRole->givePermissionTo($permission);
            }
        }
    }

    /**
     * Handle the Permission "force deleted" event.
     *
     * @param  \Spatie\Permission\Models\Permission  $permission
     * @return void
     */
    public function forceDeleted(Permission $permission)
    {
        // No additional actions needed for force delete
    }
}
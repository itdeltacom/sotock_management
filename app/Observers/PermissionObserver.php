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
        // When a new permission is created, automatically assign it to the Super Admin role
        if ($permission->guard_name === 'admin') {
            $superAdminRole = Role::where('name', 'Super Admin')
                                  ->where('guard_name', 'admin')
                                  ->first();
            
            if ($superAdminRole) {
                $superAdminRole->givePermissionTo($permission);
            }
        }
    }
}
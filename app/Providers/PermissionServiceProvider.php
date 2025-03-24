<?php

namespace App\Providers;

use Spatie\Permission\Models\Role;
use App\Observers\PermissionObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use App\Console\Commands\SyncAdminPermissions;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the observer
        Permission::observe(PermissionObserver::class);
        
        // Register a command to sync permissions
        $this->app->booted(function () {
            $this->commands([
                SyncAdminPermissions::class,
            ]);
        });
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Admin\NotificationController;

class AdminNotificationServiceProvider extends ServiceProvider
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
        View::composer('admin.includes.navbar', function ($view) {
            $notificationController = new NotificationController();
            $notificationData = $notificationController->getNotifications();
            
            $view->with('notifications', $notificationData['notifications']);
            $view->with('unreadNotifications', $notificationData['unread_count']);
        });
    }
}
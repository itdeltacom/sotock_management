<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\PermissionController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| This file contains all the routes for the admin panel.
|
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes
    Route::middleware('guest:admin')->group(function () {
        // Authentication
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
        
        // Password Reset
        Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
        Route::post('/validate-password', [AuthController::class, 'validatePassword'])->name('password.validate');
    });
    
    // Two-Factor Authentication Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('two-factor/verify', [TwoFactorController::class, 'showVerificationForm'])->name('two-factor.verify');
        Route::post('two-factor/verify', [TwoFactorController::class, 'verify']);
    });
    
    // Authenticated routes
    Route::middleware(['auth:admin', TwoFactorMiddleware::class])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        
        // Logout
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        
        // Profile
        Route::get('profile', [AdminController::class, 'profile'])->name('profile');
        Route::put('profile', [AdminController::class, 'updateProfile'])->name('profile.update');
        
        // Two-Factor Authentication Setup
        Route::get('two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
        Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
        Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
        
        // Activity Logs
        Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
        Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
        Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');
        Route::post('activities/clear', [ActivityController::class, 'clearAll'])->name('activities.clear');
        
        // Bookings Routes (Placeholder - you'll need to create these controllers)
        Route::resource('bookings', BookingController::class);
        Route::get('bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
        
        // Vehicles Routes (Placeholder - you'll need to create these controllers)
        Route::resource('vehicles', VehicleController::class);
        Route::resource('vehicles/categories', VehicleCategoryController::class)->except(['show']);
        
        // Customers Routes (Placeholder - you'll need to create this controller)
        Route::resource('customers', CustomerController::class);
        
        // Reports Routes (Placeholder - you'll need to create this controller)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('bookings', [ReportController::class, 'bookings'])->name('bookings');
            Route::get('vehicles', [ReportController::class, 'vehicles'])->name('vehicles');
        });
        
        // Settings (Placeholder - you'll need to create this controller)
        Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
        Route::post('settings/general', [SettingController::class, 'updateGeneral']);
        
        // Admin Management (requires permissions)
        Route::middleware('permission:manage admins')->group(function () {
            Route::resource('admins', AdminController::class);
        });
        
        // Role & Permission Management
        // These routes are protected by both permission middleware AND Super Admin middleware
        // for double security
        Route::middleware(['permission:manage roles', SuperAdminMiddleware::class])->group(function () {
            Route::resource('roles', RoleController::class);
        });
        
        // Permission Management - Super Admin only
        Route::middleware(SuperAdminMiddleware::class)->group(function () {
            Route::resource('permissions', PermissionController::class);
        });
        
        // Artisan command to sync permissions - access via web for Super Admin only
        Route::middleware(SuperAdminMiddleware::class)->group(function () {
            Route::post('sync-permissions', function () {
                Artisan::call('admin:sync-permissions');
                return back()->with('success', 'Super Admin permissions synchronized successfully.');
            })->name('sync-permissions');
        });
    });
});
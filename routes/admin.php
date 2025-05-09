<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Middleware\TwoFactorMiddleware;
use App\Http\Controllers\Admin\CarController;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContractController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\BlogCommentController;
use App\Http\Controllers\Admin\CarDocumentController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\CarMaintenanceController;
use App\Http\Controllers\Admin\NewsletterAdminController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| This file contains all the routes for the admin panel.
|
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');

        // Password Reset Routes
        Route::get('forgot-password', [AdminAuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('password.email');
        Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');
        Route::post('/validate-password', [AdminAuthController::class, 'validatePassword'])->name('password.validate');
    });Route::get('password/change', [AdminAuthController::class, 'showChangePasswordForm'])->name('password.change');
Route::post('password/change', [AdminAuthController::class, 'changePassword'])->name('password.update');
// Password Change
//Route::get('password/change', [AdminAuthController::class, 'showChangePasswordForm'])->name('password.change');
//Route::post('password/change', [AdminAuthController::class, 'changePassword'])->name('password.update');
    
    // Two-Factor Authentication Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('two-factor/verify', [TwoFactorController::class, 'showVerificationForm'])->name('two-factor.verify');
        Route::post('two-factor/verify', [TwoFactorController::class, 'verify']);
    });
    
    // Authenticated routes
    Route::middleware(['auth:admin', TwoFactorMiddleware::class])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        
        // Logout
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        
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
        
        // Admin Management (requires permissions)
        Route::middleware('permission:view admins')->group(function () {
            Route::get('admins', [AdminController::class, 'index'])->name('admins.index');
            Route::get('admins/data', [AdminController::class, 'data'])->name('admins.data');
            Route::get('admins/{admin}', [AdminController::class, 'show'])->name('admins.show');
        });

        Route::middleware('permission:create admins')->group(function () {
            Route::post('admins', [AdminController::class, 'store'])->name('admins.store');
        });

        Route::middleware('permission:edit admins')->group(function () {
            Route::get('admins/{admin}/edit', [AdminController::class, 'edit'])->name('admins.edit');
            Route::get('admins/{admin}/edit/form', [AdminController::class, 'getEditForm'])->name('admins.edit.form');
            Route::put('admins/{admin}', [AdminController::class, 'update'])->name('admins.update');
        });

        Route::middleware('permission:delete admins')->group(function () {
            Route::delete('admins/{admin}', [AdminController::class, 'destroy'])->name('admins.destroy');
        });

       // Permission Routes 
Route::prefix('permissions')->name('permissions.')->middleware(SuperAdminMiddleware::class)->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/data', [PermissionController::class, 'data'])->name('data');
    Route::post('/', [PermissionController::class, 'store'])->name('store');
    Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
    Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    Route::post('/validate-field', [PermissionController::class, 'validateField'])->name('validate-field');
});

// Role Routes 
Route::prefix('roles')->name('roles.')->middleware('permission:manage roles')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/data', [RoleController::class, 'data'])->name('data');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/validate-field', [RoleController::class, 'validateField'])->name('validate-field');
});
      // Category Routes
Route::prefix('categories')->name('categories.')->middleware('permission:manage categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::get('/data', [CategoryController::class, 'data'])->name('data');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
});

// Brand Routes
Route::prefix('brands')->name('brands.')->middleware('permission:manage brands')->group(function () {
    Route::get('/', [BrandController::class, 'index'])->name('index');
    Route::get('/data', [BrandController::class, 'data'])->name('data');
    Route::post('/', [BrandController::class, 'store'])->name('store');
    Route::get('/{brand}/edit', [BrandController::class, 'edit'])->name('edit');
    Route::put('/{brand}', [BrandController::class, 'update'])->name('update');
    Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
});

// Car Routes
Route::prefix('cars')->name('cars.')->middleware('permission:manage cars')->group(function () {
    // Basic car management
    Route::get('/', [CarController::class, 'index'])->name('index');
    Route::get('/data', [CarController::class, 'datatable'])->name('data');
    Route::post('/', [CarController::class, 'store'])->name('store');
    Route::get('/{car}/edit', [CarController::class, 'edit'])->name('edit');
    Route::get('/{car}', [CarController::class, 'show'])->name('show'); // Add this to fix the show route error
    Route::put('/{car}', [CarController::class, 'update'])->name('update');
    Route::delete('/{car}', [CarController::class, 'destroy'])->name('destroy');
    
    // Car image management
    Route::post('/update-image-order', [CarController::class, 'updateImageOrder'])->name('update-image-order');
    Route::post('/set-featured-image', [CarController::class, 'setFeaturedImage'])->name('set-featured-image');
    Route::post('/delete-image', [CarController::class, 'deleteImage'])->name('delete-image');
    
    // Car Document Management Routes
    Route::get('{car}/documents', [CarDocumentController::class, 'getDocuments'])->name('documents');
    Route::post('{car}/documents/update', [CarDocumentController::class, 'ajaxUpdate'])->name('documents.update');
    Route::post('{car}/documents/upload', [CarDocumentController::class, 'uploadDocument'])->name('documents.upload');
    Route::delete('{car}/documents/delete', [CarDocumentController::class, 'deleteDocument'])->name('documents.delete');
    Route::get('{car}/documents/show', [CarDocumentController::class, 'show'])->name('documents.show');
    
    // Expiring documents view
    Route::get('documents/expiring', [CarDocumentController::class, 'expiringDocuments'])->name('documents.expiring');
    Route::get('documents/expiring/data', [CarDocumentController::class, 'expiringDocumentsDatatable'])->name('documents.expiring.data');
});
// Car Maintenance Routes
Route::prefix('cars/maintenance')->name('cars.maintenance.')->group(function () {
    // Due Soon Dashboard
    Route::get('/due-soon', [CarMaintenanceController::class, 'maintenanceDueSoon'])
        ->name('due-soon');
    Route::get('/datatable/due-soon', [CarMaintenanceController::class, 'maintenanceDueSoonDatatable'])
        ->name('due-soon.datatable');
    
    // Export and Print for Due Soon
    Route::get('/due-soon/print', [CarMaintenanceController::class, 'printDueMaintenance'])
        ->name('due-soon.print');
    Route::get('/due-soon/export-csv', [CarMaintenanceController::class, 'exportDueMaintenanceCsv'])
        ->name('due-soon.export-csv');
    
    // Maintenance Counters API
    Route::get('/counters', [CarMaintenanceController::class, 'getMaintenanceCounters'])
        ->name('counters');
    
    // Car-specific maintenance routes
    Route::prefix('/{car}')->group(function () {
        Route::get('/', [CarMaintenanceController::class, 'index'])->name('index');
        Route::get('/datatable', [CarMaintenanceController::class, 'datatable'])->name('datatable');
        Route::post('/', [CarMaintenanceController::class, 'store'])->name('store');
        Route::get('/{maintenance}/edit', [CarMaintenanceController::class, 'edit'])->name('edit');
        Route::put('/{maintenance}', [CarMaintenanceController::class, 'update'])->name('update');
        Route::delete('/{maintenance}', [CarMaintenanceController::class, 'destroy'])->name('destroy');
        
        // Export and Print for specific car
        Route::get('/print', [CarMaintenanceController::class, 'printMaintenanceHistory'])->name('print');
        Route::get('/export-csv', [CarMaintenanceController::class, 'exportCsv'])->name('export-csv');
    });
});

// Booking Management Routes
Route::prefix('bookings')->name('bookings.')->middleware(['auth:admin', 'permission:manage bookings'])->group(function () {
    // Routes pour lister et gérer les réservations
    Route::get('/', [BookingController::class, 'index'])->name('index');
    Route::get('/data', [BookingController::class, 'data'])->name('data');
    Route::post('/', [BookingController::class, 'store'])->name('store');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    Route::get('/{booking}/edit', [BookingController::class, 'edit'])->name('edit');
    Route::put('/{booking}', [BookingController::class, 'update'])->name('update');
    Route::delete('/{booking}', [BookingController::class, 'destroy'])->name('destroy');

    // Routes supplémentaires spécifiques aux réservations
    Route::get('/export', [BookingController::class, 'export'])->name('export');
    Route::post('/calculate-prices', [BookingController::class, 'calculatePrices'])->name('calculate-prices');
    Route::patch('/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('update-status');
    Route::patch('/{booking}/update-payment-status', [BookingController::class, 'updatePaymentStatus'])->name('update-payment-status');
    Route::patch('/{booking}/update-deposit-status', [BookingController::class, 'updateDepositStatus'])->name('update-deposit-status');
    Route::get('/dashboard-stats', [BookingController::class, 'dashboardStats'])->name('dashboard-stats');
    Route::get('/calendar', [BookingController::class, 'calendar'])->name('calendar');
    
    // Nouvelles routes pour la gestion de location
    Route::post('/{booking}/start-rental', [BookingController::class, 'startRental'])->name('start-rental');
    Route::post('/{booking}/complete-rental', [BookingController::class, 'completeRental'])->name('complete-rental');
    Route::post('/calculate-mileage-charges', [BookingController::class, 'calculateMileageCharges'])->name('calculate-mileage-charges');
    
    // Routes optionnelles pour les rapports de kilométrage
    Route::get('/mileage-report', [BookingController::class, 'mileageReport'])->name('mileage-report');
    Route::get('/check-mileage-integrity', [BookingController::class, 'checkMileageIntegrity'])->name('check-mileage-integrity');
});

// Additional Contract Routes
Route::prefix('contracts')->name('contracts.')->middleware(['auth:admin', 'permission:manage contracts'])->group(function () {
    // Define specific routes first - BEFORE any resource routes
    Route::get('/ending-soon', [ContractController::class, 'endingSoon'])->name('ending-soon');
    Route::get('/ending-soon/data', [ContractController::class, 'endingSoonDatatable'])->name('ending-soon.datatable');
    Route::get('/overdue', [ContractController::class, 'overdue'])->name('overdue');
    Route::get('/overdue/data', [ContractController::class, 'overdueDatatable'])->name('overdue.datatable');
    Route::get('/pending-bookings', [ContractController::class, 'getPendingBookings'])->name('pending-bookings');
    Route::post('/{contract}/notify', [ContractController::class, 'sendNotification'])->name('notify');
    
    // Then define resourceful routes
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/stats', [ContractController::class, 'getStats'])->name('stats');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::get('/data', [ContractController::class, 'datatable'])->name('datatable');
    Route::post('/', [ContractController::class, 'store'])->name('store');
    Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
    Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
    Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
    Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
    Route::post('/{contract}/upload-document', [ContractController::class, 'uploadDocument'])->name('upload-document');
    Route::delete('/{contract}/delete-document', [ContractController::class, 'deleteDocument'])->name('delete-document');
    Route::post('/{contract}/complete', [ContractController::class, 'complete'])->name('complete');
    Route::post('/{contract}/cancel', [ContractController::class, 'cancel'])->name('cancel');
    Route::post('/{contract}/extend', [ContractController::class, 'extend'])->name('extend');
    Route::get('/{contract}/print', [ContractController::class, 'printContract'])->name('print');
});
/*Route::prefix('contracts')->name('contracts.')->middleware(['auth:admin', 'permission:manage contracts'])->group(function () {
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/stats', [ContractController::class, 'getStats'])->name('stats');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::get('/data', [ContractController::class, 'datatable'])->name('datatable');
    Route::post('/', [ContractController::class, 'store'])->name('store');
    Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
    Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
    Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
    Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');
    Route::post('/{contract}/upload-document', [ContractController::class, 'uploadDocument'])->name('upload-document');
    Route::delete('/{contract}/delete-document', [ContractController::class, 'deleteDocument'])->name('delete-document');
    Route::post('/{contract}/complete', [ContractController::class, 'complete'])->name('complete');
    Route::post('/{contract}/cancel', [ContractController::class, 'cancel'])->name('cancel');
    Route::post('/{contract}/extend', [ContractController::class, 'extend'])->name('extend');
    Route::get('/{contract}/print', [ContractController::class, 'printContract'])->name('print');
    Route::get('/ending-soon', [ContractController::class, 'endingSoon'])->name('ending-soon');
    Route::get('/ending-soon/data', [ContractController::class, 'endingSoonDatatable'])->name('ending-soon.datatable');
    Route::get('/overdue', [ContractController::class, 'overdue'])->name('overdue');
    Route::get('/overdue/data', [ContractController::class, 'overdueDatatable'])->name('overdue.datatable');
    Route::get('/pending-bookings', [ContractController::class, 'getPendingBookings'])->name('pending-bookings');
});*/

// API endpoints for contract management
Route::prefix('api')->name('api.')->middleware(['auth:admin'])->group(function () {
    // Get available clients
    Route::get('/clients', [CustomerController::class, 'getClientsList'])->name('clients.list');
    
    // Get available cars
    Route::get('/cars/available', [CarController::class, 'getAvailableCars'])->name('cars.available');
});


// Customer Management Routes
Route::prefix('clients')->name('clients.')->middleware(['auth:admin', 'permission:manage clients'])->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/api/{client}', [CustomerController::class, 'show'])->name('api.show');
    //Route::get('/api/{id}', [CustomerController::class, 'getClientDetails'])->name('api.show');
    Route::get('/{client}', [CustomerController::class, 'show'])->name('show');
    Route::get('/{client}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{client}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{client}', [CustomerController::class, 'destroy'])->name('destroy');
    Route::post('/datatable', [CustomerController::class, 'datatable'])->name('datatable');
    Route::get('/list/ajax', [CustomerController::class, 'getClientsList'])->name('list');
    
    // Additional customer routes
    Route::get('/{client}/payments', [CustomerController::class, 'payments'])->name('payments');
    Route::post('/{client}/payments', [CustomerController::class, 'addPayment'])->name('payments.add');
    Route::get('/{client}/contracts', [CustomerController::class, 'contracts'])->name('contracts');
    Route::get('/{client}/statistics', [CustomerController::class, 'getStatistics'])->name('statistics');
    Route::get('/{client}/status', [CustomerController::class, 'checkStatus'])->name('status');
    Route::post('/{client}/ban', [CustomerController::class, 'ban'])->name('ban');
    Route::post('/{client}/unban', [CustomerController::class, 'unban'])->name('unban');
    Route::post('/{client}/notify', [CustomerController::class, 'sendNotification'])->name('sendNotification');
    Route::get('/export', [CustomerController::class, 'export'])->name('export');
});

// Review Management
Route::prefix('reviews')->name('reviews.')->middleware('permission:manage reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('index');
    Route::get('/data', [ReviewController::class, 'data'])->name('data');
    Route::post('/', [ReviewController::class, 'store'])->name('store');
    Route::get('/{review}/edit', [ReviewController::class, 'edit'])->name('edit');
    Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    Route::post('/{review}/toggle-approval', [ReviewController::class, 'toggleApproval'])->name('toggle-approval');
    Route::get('/get-users', [ReviewController::class, 'getUsers'])->name('get-users');
});

// Testimonial Routes
Route::prefix('testimonials')->name('testimonials.')->group(function () {
    Route::get('/', [TestimonialController::class, 'index'])->name('index');
    Route::get('/data', [TestimonialController::class, 'data'])->name('data');
    Route::post('/', [TestimonialController::class, 'store'])->name('store')->middleware('can:create testimonials');
    Route::get('/{testimonial}', [TestimonialController::class, 'show'])->name('show')->middleware('can:view testimonials');
    Route::get('/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('edit')->middleware('can:edit testimonials');
    Route::put('/{testimonial}', [TestimonialController::class, 'update'])->name('update')->middleware('can:edit testimonials');
    Route::delete('/{testimonial}', [TestimonialController::class, 'destroy'])->name('destroy')->middleware('can:delete testimonials');
    
    // Additional actions
    Route::post('/{testimonial}/toggle-featured', [TestimonialController::class, 'toggleFeatured'])->name('toggle-featured')->middleware('can:edit testimonials');
    Route::post('/{testimonial}/toggle-approval', [TestimonialController::class, 'toggleApproval'])->name('toggle-approval')->middleware('can:edit testimonials');
    Route::post('/update-order', [TestimonialController::class, 'updateOrder'])->name('update-order')->middleware('can:edit testimonials');
});

 // Blog Categories
 Route::prefix('blog-categories')->name('blog-categories.')->middleware('permission:manage blog categories')->group(function () {
    Route::get('/', [BlogCategoryController::class, 'index'])->name('index');
    Route::get('/data', [BlogCategoryController::class, 'data'])->name('data');
    Route::post('/', [BlogCategoryController::class, 'store'])->name('store');
    Route::get('/{category}', [BlogCategoryController::class, 'show'])->name('show');
    Route::get('/{category}/edit', [BlogCategoryController::class, 'edit'])->name('edit');
    Route::put('/{category}', [BlogCategoryController::class, 'update'])->name('update');
    Route::delete('/{category}', [BlogCategoryController::class, 'destroy'])->name('destroy');
    Route::post('/validate-field', [BlogCategoryController::class, 'validateField'])->name('validate-field');
    Route::get('/get-categories', [BlogCategoryController::class, 'getCategories'])->name('get-categories');
});

// Blog Tags
Route::prefix('blog-tags')->name('blog-tags.')->middleware('permission:manage blog tags')->group(function () {
    Route::get('/', [BlogTagController::class, 'index'])->name('index');
    Route::get('/data', [BlogTagController::class, 'data'])->name('data');
    Route::post('/', [BlogTagController::class, 'store'])->name('store');
    Route::get('/{tag}', [BlogTagController::class, 'show'])->name('show');
    Route::get('/{tag}/edit', [BlogTagController::class, 'edit'])->name('edit');
    Route::put('/{tag}', [BlogTagController::class, 'update'])->name('update');
    Route::delete('/{tag}', [BlogTagController::class, 'destroy'])->name('destroy');
    Route::post('/validate-field', [BlogTagController::class, 'validateField'])->name('validate-field');
    Route::get('/get-tags', [BlogTagController::class, 'getTags'])->name('get-tags');
});

// Blog Posts Routes
Route::prefix('blog-posts')->name('blog-posts.')->group(function () {
    Route::get('/', [BlogPostController::class, 'index'])->name('index');
    Route::get('/data', [BlogPostController::class, 'data'])->name('data');
    Route::post('/', [BlogPostController::class, 'store'])->name('store')->middleware('can:create blog posts');
    Route::get('/{post}', [BlogPostController::class, 'show'])->name('show')->middleware('can:view blog posts');
    Route::get('/{post}/edit', [BlogPostController::class, 'edit'])->name('edit')->middleware('can:edit blog posts');
    Route::put('/{post}', [BlogPostController::class, 'update'])->name('update')->middleware('can:edit blog posts');
    Route::delete('/{post}', [BlogPostController::class, 'destroy'])->name('destroy')->middleware('can:delete blog posts');
    
    // Additional actions
    Route::post('/upload-image', [BlogPostController::class, 'uploadImage'])->name('upload-image')->middleware('can:create blog posts');
    Route::post('/generate-slug', [BlogPostController::class, 'generateSlug'])->name('generate-slug');
    Route::post('/validate-slug', [BlogPostController::class, 'validateSlug'])->name('validate-slug');
    Route::post('/validate-field', [BlogPostController::class, 'validateField'])->name('validate-field');
    Route::post('/{post}/toggle-featured', [BlogPostController::class, 'toggleFeatured'])->name('toggle-featured')->middleware('can:edit blog posts');
    Route::post('/{post}/toggle-published', [BlogPostController::class, 'togglePublished'])->name('toggle-published')->middleware('can:edit blog posts');
});

// Blog Comments
Route::prefix('blog-comments')->name('blog-comments.')->middleware('permission:manage blog comments')->group(function () {
    Route::get('/', [BlogCommentController::class, 'index'])->name('index');
    Route::get('/data', [BlogCommentController::class, 'data'])->name('data');
    Route::get('/post/{post}', [BlogCommentController::class, 'getByPost'])->name('get-by-post');
    Route::post('/', [BlogCommentController::class, 'store'])->name('store');
    Route::get('/{comment}', [BlogCommentController::class, 'show'])->name('show');
    Route::put('/{comment}', [BlogCommentController::class, 'update'])->name('update');
    Route::delete('/{comment}', [BlogCommentController::class, 'destroy'])->name('destroy');
    Route::patch('/{comment}/approve', [BlogCommentController::class, 'approve'])->name('approve');
    Route::patch('/{comment}/reject', [BlogCommentController::class, 'reject'])->name('reject');
});
        // Bookings Routes (Placeholder - you'll need to create these controllers)
        Route::resource('bookings', BookingController::class);
        Route::get('bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
        // Admin Newsletter Routes (add to admin.php)
Route::prefix('newsletters')->name('newsletters.')->middleware(['auth:admin', 'permission:manage newsletters'])->group(function () {
    Route::get('/', [NewsletterAdminController::class, 'index'])->name('index');
    Route::get('/data', [NewsletterAdminController::class, 'data'])->name('data');
    Route::get('/create', [NewsletterAdminController::class, 'create'])->name('create');
    Route::post('/', [NewsletterAdminController::class, 'store'])->name('store');
    Route::get('/{newsletter}', [NewsletterAdminController::class, 'show'])->name('show');
    Route::get('/{newsletter}/edit', [NewsletterAdminController::class, 'edit'])->name('edit');
    Route::put('/{newsletter}', [NewsletterAdminController::class, 'update'])->name('update');
    Route::delete('/{newsletter}', [NewsletterAdminController::class, 'destroy'])->name('destroy');
    Route::post('/{newsletter}/send', [NewsletterAdminController::class, 'send'])->name('send');
    
    // Subscriber Management
    Route::get('/subscribers', [NewsletterAdminController::class, 'subscribers'])->name('subscribers');
    Route::get('/subscribers/data', [NewsletterAdminController::class, 'subscribersData'])->name('subscribers.data');
    Route::delete('/subscribers/{subscriber}', [NewsletterAdminController::class, 'deleteSubscriber'])->name('subscribers.delete');
    Route::post('/subscribers/{subscriber}/toggle-status', [NewsletterAdminController::class, 'toggleSubscriberStatus'])->name('subscribers.toggle-status');
    Route::post('/subscribers/{subscriber}/resend-confirmation', [NewsletterAdminController::class, 'resendConfirmation'])->name('subscribers.resend-confirmation');
    Route::post('/subscribers/import', [NewsletterAdminController::class, 'importSubscribers'])->name('subscribers.import');
    Route::get('/subscribers/export', [NewsletterAdminController::class, 'exportSubscribers'])->name('subscribers.export');
});

        
        // Customers Routes (Placeholder - you'll need to create this controller)
        Route::resource('customers', CustomerController::class);
        
        // Reports Routes (Placeholder - you'll need to create this controller)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('bookings', [ReportController::class, 'bookings'])->name('bookings');
            Route::get('vehicles', [ReportController::class, 'vehicles'])->name('vehicles');
        });
        
        // Settings (Placeholder - you'll need to create this controller)
        //Route::get('settings/general', [SettingController::class, 'general'])->name('settings.general');
        //Route::post('settings/general', [SettingController::class, 'updateGeneral']);
        
        // Role & Permission Management
        // These routes are protected by both permission middleware AND Super Admin middleware
        // for double security
        Route::middleware(['permission:manage roles', SuperAdminMiddleware::class])->group(function () {
            Route::resource('roles', RoleController::class);
        });
        
        // Activity Logs
        Route::prefix('activities')->name('activities.')->middleware('permission:view activities')->group(function () {
            Route::get('/', [ActivityController::class, 'index'])->name('index');
            Route::get('/export', [ActivityController::class, 'export'])->name('export');
            Route::get('/{activity}', [ActivityController::class, 'show'])->name('show');
            
            // These routes require additional permissions
            Route::delete('/{activity}', [ActivityController::class, 'destroy'])
                ->name('destroy')
                ->middleware('permission:delete activities');
            
            Route::delete('/clear', [ActivityController::class, 'clearAll'])
                ->name('clear')
                ->middleware('permission:clear activities');
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
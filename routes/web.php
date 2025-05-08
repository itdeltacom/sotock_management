<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\CarController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\ReviewController;
use App\Http\Controllers\Front\BookingController;
use App\Http\Controllers\Front\ProfileController;
use App\Http\Controllers\Front\CategoryController;
use App\Http\Controllers\Front\CarListingController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Front\BlogCommentController;
use App\Http\Controllers\Front\ClientDashboardController;
use App\Http\Controllers\Front\TestimonialFrontController;

// Home and static pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContactForm'])->name('contact.submit');
Route::get('/search-form-data', [HomeController::class, 'getSearchFormData'])->name('search.form.data');
Route::post('/process-search', [HomeController::class, 'processSearch'])->name('search.process');

// Car listing with AJAX pagination
Route::get('/cars', [CarListingController::class, 'index'])->name('cars.index');
Route::get('/cars/load', [CarListingController::class, 'loadCars'])->name('cars.loadCars');
Route::get('/cars/{slug}', [CarListingController::class, 'show'])->name('cars.show');
Route::get('/search', [CarListingController::class, 'search'])->name('cars.search');

// Booking process
Route::get('/booking/{car_slug}', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/booking', [BookingController::class, 'store'])->name('bookings.store');
Route::get('/booking/payment/{booking_number}', [BookingController::class, 'payment'])->name('bookings.payment');
Route::post('/booking/payment/{booking_number}', [BookingController::class, 'processPayment'])->name('bookings.process-payment');
Route::get('/booking/confirmation/{booking_number}', [BookingController::class, 'confirmation'])->name('bookings.confirmation');

// Blog routes
Route::get('/blogs', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blogs/load-more', [BlogController::class, 'loadMorePosts'])->name('blogs.load-more');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blogs/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blogs/tag/{tag}', [BlogController::class, 'tag'])->name('blog.tag');

// Blog comments AJAX routes
Route::post('/blogs/{slug}/comment', [BlogCommentController::class, 'store'])->name('blog.comment.store');
Route::post('/blogs/{slug}/comment/{comment}/reply', [BlogCommentController::class, 'reply'])->name('blog.comment.reply');
Route::get('/blogs/{slug}/comments', [BlogCommentController::class, 'getComments'])->name('blog.comments.get');

// Category routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/api/categories/meta/{slug}', [CategoryController::class, 'getMeta'])->name('categories.meta');
Route::get('/api/categories/featured', [CategoryController::class, 'featured'])->name('categories.featured');

// Reviews
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// Testimonial Frontend Routes
Route::get('testimonials', [TestimonialFrontController::class, 'index'])->name('testimonials');
Route::get('testimonials/create', function() {
    return view('site.testimonial-form');
})->name('testimonials.create');
Route::post('testimonials/submit', [TestimonialFrontController::class, 'store'])->name('testimonials.submit');
Route::post('testimonials/submit-ajax', [HomeController::class, 'submitTestimonialAjax'])->name('testimonials.submit.ajax');

// Newsletter Routes
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
Route::get('/newsletter/unsubscribe/{email}/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Login and Registration
Route::get('/login-register', [AuthController::class, 'showLoginRegister'])->name('login-register');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Social Authentication
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('/auth/facebook/callback', [AuthController::class, 'handleFacebookCallback']);

// Client Dashboard Routes
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::get('/profile', [ClientDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [ClientDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/update-password', [ClientDashboardController::class, 'updatePassword'])->name('password.update');
    
    // Bookings
    Route::get('/bookings', [ClientDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/bookings/{id}', [ClientDashboardController::class, 'bookingDetails'])->name('booking.details');
    Route::post('/bookings/{id}/cancel', [ClientDashboardController::class, 'cancelBooking'])->name('booking.cancel');
});

// Profile & Reservations redirect to client dashboard (for backwards compatibility)
Route::get('/profile', function() {
    return redirect()->route('client.profile');
})->name('profile')->middleware('auth');

Route::get('/reservations', function() {
    return redirect()->route('client.bookings');
})->name('reservations')->middleware('auth');

// FAQ Page
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

// Payment checkout route
Route::get('/payment/checkout/{booking}', [BookingController::class, 'checkoutPayment'])->name('payment.checkout')->middleware('auth');

// Policy Page
Route::get('/policy', [HomeController::class, 'policy'])->name('policy');
// Privacy Page
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/comment-ca-marche', function () {
    return view('site.how-it-works');
})->name('how-it-works');
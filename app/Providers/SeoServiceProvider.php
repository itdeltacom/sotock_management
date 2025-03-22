<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SeoServiceProvider extends ServiceProvider
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
        View::composer('*', function ($view) {
            $seoData = [
                'title' => config('app.name') . ' - Premium Car Rental Services',
                'description' => 'Cental offers premium car rental services with a wide selection of vehicles at competitive prices.',
                'keywords' => 'car rental, vehicle hire, premium cars, rent a car',
                'ogImage' => asset('site/img/carousel-1.jpg'),
                'canonical' => url()->current(),
            ];
            
            $view->with('seo', $seoData);
        });
    }
}
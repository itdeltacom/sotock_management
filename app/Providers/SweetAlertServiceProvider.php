<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SweetAlertServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('sweet-alert', function () {
            return new \App\Helpers\SweetAlert();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Include SweetAlert2 CSS in the head section
        $this->includeStyles();
        
        // Include SweetAlert2 JS scripts
        $this->includeScripts();
        
        // Add the SweetAlert2 session flash view composer
        $this->setupViewComposer();
    }

    /**
     * Include SweetAlert2 CSS styles
     */
    protected function includeStyles(): void
    {
        // Add SweetAlert2 CSS from CDN
        $this->app['view']->composer('*', function ($view) {
            $styles = '
                <!-- SweetAlert2 CSS -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
                <style>
                    :root {
                        --swal2-primary: #2D3FE0;
                        --swal2-success: #34D399;
                        --swal2-warning: #FBBF24;
                        --swal2-error: #EF4444;
                        --swal2-info: #2D3FE0;
                        --swal2-accent: #FF7D3B;
                    }
                    
                    .colored-toast.swal2-icon-success {
                        background-color: rgba(52, 211, 153, 0.9) !important;
                    }
                    .colored-toast.swal2-icon-error {
                        background-color: rgba(239, 68, 68, 0.9) !important;
                    }
                    .colored-toast.swal2-icon-warning {
                        background-color: rgba(251, 191, 36, 0.9) !important;
                    }
                    .colored-toast.swal2-icon-info {
                        background-color: rgba(45, 63, 224, 0.9) !important;
                    }
                    
                    .colored-toast .swal2-title {
                        color: white !important;
                    }
                    .colored-toast .swal2-html-container {
                        color: rgba(255, 255, 255, 0.8) !important;
                    }
                    
                    .swal2-popup {
                        font-family: var(--font-sans, "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif) !important;
                        border-radius: var(--radius-lg, 0.75rem) !important;
                    }
                    
                    .swal2-confirm {
                        background-color: var(--swal2-primary) !important;
                    }
                    
                    .swal2-styled.swal2-confirm:focus {
                        box-shadow: 0 0 0 3px rgba(45, 63, 224, 0.3) !important;
                    }
                </style>
            ';
            
            if (!isset($view->sweet_alert_included)) {
                $view->with('sweet_alert_styles', $styles);
                $view->with('sweet_alert_included', true);
            }
        });
    }

    /**
     * Include SweetAlert2 JavaScript
     */
    protected function includeScripts(): void
    {
        // Add SweetAlert2 JS from CDN
        $this->app['view']->composer('*', function ($view) {
            $scripts = '
                <!-- SweetAlert2 JS -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            ';
            
            if (!isset($view->sweet_alert_scripts_included)) {
                $view->with('sweet_alert_scripts', $scripts);
                $view->with('sweet_alert_scripts_included', true);
            }
        });
    }

    /**
     * Setup the view composer to display flash messages
     */
    protected function setupViewComposer(): void
    {
        $this->app['view']->composer('*', function ($view) {
            // Check for flash message in session
            if (session()->has('sweet_alert')) {
                $script = session('sweet_alert');
                $view->with('sweet_alert', $script);
            }
        });
    }
}
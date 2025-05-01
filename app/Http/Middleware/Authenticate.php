<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (! $request->expectsJson()) {
            // Check if the request is for the admin area
            if (str_starts_with($request->path(), 'admin')) {
                return route('admin.login');
            }
            
            return route('login-register');
        }
        
        return null;
    }
}
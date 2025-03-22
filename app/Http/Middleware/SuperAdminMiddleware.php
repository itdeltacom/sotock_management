<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated with admin guard
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        // Get the authenticated admin
        $admin = Auth::guard('admin')->user();

        // Check if admin has the Super Admin role
        if (!$admin->hasRole('Super Admin')) {
            // If any specific route needs a custom redirect
            if ($request->routeIs('admin.permissions.*') || $request->routeIs('admin.roles.*')) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'You do not have permission to access this resource.');
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'This action requires Super Admin privileges.');
        }

        return $next($request);
    }
}
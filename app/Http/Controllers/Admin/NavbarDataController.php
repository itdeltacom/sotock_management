<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Contract;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NavbarDataController extends Controller
{
    /**
     * Share common data with all views
     *
     * @return void
     */
    public function __construct()
    {
        // Run this method on every request in admin area
        $this->shareNavbarData();
    }

    /**
     * Share data with all views
     *
     * @return void
     */
    private function shareNavbarData()
    {
        // Only process for authenticated admin users
        if (Auth::guard('admin')->check()) {
            // Get today's date
            $today = Carbon::today();
            
            // Get available and total vehicles count
            $availableVehicles = Car::where('status', 'available')->count();
            $totalVehicles = Car::count();
            
            // Get today's bookings
            $todayBookings = Booking::whereDate('pickup_date', $today)->count();
            
            // Get pending returns (vehicles that should be returned today)
            $pendingReturns = Booking::whereDate('return_date', $today)
                ->where('status', 'active')
                ->count();
            
            // Get notification count
            $unreadNotifications = Auth::guard('admin')->user()->unreadNotifications->count();
            
            // Get 5 latest notifications
            $notifications = Auth::guard('admin')->user()
                ->notifications()
                ->latest()
                ->take(5)
                ->get();
            
            // Additional data for sidebar
            // Count of expiring documents within next 30 days
            $expiringDocsCount = Car::whereHas('documents', function($query) {
                $query->whereDate('expiry_date', '>=', Carbon::today())
                    ->whereDate('expiry_date', '<=', Carbon::today()->addDays(30));
            })->count();
            
            // Count of vehicles requiring maintenance soon
            $maintenanceDueCount = Car::where('next_maintenance_date', '<=', Carbon::today()->addDays(30))
                ->where('next_maintenance_date', '>=', Carbon::today())
                ->count();
            
            // Count of active bookings
            $activeBookingsCount = Booking::where('status', 'active')->count();
            
            // Count of contracts ending within next 7 days
            $contractsEndingSoon = Contract::whereDate('end_date', '>=', Carbon::today())
                ->whereDate('end_date', '<=', Carbon::today()->addDays(7))
                ->count();
                
            // Count of overdue contracts
            $overdueContracts = Contract::whereDate('end_date', '<', Carbon::today())
                ->where('status', '!=', 'completed')
                ->count();
            
            // Share all this data with all views
            View::share([
                'availableVehicles' => $availableVehicles,
                'totalVehicles' => $totalVehicles,
                'todayBookings' => $todayBookings,
                'pendingReturns' => $pendingReturns,
                'unreadNotifications' => $unreadNotifications,
                'notifications' => $notifications,
                'expiringDocsCount' => $expiringDocsCount,
                'maintenanceDueCount' => $maintenanceDueCount,
                'activeBookingsCount' => $activeBookingsCount,
                'contractsEndingSoon' => $contractsEndingSoon,
                'overdueContracts' => $overdueContracts,
            ]);
        }
    }
}
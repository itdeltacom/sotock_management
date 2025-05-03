<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // User must have permission to access dashboard
        if (!auth()->guard('admin')->user()->can('access dashboard')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Date ranges
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $previousMonth = Carbon::now()->subMonth();
        $startOfPreviousMonth = $previousMonth->startOfMonth();
        $endOfPreviousMonth = $previousMonth->endOfMonth();

        // Recent activities with user relationship
        $recentActivities = Activity::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->each(function($activity) {
                $activity->created_at_diff = $activity->created_at->diffForHumans();
            });
            
        // Activity statistics for dashboard cards
        $activityStats = [
            'totalToday' => Activity::whereDate('created_at', $today)->count(),
            'totalMonth' => Activity::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'loginCount' => Activity::where('type', 'login')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'uniqueUsers' => Activity::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where('user_type', 'App\\Models\\Admin')
                ->distinct('user_id')
                ->count('user_id')
        ];

        // Vehicle statistics
        $totalVehicles = Car::count();
        $availableVehicles = Car::where('is_available', true)->count();
        
        // Booking statistics
        $activeBookings = Booking::whereIn('status', ['pending', 'confirmed'])->count();
        $thisMonthBookings = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $totalBookings = Booking::count();
        
        // Calculate revenue
        $totalRevenue = Booking::where('status', 'completed')
            ->orWhere('status', 'confirmed')
            ->sum('total_amount');
        
        $thisMonthRevenue = Booking::where(function($query) {
                $query->where('status', 'completed')
                    ->orWhere('status', 'confirmed');
            })
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount');
            
        // Get recent bookings with car relationship
        $recentBookings = Booking::with(['car', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get activity types distribution
        $activityTypes = Activity::select('type', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
            
        // Get browser statistics
        $browserStats = Activity::select(
                DB::raw('COALESCE(JSON_EXTRACT(properties, "$.browser"), "Unknown") as browser'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'browser' => str_replace('"', '', $item->browser),
                    'count' => $item->count
                ];
            });
            
        // Get location statistics
        $locationStats = Activity::select(
                DB::raw('COALESCE(JSON_EXTRACT(properties, "$.location"), "Unknown") as location'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->groupBy('location')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'location' => str_replace('"', '', $item->location),
                    'count' => $item->count
                ];
            });

        return view('admin.dashboard', compact(
            'recentActivities',
            'activityStats',
            'activityTypes',
            'totalVehicles',
            'availableVehicles',
            'activeBookings',
            'thisMonthBookings',
            'completedBookings',
            'totalBookings',
            'totalRevenue',
            'thisMonthRevenue',
            'recentBookings',
            'browserStats',
            'locationStats'
        ));
    }

    /**
     * Get chart data for AJAX requests
     */
    public function getChartData(Request $request)
    {
        // User must have permission to access dashboard
        if (!auth()->guard('admin')->user()->can('access dashboard')) {
            abort(403, 'Unauthorized action.');
        }
        
        $period = $request->input('period', 'month');
        $data = [];
        $labels = [];
        $revenueData = [];
        $bookingData = [];
        
        // Current date for reference
        $now = Carbon::now();
        
        if ($period === 'week') {
            // Last 7 days data
            $startDate = Carbon::now()->subDays(6);
            
            for ($i = 0; $i < 7; $i++) {
                $date = clone $startDate;
                $date->addDays($i);
                $labels[] = $date->format('D');
                
                // Activity counts
                $activityCount = Activity::whereDate('created_at', $date->format('Y-m-d'))->count();
                $data[] = $activityCount;
                
                // Revenue data
                $dailyRevenue = Booking::whereDate('created_at', $date->format('Y-m-d'))
                    ->where(function($query) {
                        $query->where('status', 'completed')
                            ->orWhere('status', 'confirmed');
                    })
                    ->sum('total_amount');
                $revenueData[] = round($dailyRevenue, 2);
                
                // Booking counts
                $bookingCount = Booking::whereDate('created_at', $date->format('Y-m-d'))->count();
                $bookingData[] = $bookingCount;
            }
        } elseif ($period === 'month') {
            // Last 30 days data
            $startDate = Carbon::now()->subDays(29);
            
            for ($i = 0; $i < 30; $i++) {
                $date = clone $startDate;
                $date->addDays($i);
                $labels[] = $date->format('j'); // Day of month without leading zeros
                
                // Activity counts
                $activityCount = Activity::whereDate('created_at', $date->format('Y-m-d'))->count();
                $data[] = $activityCount;
                
                // Revenue data
                $dailyRevenue = Booking::whereDate('created_at', $date->format('Y-m-d'))
                    ->where(function($query) {
                        $query->where('status', 'completed')
                            ->orWhere('status', 'confirmed');
                    })
                    ->sum('total_amount');
                $revenueData[] = round($dailyRevenue, 2);
                
                // Booking counts
                $bookingCount = Booking::whereDate('created_at', $date->format('Y-m-d'))->count();
                $bookingData[] = $bookingCount;
            }
        } else {
            // Monthly data for the current year
            $year = Carbon::now()->year;
            
            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::createFromDate($year, $month, 1);
                $labels[] = $date->format('M');
                
                // Activity counts
                $activityCount = Activity::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count();
                $data[] = $activityCount;
                
                // Revenue data
                $monthlyRevenue = Booking::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where(function($query) {
                        $query->where('status', 'completed')
                            ->orWhere('status', 'confirmed');
                    })
                    ->sum('total_amount');
                $revenueData[] = round($monthlyRevenue, 2);
                
                // Booking counts
                $bookingCount = Booking::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count();
                $bookingData[] = $bookingCount;
            }
        }
        
        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'revenueData' => $revenueData,
            'bookingData' => $bookingData
        ]);
    }

/**
 * Get maintenance alerts for dashboard
 *
 * @return array
 */
public function getMaintenanceAlerts()
{
    // Get all maintenance records due soon or overdue
    $maintenances = \App\Models\CarMaintenance::with('car')
        ->where(function($query) {
            $query->whereDate('next_due_date', '<=', now()->addDays(15))
                ->orWhereRaw('next_due_mileage - (SELECT mileage FROM cars WHERE cars.id = car_maintenances.car_id) <= 500');
        })
        ->get();
    
    // Group by urgency
    $overdue = $maintenances->filter(function($maintenance) {
        return $maintenance->isOverdue();
    });
    
    $dueThisWeek = $maintenances->filter(function($maintenance) {
        if ($maintenance->isOverdue()) {
            return false;
        }
        
        $daysLeft = $maintenance->next_due_date ? now()->diffInDays($maintenance->next_due_date, false) : null;
        $kmLeft = $maintenance->next_due_mileage && $maintenance->car ? $maintenance->next_due_mileage - $maintenance->car->mileage : null;
        
        return ($daysLeft !== null && $daysLeft <= 7) || ($kmLeft !== null && $kmLeft <= 200);
    });
    
    $comingUp = $maintenances->filter(function($maintenance) {
        if ($maintenance->isOverdue()) {
            return false;
        }
        
        $daysLeft = $maintenance->next_due_date ? now()->diffInDays($maintenance->next_due_date, false) : null;
        $kmLeft = $maintenance->next_due_mileage && $maintenance->car ? $maintenance->next_due_mileage - $maintenance->car->mileage : null;
        
        return !(($daysLeft !== null && $daysLeft <= 7) || ($kmLeft !== null && $kmLeft <= 200));
    });
    
    // Format for dashboard display
    $formattedOverdue = $this->formatMaintenanceAlerts($overdue);
    $formattedDueThisWeek = $this->formatMaintenanceAlerts($dueThisWeek);
    $formattedComingUp = $this->formatMaintenanceAlerts($comingUp);
    
    return [
        'overdue' => [
            'count' => $overdue->count(),
            'items' => $formattedOverdue
        ],
        'due_this_week' => [
            'count' => $dueThisWeek->count(),
            'items' => $formattedDueThisWeek
        ],
        'coming_up' => [
            'count' => $comingUp->count(),
            'items' => $formattedComingUp
        ]
    ];
}

/**
 * Format maintenance alerts for dashboard display
 *
 * @param \Illuminate\Support\Collection $maintenances
 * @return array
 */
private function formatMaintenanceAlerts($maintenances)
{
    return $maintenances->map(function($maintenance) {
        $daysLeft = $maintenance->next_due_date ? now()->diffInDays($maintenance->next_due_date, false) : null;
        $kmLeft = $maintenance->next_due_mileage && $maintenance->car ? $maintenance->next_due_mileage - $maintenance->car->mileage : null;
        
        return [
            'id' => $maintenance->id,
            'car_id' => $maintenance->car_id,
            'car_name' => $maintenance->car->brand_name . ' ' . $maintenance->car->model,
            'matricule' => $maintenance->car->matricule,
            'maintenance_type' => ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)),
            'days_left' => $daysLeft,
            'km_left' => $kmLeft,
            'days_text' => $daysLeft !== null ? ($daysLeft < 0 ? 'Overdue by ' . abs($daysLeft) . ' days' : $daysLeft . ' days left') : null,
            'km_text' => $kmLeft !== null ? ($kmLeft < 0 ? 'Overdue by ' . abs($kmLeft) . ' km' : $kmLeft . ' km left') : null,
            'url' => route('admin.cars.maintenance.index', $maintenance->car_id)
        ];
    })->take(5)->toArray(); // Limit to 5 items for dashboard
}

/**
 * Add maintenance alerts to dashboard data
 * 
 * @param array $data Current dashboard data
 * @return array Updated dashboard data with maintenance alerts
 */
public function addMaintenanceAlertsToDashboard($data)
{
    // Get maintenance alerts
    $maintenanceAlerts = $this->getMaintenanceAlerts();
    
    // Add to data
    $data['maintenance_alerts'] = $maintenanceAlerts;
    
    return $data;
}
}
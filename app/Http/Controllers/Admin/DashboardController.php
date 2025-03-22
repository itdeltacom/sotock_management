<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Vehicle;
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
        // Date ranges
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $previousMonth = Carbon::now()->subMonth();
        $startOfPreviousMonth = $previousMonth->startOfMonth();
        $endOfPreviousMonth = $previousMonth->endOfMonth();

        // Total bookings with trend
      /*  $totalBookings = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
        $previousMonthBookings = Booking::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])->count();
        $bookingTrend = $previousMonthBookings > 0 
            ? round((($totalBookings - $previousMonthBookings) / $previousMonthBookings) * 100) 
            : 100;

        // Total revenue with trend
        $totalRevenue = Booking::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'confirmed')
            ->sum('total_amount');
        $previousMonthRevenue = Booking::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->where('status', 'confirmed')
            ->sum('total_amount');
        $revenueTrend = $previousMonthRevenue > 0 
            ? round((($totalRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100) 
            : 100;

        // Vehicle statistics
        $totalVehicles = Vehicle::count();
        $availableVehicles = Vehicle::where('status', 'available')->count();

        // Customer statistics
        $activeCustomers = Customer::where('status', 'active')->count();
        $newCustomers = Customer::where('status', 'active')
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Recent bookings
        $recentBookings = Booking::with(['customer', 'vehicle'])
            ->select(
                'bookings.*', 
                'customers.name as customer_name', 
                'vehicles.model as vehicle_name'
            )
            ->join('customers', 'bookings.customer_id', '=', 'customers.id')
            ->join('vehicles', 'bookings.vehicle_id', '=', 'vehicles.id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();*/

        // Recent activity
        $recentActivities = Activity::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Revenue chart data
       // $revenueChart = $this->getRevenueChartData('month');

        // Booking sources data
        //$bookingSources = $this->getBookingSourcesData();

        return view('admin.dashboard', compact(
            //'totalBookings',
            //'bookingTrend',
            //'totalRevenue',
            //'revenueTrend',
            //'totalVehicles',
            //'availableVehicles',
            //'activeCustomers',
           // 'newCustomers',
            //'recentBookings',
            'recentActivities',
            //'revenueChart',
            //'bookingSources'
        ));
    }

    /**
     * Get revenue chart data
     */
    /*private function getRevenueChartData($period = 'month')
    {
        $labels = [];
        $data = [];

        if ($period === 'week') {
            // Last 7 days data
            $startDate = Carbon::now()->subDays(6);
            $endDate = Carbon::now();

            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $labels[] = $date->format('D');
                $data[] = Booking::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'confirmed')
                    ->sum('total_amount');
            }
        } elseif ($period === 'month') {
            // Monthly data for the current year
            $currentYear = Carbon::now()->year;
            $startDate = Carbon::createFromDate($currentYear, 1, 1);
            $endDate = Carbon::createFromDate($currentYear, 12, 31);

            for ($month = 1; $month <= 12; $month++) {
                $labels[] = Carbon::createFromDate($currentYear, $month, 1)->format('M');
                $data[] = Booking::whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'confirmed')
                    ->sum('total_amount');
            }
        } else {
            // Yearly data for the past 5 years
            $currentYear = Carbon::now()->year;
            $startYear = $currentYear - 4;

            for ($year = $startYear; $year <= $currentYear; $year++) {
                $labels[] = (string) $year;
                $data[] = Booking::whereYear('created_at', $year)
                    ->where('status', 'confirmed')
                    ->sum('total_amount');
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }*/

    /**
     * Get booking sources data
     */
    /*private function getBookingSourcesData()
    {
        $sources = DB::table('bookings')
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->get();

        $labels = [];
        $data = [];

        foreach ($sources as $source) {
            $labels[] = ucfirst($source->source);
            $data[] = $source->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }*/

    /**
     * Get chart data for AJAX requests
     */
    public function getChartData(Request $request)
    {
        $period = $request->input('period', 'month');
        return response()->json($this->getRevenueChartData($period));
    }
}
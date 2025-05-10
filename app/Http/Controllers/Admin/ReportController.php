<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Contract;
use App\Models\Car;
use App\Models\User;
use App\Models\CarMaintenance;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReportController extends Controller
{
    /**
     * Show revenue report
     */
    public function revenue(Request $request)
    {
        // Default date range (current month)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get revenue data grouped by date
        $revenueData = $this->getRevenueData($startDate, $endDate);
        
        // Get top earning cars
        $topCars = $this->getTopEarningCars($startDate, $endDate);
        
        // Get payment method breakdown
        $paymentMethods = $this->getPaymentMethodBreakdown($startDate, $endDate);
        
        // Get statistics
        $stats = $this->getRevenueStats($startDate, $endDate);
        
        return view('admin.reports.revenue', compact(
            'revenueData', 
            'topCars', 
            'paymentMethods', 
            'stats', 
            'startDate', 
            'endDate'
        ));
    }
    
    /**
     * Export revenue report
     */
    public function exportRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $format = $request->input('format', 'csv');
        
        // Get revenue data
        $revenueData = $this->getRevenueData($startDate, $endDate, false);
        
        // Based on requested format
        switch($format) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.export.revenue-pdf', [
                    'data' => $revenueData,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'stats' => $this->getRevenueStats($startDate, $endDate)
                ]);
                return $pdf->download('revenue-report-' . date('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(new \App\Exports\RevenueExport($revenueData, $startDate, $endDate), 
                    'revenue-report-' . date('Y-m-d') . '.xlsx');
                
            default: // CSV
                return $this->downloadCsv($revenueData, 'revenue-report-' . date('Y-m-d'));
        }
    }

    /**
     * Show bookings report
     */
    public function bookings(Request $request)
    {
        // Default date range (current month)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get booking data by date
        $bookingData = $this->getBookingData($startDate, $endDate);
        
        // Get status breakdown
        $statusBreakdown = $this->getBookingStatusBreakdown($startDate, $endDate);
        
        // Get popular cars
        $popularCars = $this->getPopularCars($startDate, $endDate);
        
        // Get time of day breakdown
        $timeDistribution = $this->getBookingTimeDistribution($startDate, $endDate);
        
        // Get statistics
        $stats = $this->getBookingStats($startDate, $endDate);
        
        return view('admin.reports.bookings', compact(
            'bookingData', 
            'statusBreakdown', 
            'popularCars', 
            'timeDistribution',
            'stats', 
            'startDate', 
            'endDate'
        ));
    }
    
    /**
     * Export bookings report
     */
    public function exportBookings(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $format = $request->input('format', 'csv');
        
        // Get booking data
        $bookings = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['car', 'user'])
            ->get();
        
        // Based on requested format
        switch($format) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.export.bookings-pdf', [
                    'bookings' => $bookings,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'stats' => $this->getBookingStats($startDate, $endDate)
                ]);
                return $pdf->download('bookings-report-' . date('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(new \App\Exports\BookingsExport($bookings, $startDate, $endDate), 
                    'bookings-report-' . date('Y-m-d') . '.xlsx');
                
            default: // CSV
                return $this->exportBookingsCsv($bookings, 'bookings-report-' . date('Y-m-d'));
        }
    }

    /**
     * Show vehicles report
     */
    public function vehicles(Request $request)
    {
        // Default date range (current month)
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get vehicle utilization data
        $vehicleUtilization = $this->getVehicleUtilization($startDate, $endDate);
        
        // Get maintenance costs
        $maintenanceCosts = $this->getMaintenanceCosts($startDate, $endDate);
        
        // Get revenue by vehicle category
        $categoryRevenue = $this->getCategoryRevenue($startDate, $endDate);
        
        // Get mileage data
        $mileageData = $this->getMileageData($startDate, $endDate);
        
        // Get statistics
        $stats = $this->getVehicleStats($startDate, $endDate);
        
        return view('admin.reports.vehicles', compact(
            'vehicleUtilization', 
            'maintenanceCosts', 
            'categoryRevenue', 
            'mileageData',
            'stats', 
            'startDate', 
            'endDate'
        ));
    }
    
    /**
     * Export vehicles report
     */
    public function exportVehicles(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $format = $request->input('format', 'csv');
        
        // Get vehicle data with utilization
        $vehicles = $this->getVehicleDataForExport($startDate, $endDate);
        
        // Based on requested format
        switch($format) {
            case 'pdf':
                $pdf = PDF::loadView('admin.reports.export.vehicles-pdf', [
                    'vehicles' => $vehicles,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'stats' => $this->getVehicleStats($startDate, $endDate)
                ]);
                return $pdf->download('vehicles-report-' . date('Y-m-d') . '.pdf');
                
            case 'excel':
                return Excel::download(new \App\Exports\VehiclesExport($vehicles, $startDate, $endDate), 
                    'vehicles-report-' . date('Y-m-d') . '.xlsx');
                
            default: // CSV
                return $this->exportVehiclesCsv($vehicles, 'vehicles-report-' . date('Y-m-d'));
        }
    }

    /**
     * Get revenue data grouped by date
     */
    private function getRevenueData($startDate, $endDate, $groupByDay = true)
    {
        $query = Payment::whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59']);
        
        if ($groupByDay) {
            return $query->select(
                    DB::raw('DATE(payment_date) as date'),
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy(DB::raw('DATE(payment_date)'))
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('Y-m-d'),
                        'total' => $item->total
                    ];
                });
        } else {
            return $query->with(['contract', 'contract.car', 'contract.client'])
                ->orderBy('payment_date')
                ->get();
        }
    }
    
    /**
     * Get top earning cars
     */
    private function getTopEarningCars($startDate, $endDate, $limit = 5)
    {
        return Car::select('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->selectRaw('SUM(payments.amount) as total_revenue')
            ->join('contracts', 'cars.id', '=', 'contracts.car_id')
            ->join('payments', 'contracts.id', '=', 'payments.contract_id')
            ->whereBetween('payments.payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get payment method breakdown
     */
    private function getPaymentMethodBreakdown($startDate, $endDate)
    {
        return Payment::whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();
    }
    
    /**
     * Get revenue statistics
     */
    private function getRevenueStats($startDate, $endDate)
    {
        // Total revenue
        $totalRevenue = Payment::whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->sum('amount');
        
        // Previous period (same duration, previous time period)
        $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $prevStartDate = Carbon::parse($startDate)->subDays($daysCount)->format('Y-m-d');
        $prevEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        
        $previousRevenue = Payment::whereBetween('payment_date', [$prevStartDate, $prevEndDate . ' 23:59:59'])
            ->sum('amount');
        
        // Calculate change percentage
        $percentChange = 0;
        if ($previousRevenue > 0) {
            $percentChange = (($totalRevenue - $previousRevenue) / $previousRevenue) * 100;
        } elseif ($totalRevenue > 0) {
            $percentChange = 100;
        }
        
        // Count number of payments
        $paymentsCount = Payment::whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->count();
        
        // Average payment amount
        $avgPayment = $paymentsCount > 0 ? $totalRevenue / $paymentsCount : 0;
        
        // Return statistics
        return [
            'total_revenue' => $totalRevenue,
            'previous_revenue' => $previousRevenue,
            'percent_change' => $percentChange,
            'payments_count' => $paymentsCount,
            'avg_payment' => $avgPayment,
            'days_count' => $daysCount,
            'daily_average' => $daysCount > 0 ? $totalRevenue / $daysCount : 0
        ];
    }
    
    /**
     * Get booking data grouped by date
     */
    private function getBookingData($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('Y-m-d'),
                    'count' => $item->count
                ];
            });
    }
    
    /**
     * Get booking status breakdown
     */
    private function getBookingStatusBreakdown($startDate, $endDate)
    {
        return Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();
    }
    
    /**
     * Get popular cars based on bookings
     */
    private function getPopularCars($startDate, $endDate, $limit = 5)
    {
        return Car::select('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->selectRaw('COUNT(bookings.id) as booking_count')
            ->join('bookings', 'cars.id', '=', 'bookings.car_id')
            ->whereBetween('bookings.created_at', [$startDate, $endDate . ' 23:59:59'])
            ->groupBy('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->orderByDesc('booking_count')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get booking time distribution (morning, afternoon, evening, night)
     */
    private function getBookingTimeDistribution($startDate, $endDate)
    {
        $morning = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereRaw('HOUR(created_at) BETWEEN 6 AND 11')
            ->count();
            
        $afternoon = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereRaw('HOUR(created_at) BETWEEN 12 AND 17')
            ->count();
            
        $evening = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereRaw('HOUR(created_at) BETWEEN 18 AND 21')
            ->count();
            
        $night = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->whereRaw('(HOUR(created_at) BETWEEN 22 AND 23) OR (HOUR(created_at) BETWEEN 0 AND 5)')
            ->count();
            
        return [
            ['name' => 'Morning (6AM-12PM)', 'count' => $morning],
            ['name' => 'Afternoon (12PM-6PM)', 'count' => $afternoon],
            ['name' => 'Evening (6PM-10PM)', 'count' => $evening],
            ['name' => 'Night (10PM-6AM)', 'count' => $night]
        ];
    }
    
    /**
     * Get booking statistics
     */
    private function getBookingStats($startDate, $endDate)
    {
        // Total bookings
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();
        
        // Previous period (same duration, previous time period)
        $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $prevStartDate = Carbon::parse($startDate)->subDays($daysCount)->format('Y-m-d');
        $prevEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        
        $previousBookings = Booking::whereBetween('created_at', [$prevStartDate, $prevEndDate . ' 23:59:59'])
            ->count();
        
        // Calculate change percentage
        $percentChange = 0;
        if ($previousBookings > 0) {
            $percentChange = (($totalBookings - $previousBookings) / $previousBookings) * 100;
        } elseif ($totalBookings > 0) {
            $percentChange = 100;
        }
        
        // Booking completion rate
        $completedBookings = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('status', 'completed')
            ->count();
            
        $completionRate = $totalBookings > 0 ? ($completedBookings / $totalBookings) * 100 : 0;
        
        // Booking cancellation rate
        $cancelledBookings = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->where('status', 'cancelled')
            ->count();
            
        $cancellationRate = $totalBookings > 0 ? ($cancelledBookings / $totalBookings) * 100 : 0;
        
        // Average booking value
        $totalBookingValue = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->sum('total_amount');
            
        $avgBookingValue = $totalBookings > 0 ? $totalBookingValue / $totalBookings : 0;
        
        // Return statistics
        return [
            'total_bookings' => $totalBookings,
            'previous_bookings' => $previousBookings,
            'percent_change' => $percentChange,
            'completion_rate' => $completionRate,
            'cancellation_rate' => $cancellationRate,
            'avg_booking_value' => $avgBookingValue,
            'days_count' => $daysCount,
            'daily_average' => $daysCount > 0 ? $totalBookings / $daysCount : 0
        ];
    }
    
    /**
     * Get vehicle utilization data
     */
    private function getVehicleUtilization($startDate, $endDate)
    {
        // Get all cars
        $cars = Car::select('id', 'name', 'brand_name', 'model', 'matricule')->get();
        
        // Total days in period
        $daysInPeriod = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        
        // Calculate utilization for each car
        return $cars->map(function ($car) use ($startDate, $endDate, $daysInPeriod) {
            // Count days the car was rented in this period
            $rentedDays = DB::table('bookings')
                ->where('car_id', $car->id)
                ->where('status', 'in:in_progress,completed')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('pickup_date', [$startDate, $endDate])
                        ->orWhereBetween('dropoff_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('pickup_date', '<', $startDate)
                                ->where('dropoff_date', '>', $endDate);
                        });
                })
                ->sum(DB::raw('DATEDIFF(LEAST(dropoff_date, "' . $endDate . '"), GREATEST(pickup_date, "' . $startDate . '"))'));
            
            // Calculate utilization percentage
            $utilization = $daysInPeriod > 0 ? ($rentedDays / $daysInPeriod) * 100 : 0;
            
            return [
                'car' => $car,
                'utilization' => $utilization,
                'rented_days' => $rentedDays,
                'total_days' => $daysInPeriod
            ];
        })
        ->sortByDesc('utilization')
        ->values();
    }
    
    /**
     * Get maintenance costs
     */
    private function getMaintenanceCosts($startDate, $endDate)
    {
        return Car::select('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->selectRaw('SUM(car_maintenances.cost) as total_cost')
            ->selectRaw('COUNT(car_maintenances.id) as maintenance_count')
            ->join('car_maintenances', 'cars.id', '=', 'car_maintenances.car_id')
            ->whereBetween('car_maintenances.date_performed', [$startDate, $endDate])
            ->groupBy('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule')
            ->orderByDesc('total_cost')
            ->get();
    }
    
    /**
     * Get revenue by vehicle category
     */
    private function getCategoryRevenue($startDate, $endDate)
{
    return DB::table('categories')
        ->select('categories.name')
        ->selectRaw('SUM(payments.amount) as total_revenue')
        ->selectRaw('COUNT(DISTINCT cars.id) as car_count')
        ->join('cars', 'categories.id', '=', 'cars.category_id')
        ->join('contracts', 'cars.id', '=', 'contracts.car_id')
        ->join('payments', 'contracts.id', '=', 'payments.contract_id')
        ->whereBetween('payments.payment_date', [$startDate, $endDate . ' 23:59:59'])
        ->groupBy('categories.name')
        ->orderByDesc('total_revenue')
        ->get();
}
    
    /**
     * Get mileage data for vehicles
     */
    private function getMileageData($startDate, $endDate)
    {
        // Get cars with completed bookings in this period
        return Car::select('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule', 'cars.mileage')
            ->selectRaw('SUM(bookings.end_mileage - bookings.start_mileage) as total_distance')
            ->selectRaw('COUNT(bookings.id) as booking_count')
            ->join('bookings', 'cars.id', '=', 'bookings.car_id')
            ->where('bookings.status', 'completed')
            ->whereNotNull('bookings.start_mileage')
            ->whereNotNull('bookings.end_mileage')
            ->whereBetween('bookings.dropoff_date', [$startDate, $endDate])
            ->groupBy('cars.id', 'cars.name', 'cars.brand_name', 'cars.model', 'cars.matricule', 'cars.mileage')
            ->orderByDesc('total_distance')
            ->get();
    }
    
    /**
     * Get vehicle statistics
     */
    private function getVehicleStats($startDate, $endDate)
    {
        // Total active vehicles
        $totalVehicles = Car::count();
        $activeVehicles = Car::where('status', 'available')->count();
        
        // Maintenance stats
        $maintenanceCount = CarMaintenance::whereBetween('date_performed', [$startDate, $endDate])
            ->count();
        $maintenanceCost = CarMaintenance::whereBetween('date_performed', [$startDate, $endDate])
            ->sum('cost');
        
        // Bookings per vehicle
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->count();
        $bookingsPerVehicle = $totalVehicles > 0 ? $totalBookings / $totalVehicles : 0;
        
        // Revenue per vehicle
        $totalRevenue = Payment::whereBetween('payment_date', [$startDate, $endDate . ' 23:59:59'])
            ->sum('amount');
        $revenuePerVehicle = $totalVehicles > 0 ? $totalRevenue / $totalVehicles : 0;
        
        // Average distance per vehicle
        $totalDistance = Booking::where('status', 'completed')
            ->whereNotNull('start_mileage')
            ->whereNotNull('end_mileage')
            ->whereBetween('dropoff_date', [$startDate, $endDate])
            ->sum(DB::raw('end_mileage - start_mileage'));
        $averageDistance = $totalVehicles > 0 ? $totalDistance / $totalVehicles : 0;
        
        // Return statistics
        return [
            'total_vehicles' => $totalVehicles,
            'active_vehicles' => $activeVehicles,
            'maintenance_count' => $maintenanceCount,
            'maintenance_cost' => $maintenanceCost,
            'bookings_per_vehicle' => $bookingsPerVehicle,
            'revenue_per_vehicle' => $revenuePerVehicle,
            'average_distance' => $averageDistance,
            'total_distance' => $totalDistance
        ];
    }
    
    /**
     * Get vehicles data for export
     */
    private function getVehicleDataForExport($startDate, $endDate)
    {
        $cars = Car::with(['category', 'brand'])
            ->withCount(['bookings' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
            }])
            ->get();
        
        return $cars->map(function ($car) use ($startDate, $endDate) {
            // Calculate revenue
            $revenue = DB::table('bookings')
                ->join('contracts', 'bookings.id', '=', 'contracts.booking_id')
                ->join('payments', 'contracts.id', '=', 'payments.contract_id')
                ->where('bookings.car_id', $car->id)
                ->whereBetween('payments.payment_date', [$startDate, $endDate . ' 23:59:59'])
                ->sum('payments.amount');
                
            // Calculate maintenance cost
            $maintenanceCost = CarMaintenance::where('car_id', $car->id)
                ->whereBetween('date_performed', [$startDate, $endDate])
                ->sum('cost');
                
            // Calculate total distance
            $totalDistance = Booking::where('car_id', $car->id)
                ->where('status', 'completed')
                ->whereNotNull('start_mileage')
                ->whereNotNull('end_mileage')
                ->whereBetween('dropoff_date', [$startDate, $endDate])
                ->sum(DB::raw('end_mileage - start_mileage'));
                
            return [
                'car' => $car,
                'revenue' => $revenue,
                'maintenance_cost' => $maintenanceCost,
                'net_revenue' => $revenue - $maintenanceCost,
                'total_distance' => $totalDistance
            ];
        });
    }
    
    /**
     * Helper method to download CSV
     */
    private function downloadCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Pragma' => 'no-cache',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            // Add headers
            fputcsv($file, ['Date', 'Amount', 'Type', 'Reference', 'Customer', 'Vehicle']);
            
            // Add data
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->payment_date,
                    $row->amount,
                    $row->payment_method,
                    $row->reference ?? 'N/A',
                    $row->contract && $row->contract->client ? $row->contract->client->name : 'N/A',
                    $row->contract && $row->contract->car ? $row->contract->car->brand_name . ' ' . $row->contract->car->model : 'N/A'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export vehicles as CSV
     */
    private function exportVehiclesCsv($vehicles, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Pragma' => 'no-cache',
        ];

        $callback = function() use ($vehicles) {
            $file = fopen('php://output', 'w');
            // Add headers
            fputcsv($file, [
                'Vehicle ID', 'Brand', 'Model', 'License Plate', 'Current Mileage',
                'Bookings Count', 'Revenue', 'Maintenance Cost', 'Net Revenue', 'Total Distance (km)'
            ]);
            
            // Add data
            foreach ($vehicles as $item) {
                $car = $item['car'];
                fputcsv($file, [
                    $car->id,
                    $car->brand_name,
                    $car->model,
                    $car->matricule,
                    number_format($car->mileage),
                    $car->bookings_count,
                    number_format($item['revenue'], 2),
                    number_format($item['maintenance_cost'], 2),
                    number_format($item['net_revenue'], 2),
                    number_format($item['total_distance'])
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    }
    
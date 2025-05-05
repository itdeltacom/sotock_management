<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings.
     */
    public function index()
    {
        $cars = Car::orderBy('name')->get();

        // Calculate statistics for the dashboard cards
        $statistics = [];

        // Total bookings
        $statistics['total_bookings'] = Booking::count();
        $lastMonthTotal = Booking::where('created_at', '>=', Carbon::now()->subMonth())
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->count();
        $currentMonthTotal = Booking::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $statistics['total_bookings_change'] = $lastMonthTotal > 0
            ? round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 2)
            : ($currentMonthTotal > 0 ? 100 : 0);

        // Active bookings (pending or confirmed)
        $statistics['active_bookings'] = Booking::whereIn('status', ['pending', 'confirmed'])->count();
        $lastWeekActive = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '>=', Carbon::now()->subWeek())
            ->where('created_at', '<', Carbon::now()->startOfWeek())
            ->count();
        $currentWeekActive = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->count();
        $statistics['active_bookings_change'] = $lastWeekActive > 0
            ? round((($currentWeekActive - $lastWeekActive) / $lastWeekActive) * 100, 2)
            : ($currentWeekActive > 0 ? 100 : 0);

        // Pending payments
        $statistics['pending_payments'] = Booking::where('payment_status', 'pending')->count();
        $yesterdayPending = Booking::where('payment_status', 'pending')
            ->whereDate('created_at', Carbon::yesterday())
            ->count();
        $todayPending = Booking::where('payment_status', 'pending')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $statistics['pending_payments_change'] = $yesterdayPending > 0
            ? round((($todayPending - $yesterdayPending) / $yesterdayPending) * 100, 2)
            : ($todayPending > 0 ? 100 : 0);

        // Cancelled bookings
        $statistics['cancelled_bookings'] = Booking::where('status', 'cancelled')->count();
        $lastMonthCancelled = Booking::where('status', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->count();
        $currentMonthCancelled = Booking::where('status', 'cancelled')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();
        $statistics['cancelled_bookings_change'] = $lastMonthCancelled > 0
            ? round((($currentMonthCancelled - $lastMonthCancelled) / $lastMonthCancelled) * 100, 2)
            : ($currentMonthCancelled > 0 ? 100 : 0);

        return view('admin.bookings.index', compact('cars', 'statistics'));
    }

    /**
     * Get bookings data for DataTables.
     */
    public function data(Request $request)
    {
        $query = Booking::with(['car', 'user'])
            ->select('bookings.*');
            
        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('car_id') && $request->car_id) {
            $query->where('car_id', $request->car_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        return DataTables::of($query)
            ->addColumn('action', function (Booking $booking) {
                $actions = '<div class="btn-group" role="group">';
                
                // View button
                $actions .= '<button type="button" class="btn btn-sm btn-info me-1 btn-view" data-id="'.$booking->id.'" title="View">
                    <i class="fas fa-eye"></i>
                </button> ';
                
                // Edit button
                $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$booking->id.'" title="Edit">
                    <i class="fas fa-edit"></i>
                </button> ';
                
                // Delete button
                $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$booking->id.'" data-number="'.$booking->booking_number.'" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->addColumn('car_name', function (Booking $booking) {
                return $booking->car ? $booking->car->name : 'N/A';
            })
            ->addColumn('customer', function (Booking $booking) {
                $html = $booking->customer_name;
                
                if ($booking->user) {
                    $html .= ' <span class="badge bg-info">Registered</span>';
                } else {
                    $html .= ' <span class="badge bg-secondary">Guest</span>';
                }
                
                return $html . '<br><small>'.$booking->customer_email.'</small>';
            })
            ->addColumn('booking_info', function (Booking $booking) {
                return 'Booking #: '.$booking->booking_number.'<br>
                        <small>Created: '.$booking->created_at->format('M d, Y H:i').'</small>';
            })
            ->addColumn('rental_period', function (Booking $booking) {
                return 'Pickup: '.$booking->pickup_date->format('M d, Y').' '.$booking->pickup_time.'<br>
                        Dropoff: '.$booking->dropoff_date->format('M d, Y').' '.$booking->dropoff_time.'<br>
                        <strong>Total: '.$booking->total_days.' days</strong>';
            })
            ->addColumn('amount', function (Booking $booking) {
                return '<strong>$'.number_format($booking->total_amount, 2).'</strong><br>
                        <small>Base: $'.number_format($booking->base_price, 2).' 
                        - Discount: $'.number_format($booking->discount_amount, 2).' 
                        + Tax: $'.number_format($booking->tax_amount, 2).'</small>';
            })
            ->addColumn('status_badge', function (Booking $booking) {
                $statusClasses = [
                    'pending' => 'bg-warning',
                    'confirmed' => 'bg-success',
                    'completed' => 'bg-info',
                    'cancelled' => 'bg-danger'
                ];
                
                $class = $statusClasses[$booking->status] ?? 'bg-secondary';
                
                return '<span class="badge '.$class.'">'.ucfirst($booking->status).'</span>';
            })
            ->addColumn('payment_badge', function (Booking $booking) {
                $paymentClasses = [
                    'paid' => 'bg-success',
                    'unpaid' => 'bg-danger',
                    'pending' => 'bg-warning',
                    'refunded' => 'bg-info'
                ];
                
                $class = $paymentClasses[$booking->payment_status] ?? 'bg-secondary';
                
                return '<span class="badge '.$class.'">'.ucfirst($booking->payment_status).'</span>';
            })
            ->addColumn('status_actions', function (Booking $booking) {
                $actions = '';
                
                if ($booking->status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-status" data-id="'.$booking->id.'" data-status="confirmed" title="Confirm">
                        <i class="fas fa-check"></i> Confirm
                    </button> ';
                }
                
                if ($booking->status === 'confirmed') {
                    $actions .= '<button type="button" class="btn btn-sm btn-info btn-status" data-id="'.$booking->id.'" data-status="completed" title="Complete">
                        <i class="fas fa-flag-checkered"></i> Complete
                    </button> ';
                }
                
                if ($booking->status !== 'cancelled' && $booking->status !== 'completed') {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-status" data-id="'.$booking->id.'" data-status="cancelled" title="Cancel">
                        <i class="fas fa-ban"></i> Cancel
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('payment_actions', function (Booking $booking) {
                $actions = '';
                
                if ($booking->payment_status === 'unpaid' || $booking->payment_status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-success btn-payment" data-id="'.$booking->id.'" data-status="paid" title="Mark as Paid">
                        <i class="fas fa-check"></i> Mark Paid
                    </button> ';
                }
                
                if ($booking->payment_status === 'paid') {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning btn-payment" data-id="'.$booking->id.'" data-status="refunded" title="Mark as Refunded">
                        <i class="fas fa-undo"></i> Refund
                    </button>';
                }
                
                return $actions;
            })
            ->rawColumns(['action', 'customer', 'booking_info', 'rental_period', 'amount', 'status_badge', 'payment_badge', 'status_actions', 'payment_actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $cars = Car::where('is_available', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        
        return view('admin.bookings.create', compact('cars', 'users'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'user_id' => 'nullable|exists:users,id',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:unpaid,paid,pending,refunded',
            'payment_method' => 'required|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if the car is available for the selected dates
        $isAvailable = $this->checkCarAvailability(
            $request->car_id,
            $request->pickup_date,
            $request->dropoff_date
        );
        
        if (!$isAvailable) {
            return response()->json([
                'success' => false,
                'errors' => ['car_id' => ['The selected car is not available for the chosen dates.']]
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate days and pricing
            $car = Car::findOrFail($request->car_id);
            
            $pickup = strtotime($request->pickup_date);
            $dropoff = strtotime($request->dropoff_date);
            $totalDays = max(1, ceil(($dropoff - $pickup) / (60 * 60 * 24)));
            
            $basePrice = $car->price_per_day * $totalDays;
            
            $discountAmount = 0;
            if ($car->discount_percentage > 0) {
                $discountAmount = ($basePrice * $car->discount_percentage) / 100;
            }
            
            $taxRate = config('booking.tax_rate', 10);
            $taxAmount = (($basePrice - $discountAmount) * $taxRate) / 100;
            
            $totalAmount = $basePrice - $discountAmount + $taxAmount;
            
            // Generate a unique booking number
            $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            
            $booking = Booking::create([
                'user_id' => $request->user_id,
                'car_id' => $request->car_id,
                'booking_number' => $bookingNumber,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'dropoff_date' => $request->dropoff_date,
                'dropoff_time' => $request->dropoff_time,
                'total_days' => $totalDays,
                'base_price' => $basePrice,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'special_requests' => $request->special_requests,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'transaction_id' => $request->transaction_id,
            ]);
            
            DB::commit();
            
            // Send notification email
            // $this->sendBookingNotificationEmail($booking);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
                'booking' => $booking
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin booking creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        $booking->load(['car', 'user']);
        
        return response()->json([
            'success' => true,
            'booking' => $booking
        ]);
    }

    /**
     * Display the booking calendar.
     */
    public function calendar()
    {
        $cars = Car::orderBy('name')->get();
        return view('admin.bookings.calendar', compact('cars'));
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'user_id' => 'nullable|exists:users,id',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'payment_status' => 'required|in:unpaid,paid,pending,refunded',
            'payment_method' => 'required|string|max:255',
            'transaction_id' => 'nullable|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'discount_amount' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Check if the car is available for the selected dates (excluding this booking)
        if ($booking->car_id != $request->car_id || 
            $booking->pickup_date->format('Y-m-d') != $request->pickup_date || 
            $booking->dropoff_date->format('Y-m-d') != $request->dropoff_date) {
            
            $isAvailable = $this->checkCarAvailability(
                $request->car_id,
                $request->pickup_date,
                $request->dropoff_date,
                $booking->id
            );
            
            if (!$isAvailable) {
                return response()->json([
                    'success' => false,
                    'errors' => ['car_id' => ['The selected car is not available for the chosen dates.']]
                ], 422);
            }
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate total days
            $pickup = strtotime($request->pickup_date);
            $dropoff = strtotime($request->dropoff_date);
            $totalDays = max(1, ceil(($dropoff - $pickup) / (60 * 60 * 24)));
            
            $booking->update([
                'user_id' => $request->user_id,
                'car_id' => $request->car_id,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'dropoff_date' => $request->dropoff_date,
                'dropoff_time' => $request->dropoff_time,
                'total_days' => $totalDays,
                'base_price' => $request->base_price,
                'discount_amount' => $request->discount_amount,
                'tax_amount' => $request->tax_amount,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'special_requests' => $request->special_requests,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'transaction_id' => $request->transaction_id,
            ]);
            
            DB::commit();
            
            // Send notification to user if status changed
            if ($booking->wasChanged('status') || $booking->wasChanged('payment_status')) {
                // $this->sendBookingStatusUpdateEmail($booking);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully.',
                'booking' => $booking
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin booking update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking)
    {
        try {
            // Store data for logging
            $bookingData = [
                'id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'customer_name' => $booking->customer_name,
                'car_id' => $booking->car_id
            ];
            
            $booking->delete();
            
            // Log activity if spatie activity-log package is installed
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties($bookingData)
                    ->log('Deleted booking');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Booking deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $oldStatus = $booking->status;
            
            $booking->update([
                'status' => $request->status
            ]);
            
            // Handle auto-related payment status updates
            if ($request->status === 'cancelled' && $booking->payment_status === 'paid') {
                $booking->update(['payment_status' => 'refunded']);
            }
            
            DB::commit();
            
            // Send notification to user
            // $this->sendBookingStatusUpdateEmail($booking, $oldStatus);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking status updated successfully',
                'booking' => $booking
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking status update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, Booking $booking)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|in:unpaid,paid,pending,refunded',
            'transaction_id' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $oldPaymentStatus = $booking->payment_status;
            
            $booking->update([
                'payment_status' => $request->payment_status,
                'transaction_id' => $request->transaction_id
            ]);
            
            // Auto update booking status if applicable
            if ($request->payment_status === 'paid' && $booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }
            
            DB::commit();
            
            // Send notification to user
            // $this->sendPaymentStatusUpdateEmail($booking, $oldPaymentStatus);
            
            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'booking' => $booking
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment status update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate booking prices.
     */
    public function calculatePrices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        $car = Car::findOrFail($request->car_id);
        
        // Calculate total days
        $pickup = strtotime($request->pickup_date);
        $dropoff = strtotime($request->dropoff_date);
        $totalDays = max(1, ceil(($dropoff - $pickup) / (60 * 60 * 24)));
        
        // Calculate base price
        $basePrice = $car->price_per_day * $totalDays;
        
        // Calculate discount
        $discountAmount = 0;
        if ($car->discount_percentage > 0) {
            $discountAmount = ($basePrice * $car->discount_percentage) / 100;
        }
        
        // Calculate tax
        $taxRate = config('booking.tax_rate', 10);
        $taxAmount = (($basePrice - $discountAmount) * $taxRate) / 100;
        
        // Calculate total amount
        $totalAmount = $basePrice - $discountAmount + $taxAmount;
        
        // Check availability
        $isAvailable = $this->checkCarAvailability(
            $request->car_id, 
            $request->pickup_date, 
            $request->dropoff_date,
            $request->input('booking_id')
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_days' => $totalDays,
                'base_price' => round($basePrice, 2),
                'discount_amount' => round($discountAmount, 2),
                'tax_amount' => round($taxAmount, 2),
                'total_amount' => round($totalAmount, 2),
                'is_available' => $isAvailable
            ]
        ]);
    }
    
    /**
     * Check if a car is available for the selected dates.
     */
    private function checkCarAvailability($carId, $pickupDate, $dropoffDate, $excludeBookingId = null)
    {
        $car = Car::find($carId);
        
        if (!$car || !$car->is_available) {
            return false;
        }
        
        $query = Booking::where('car_id', $carId)
            ->where(function ($query) use ($pickupDate, $dropoffDate) {
                $query->where(function ($q) use ($pickupDate, $dropoffDate) {
                    // New booking starts during an existing booking
                    $q->where('pickup_date', '<=', $pickupDate)
                      ->where('dropoff_date', '>=', $pickupDate);
                })->orWhere(function ($q) use ($pickupDate, $dropoffDate) {
                    // New booking ends during an existing booking
                    $q->where('pickup_date', '<=', $dropoffDate)
                      ->where('dropoff_date', '>=', $dropoffDate);
                })->orWhere(function ($q) use ($pickupDate, $dropoffDate) {
                    // New booking contains an existing booking
                    $q->where('pickup_date', '>=', $pickupDate)
                      ->where('dropoff_date', '<=', $dropoffDate);
                });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['paid', 'pending']);
        
        // Exclude the current booking when checking for an update
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
        
        $overlappingBookings = $query->count();
        
        return $overlappingBookings === 0;
    }
    
    /**
     * Export bookings to CSV.
     */
    public function export(Request $request)
    {
        $query = Booking::with(['car', 'user']);
            
        // Apply filters if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }
        
        if ($request->has('car_id') && $request->car_id) {
            $query->where('car_id', $request->car_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $bookings = $query->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bookings-'.date('Y-m-d').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $columns = [
            'Booking Number', 'Customer Name', 'Customer Email', 'Customer Phone',
            'Car', 'Pickup Location', 'Pickup Date', 'Pickup Time',
            'Dropoff Location', 'Dropoff Date', 'Dropoff Time', 'Total Days',
            'Base Price', 'Discount', 'Tax', 'Total Amount',
            'Status', 'Payment Status', 'Payment Method', 'Transaction ID',
            'Created At'
        ];
        
        $callback = function() use ($bookings, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($bookings as $booking) {
                $row = [
                    $booking->booking_number,
                    $booking->customer_name,
                    $booking->customer_email,
                    $booking->customer_phone,
                    $booking->car ? $booking->car->name : 'N/A',
                    $booking->pickup_location,
                    $booking->pickup_date->format('Y-m-d'),
                    $booking->pickup_time,
                    $booking->dropoff_location,
                    $booking->dropoff_date->format('Y-m-d'),
                    $booking->dropoff_time,
                    $booking->total_days,
                    $booking->base_price,
                    $booking->discount_amount,
                    $booking->tax_amount,
                    $booking->total_amount,
                    $booking->status,
                    $booking->payment_status,
                    $booking->payment_method,
                    $booking->transaction_id,
                    $booking->created_at->format('Y-m-d H:i:s')
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get dashboard statistics.
     */
    public function dashboardStats()
    {
        // Total bookings
        $totalBookings = Booking::count();
        
        // Bookings today
        $bookingsToday = Booking::whereDate('created_at', today())->count();
        
        // Pending bookings
        $pendingBookings = Booking::where('status', 'pending')->count();
        
        // Total revenue
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_amount');
        
        // Revenue today
        $revenueToday = Booking::where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('total_amount');
        
        // Recent bookings
        $recentBookings = Booking::with(['car', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Bookings by status
        $bookingsByStatus = Booking::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // Bookings by payment status
        $bookingsByPaymentStatus = Booking::selectRaw('payment_status, count(*) as count')
            ->groupBy('payment_status')
            ->get()
            ->pluck('count', 'payment_status')
            ->toArray();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_bookings' => $totalBookings,
                'bookings_today' => $bookingsToday,
                'pending_bookings' => $pendingBookings,
                'total_revenue' => $totalRevenue,
                'revenue_today' => $revenueToday,
                'recent_bookings' => $recentBookings,
                'bookings_by_status' => $bookingsByStatus,
                'bookings_by_payment_status' => $bookingsByPaymentStatus
            ]
        ]);
    }
}
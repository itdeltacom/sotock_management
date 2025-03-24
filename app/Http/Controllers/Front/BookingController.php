<?php

namespace App\Http\Controllers\Front;

use App\Models\Car;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{/**
     * Show the booking form.
     */
    public function create(Car $car)
    {
        return view('bookings.create', compact('car'));
    }

    /**
     * Initialize a booking and calculate the price.
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
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
        
        // Additional discounts can be applied here
        // e.g., for longer rentals, loyal customers, etc.
        
        // Calculate tax (assuming 10% tax)
        $taxRate = config('booking.tax_rate', 10);
        $taxAmount = (($basePrice - $discountAmount) * $taxRate) / 100;
        
        // Calculate total amount
        $totalAmount = $basePrice - $discountAmount + $taxAmount;
        
        // Check if the car is available for the selected dates
        $isAvailable = $this->checkCarAvailability($car->id, $request->pickup_date, $request->dropoff_date);
        
        return response()->json([
            'success' => true,
            'data' => [
                'car' => $car,
                'total_days' => $totalDays,
                'base_price' => $basePrice,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'tax_rate' => $taxRate,
                'total_amount' => $totalAmount,
                'is_available' => $isAvailable
            ]
        ]);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'special_requests' => 'nullable|string',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
            'total_days' => 'required|integer|min:1',
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

        // Check if the car is available
        if (!$this->checkCarAvailability($request->car_id, $request->pickup_date, $request->dropoff_date)) {
            return response()->json([
                'success' => false,
                'message' => 'The car is not available for the selected dates'
            ], 422);
        }

        // Generate a unique booking number
        $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        try {
            DB::beginTransaction();
            
            // Create the booking
            $booking = new Booking([
                'car_id' => $request->car_id,
                'booking_number' => $bookingNumber,
                'pickup_location' => $request->pickup_location,
                'dropoff_location' => $request->dropoff_location,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'dropoff_date' => $request->dropoff_date,
                'dropoff_time' => $request->dropoff_time,
                'total_days' => $request->total_days,
                'base_price' => $request->base_price,
                'discount_amount' => $request->discount_amount,
                'tax_amount' => $request->tax_amount,
                'total_amount' => $request->total_amount,
                'status' => 'pending',
                'payment_status' => $request->payment_method === 'cash_on_delivery' ? 'unpaid' : 'pending',
                'payment_method' => $request->payment_method,
                'special_requests' => $request->special_requests,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
            ]);
            
            // Associate with logged-in user if any
            if (Auth::check()) {
                $booking->user_id = Auth::id();
            }
            
            $booking->save();
            
            // Process payment if not cash on delivery
            if ($request->payment_method !== 'cash_on_delivery') {
                // If it's a real payment, we would handle it here
                // For now, we'll just simulate a successful payment
                if ($request->payment_method === 'credit_card' || $request->payment_method === 'paypal') {
                    $transactionId = 'TRANS-' . strtoupper(Str::random(10));
                    $booking->transaction_id = $transactionId;
                    $booking->payment_status = 'paid';
                    $booking->status = 'confirmed';
                    $booking->save();
                }
            }
            
            DB::commit();
            
            // Send confirmation email
            // $this->sendBookingConfirmationEmail($booking);
            
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $booking,
                'redirect' => route('bookings.thankyou', $booking->booking_number)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Booking creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the thank you page.
     */
    public function thankYou($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        return view('bookings.thankyou', compact('booking'));
    }

    /**
     * Display the booking details page.
     */
    public function show(Booking $booking)
    {
        // Check if the user is authorized to view this booking
        if (!Auth::check() || (Auth::id() !== $booking->user_id && !Auth::user()->hasRole('admin'))) {
            abort(403, 'Unauthorized');
        }
        
        return view('bookings.show', compact('booking'));
    }
    
    /**
     * Check if a car is available for the selected dates.
     */
    private function checkCarAvailability($carId, $pickupDate, $dropoffDate)
    {
        // Check if the car is marked as available
        $car = Car::find($carId);
        
        if (!$car || !$car->is_available) {
            return false;
        }
        
        // Check if there are any overlapping bookings
        $overlappingBookings = Booking::where('car_id', $carId)
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
            ->whereIn('payment_status', ['paid', 'pending'])
            ->count();
        
        return $overlappingBookings === 0;
    }
}
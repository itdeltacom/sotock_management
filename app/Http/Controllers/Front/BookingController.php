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
{
    /**
     * Calculate booking price and availability
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $car = Car::findOrFail($request->car_id);
        
        $totalDays = $this->calculateDays($request->pickup_date, $request->dropoff_date);
        $basePrice = $car->price_per_day * $totalDays;
        $discountAmount = $car->discount_percentage > 0 ? ($basePrice * $car->discount_percentage) / 100 : 0;
        $taxAmount = (($basePrice - $discountAmount) * config('booking.tax_rate', 10)) / 100;
        $totalAmount = $basePrice - $discountAmount + $taxAmount;
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_days' => $totalDays,
                'base_price' => $basePrice,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'is_available' => $this->checkCarAvailability($car->id, $request->pickup_date, $request->dropoff_date)
            ]
        ]);
    }

    /**
     * Process booking form submission
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
            'customer_phone' => 'required|string|max:20',
            'special_requests' => 'nullable|string',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $car = Car::findOrFail($request->car_id);
        $totalDays = $this->calculateDays($request->pickup_date, $request->dropoff_date);
        
        // Calculate pricing
        $basePrice = $car->price_per_day * $totalDays;
        $discountAmount = $car->discount_percentage > 0 ? ($basePrice * $car->discount_percentage) / 100 : 0;
        $taxAmount = (($basePrice - $discountAmount) * config('booking.tax_rate', 10)) / 100;
        $totalAmount = $basePrice - $discountAmount + $taxAmount;

        // Check availability
        if (!$this->checkCarAvailability($car->id, $request->pickup_date, $request->dropoff_date)) {
            return response()->json([
                'success' => false,
                'message' => 'This car is not available for the selected dates'
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            $booking = new Booking([
                'car_id' => $car->id,
                'booking_number' => 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
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
                'status' => 'pending',
                'payment_status' => $request->payment_method === 'cash_on_delivery' ? 'unpaid' : 'pending',
                'payment_method' => $request->payment_method,
                'special_requests' => $request->special_requests,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
            ]);

            if (Auth::check()) {
                $booking->user_id = Auth::id();
            }

            $booking->save();
            
            // Process payment if needed (simplified for example)
            if ($request->payment_method !== 'cash_on_delivery') {
                $booking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'transaction_id' => 'TRANS-' . strtoupper(Str::random(10))
                ]);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'booking_number' => $booking->booking_number,
                'message' => 'Your booking has been confirmed! Booking number: ' . $booking->booking_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Calculate number of days between two dates
     */
    private function calculateDays($pickupDate, $dropoffDate)
    {
        $pickup = strtotime($pickupDate);
        $dropoff = strtotime($dropoffDate);
        return max(1, ceil(($dropoff - $pickup) / (60 * 60 * 24)));
    }

    /**
     * Check car availability
     */
    private function checkCarAvailability($carId, $pickupDate, $dropoffDate)
    {
        return Booking::where('car_id', $carId)
            ->where(function ($query) use ($pickupDate, $dropoffDate) {
                $query->where(function ($q) use ($pickupDate, $dropoffDate) {
                    $q->where('pickup_date', '<=', $pickupDate)
                      ->where('dropoff_date', '>=', $pickupDate);
                })->orWhere(function ($q) use ($pickupDate, $dropoffDate) {
                    $q->where('pickup_date', '<=', $dropoffDate)
                      ->where('dropoff_date', '>=', $dropoffDate);
                })->orWhere(function ($q) use ($pickupDate, $dropoffDate) {
                    $q->where('pickup_date', '>=', $pickupDate)
                      ->where('dropoff_date', '<=', $dropoffDate);
                });
            })
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereIn('payment_status', ['paid', 'pending'])
            ->doesntExist();
    }
}
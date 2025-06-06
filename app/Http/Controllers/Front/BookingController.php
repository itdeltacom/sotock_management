<?php

namespace App\Http\Controllers\Front;

use App\Models\Car;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Display a listing of the user's bookings
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to view your bookings.');
        }
        
        $bookings = Booking::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with('car')
            ->paginate(10);
        
        return view('site.bookings.index', compact('bookings'));
    }
    
    /**
     * Display the details of a specific booking
     */
    public function show($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized to view this booking
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this booking.');
        }
        
        return view('site.bookings.show', compact('booking'));
    }
    
    /**
     * Display the confirmation page after a successful booking
     */
    public function confirmation($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['car', 'car.brand', 'car.category'])
            ->firstOrFail();
        
        // Check if user is authorized to view this booking
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this booking.');
        }
        
        return view('site.bookings.confirmation', compact('booking'));
    }

    /**
     * Calculate booking price and availability
     */
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_date' => 'required|date|after_or_equal:today',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'insurance_plan' => 'nullable|in:basic,standard,premium',
            'additional_driver' => 'nullable|boolean',
            'gps_enabled' => 'nullable|boolean',
            'child_seat' => 'nullable|boolean',
            'start_mileage' => 'nullable|integer',
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
        
        // Calculate additional costs
        $insuranceCost = 0;
        if ($request->insurance_plan === 'standard') {
            $insuranceCost = $totalDays * config('booking.insurance.standard', 50);
        } elseif ($request->insurance_plan === 'premium') {
            $insuranceCost = $totalDays * config('booking.insurance.premium', 100);
        }

        $extrasCost = 0;
        if ($request->boolean('additional_driver')) {
            $extrasCost += config('booking.additional_driver_fee', 30);
        }
        if ($request->boolean('gps_enabled')) {
            $extrasCost += config('booking.gps_fee', 20);
        }
        if ($request->boolean('child_seat')) {
            $extrasCost += config('booking.child_seat_fee', 15);
        }

        $discountAmount = $car->discount_percentage > 0 ? ($basePrice * $car->discount_percentage) / 100 : 0;
        $subtotal = $basePrice - $discountAmount + $insuranceCost + $extrasCost;
        $taxAmount = ($subtotal * config('booking.tax_rate', 10)) / 100;
        $totalAmount = $subtotal + $taxAmount;
        
        // Set mileage limit based on car configuration or default values
        $mileageLimit = $car->mileage_limit ?? config('booking.default_mileage_limit', 200);
        
        // Calculate allowed total mileage for the rental period
        $totalAllowedMileage = $mileageLimit * $totalDays;
        
        // Set extra mileage cost
        $extraMileageCost = $car->extra_mileage_cost ?? config('booking.extra_mileage_cost', 2);

        return response()->json([
            'success' => true,
            'data' => [
                'total_days' => $totalDays,
                'base_price' => $basePrice,
                'discount_amount' => $discountAmount,
                'insurance_cost' => $insuranceCost,
                'extras_cost' => $extrasCost,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'mileage_limit' => $mileageLimit,
                'total_allowed_mileage' => $totalAllowedMileage,
                'extra_mileage_cost' => $extraMileageCost,
                'is_available' => $this->checkCarAvailability($car->id, $request->pickup_date, $request->dropoff_date),
                'start_mileage' => $car->mileage,
            ]
        ]);
    }

    /**
     * Process booking form submission
     */
    public function store(Request $request)
    {
        Log::info('Booking request received', $request->all());
        
        // Add detailed debug logging for register_account
        Log::info('Register account details', [
            'has_register_account' => $request->has('register_account'),
            'register_account_value' => $request->input('register_account'),
            'register_account_boolean' => $request->boolean('register_account'),
            'register_account_type' => gettype($request->input('register_account')),
        ]);

        $validator = Validator::make($request->all(), [
            'car_id' => 'required|exists:cars,id',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|string',
            'dropoff_date' => 'required|date|after_or_equal:pickup_date',
            'dropoff_time' => 'required|string',
            'customer_name' => 'required_if:user_id,null|string|max:255',
            'customer_email' => 'required_if:user_id,null|email|max:255',
            'customer_phone' => 'required_if:user_id,null|string|max:20',
            'customer_id_number' => 'nullable|string|max:50',
            'special_requests' => 'nullable|string',
            'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
            'insurance_plan' => 'required|in:basic,standard,premium',
            'additional_driver' => 'nullable|boolean',
            'additional_driver_name' => 'required_if:additional_driver,1|string|max:255|nullable',
            'additional_driver_license' => 'required_if:additional_driver,1|string|max:50|nullable',
            'gps_enabled' => 'nullable|boolean',
            'child_seat' => 'nullable|boolean',
            'delivery_option' => 'nullable|in:none,home,airport',
            'delivery_address' => 'required_if:delivery_option,home,airport|string|max:255|nullable',
            'language_preference' => 'nullable|in:ar,fr,en',
            'start_mileage' => 'required|integer|min:0',
            'register_account' => 'nullable|boolean',
            'password' => 'required_if:register_account,1|string|min:8|confirmed|nullable',
            'password_confirmation' => 'required_if:register_account,1|string|nullable',
            'terms_accepted' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            Log::warning('Booking validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Process user account creation first if needed
        $user = Auth::user();
        if (!$user && $request->boolean('register_account')) {
            try {
                // Check for duplicate email
                if (User::where('email', $request->customer_email)->exists()) {
                    Log::warning('Duplicate email detected', ['email' => $request->customer_email]);
                    return response()->json([
                        'success' => false,
                        'message' => 'This email is already registered. Please log in or use a different email.'
                    ], 422);
                }

                $user = User::create([
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'password' => Hash::make($request->password),
                    'phone' => $request->customer_phone,
                    'id_number' => $request->customer_id_number,
                    'status' => 'active',
                ]);
                
                Auth::login($user);
                Log::info('New user created and logged in', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('User creation failed: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user account. Please try again or proceed without creating an account.'
                ], 500);
            }
        }

        // Now proceed with the booking
        try {
            DB::beginTransaction();
            Log::info('Starting booking transaction');

            // Lock the car record to prevent concurrent updates
            $car = Car::where('id', $request->car_id)->lockForUpdate()->firstOrFail();
            
            // Log car state for debugging
            Log::info('Car state before validation', [
                'car_id' => $car->id,
                'current_status' => $car->status,
                'current_is_available' => $car->is_available,
                'current_mileage' => $car->mileage
            ]);
            
            $totalDays = $this->calculateDays($request->pickup_date, $request->dropoff_date);

            // Check availability inside transaction
            if (!$this->checkCarAvailability($car->id, $request->pickup_date, $request->dropoff_date)) {
                DB::rollBack();
                Log::warning('Car not available for selected dates', [
                    'car_id' => $car->id,
                    'pickup_date' => $request->pickup_date,
                    'dropoff_date' => $request->dropoff_date
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This car is not available for the selected dates'
                ], 422);
            }

            // Validate car state before update
            Log::info('Car state before update', [
                'car_id' => $car->id,
                'current_status' => $car->status,
                'current_is_available' => $car->is_available
            ]);
            
            // Force car to be available if needed (helpful for development/testing)
            if ($request->has('_force_available') && config('app.env') !== 'production') {
                $car->is_available = true;
                $car->status = 'available';
                Log::info('Car availability forced', ['car_id' => $car->id]);
            }
            
            if (!$car->is_available || $car->status !== 'available') {
                DB::rollBack();
                Log::warning('Car is not in a reservable state', [
                    'car_id' => $car->id,
                    'status' => $car->status,
                    'is_available' => $car->is_available
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This car is not currently available for reservation'
                ], 422);
            }

            // Check if user can rent
            if ($user && method_exists($user, 'canRent') && !$user->canRent()) {
                DB::rollBack();
                Log::warning('User cannot rent', [
                    'user_id' => $user->id,
                    'reason' => method_exists($user, 'getRentalRestrictionReason') ? $user->getRentalRestrictionReason() : 'Unknown restriction'
                ]);
                return response()->json([
                    'success' => false,
                    'message' => method_exists($user, 'getRentalRestrictionReason') ? 
                        ($user->getRentalRestrictionReason() ?? 'You are not eligible to rent at this time.') : 
                        'You are not eligible to rent at this time.'
                ], 422);
            }

            // Calculate pricing
            $basePrice = $car->price_per_day * $totalDays;
            $discountAmount = $car->discount_percentage > 0 ? ($basePrice * $car->discount_percentage) / 100 : 0;

            $insuranceCost = 0;
            if ($request->insurance_plan === 'standard') {
                $insuranceCost = $totalDays * config('booking.insurance.standard', 50);
            } elseif ($request->insurance_plan === 'premium') {
                $insuranceCost = $totalDays * config('booking.insurance.premium', 100);
            }

            $extrasCost = 0;
            if ($request->boolean('additional_driver')) {
                $extrasCost += config('booking.additional_driver_fee', 30);
            }
            if ($request->boolean('gps_enabled')) {
                $extrasCost += config('booking.gps_fee', 20);
            }
            if ($request->boolean('child_seat')) {
                $extrasCost += config('booking.child_seat_fee', 15);
            }
            
            // Add delivery fees if applicable
            if ($request->delivery_option === 'home') {
                $extrasCost += config('booking.home_delivery_fee', 50);
            } elseif ($request->delivery_option === 'airport') {
                $extrasCost += config('booking.airport_delivery_fee', 80);
            }

            $subtotal = $basePrice - $discountAmount + $insuranceCost + $extrasCost;
            $taxAmount = ($subtotal * config('booking.tax_rate', 10)) / 100;
            $totalAmount = $subtotal + $taxAmount;

            $confirmationCode = method_exists(Booking::class, 'generateConfirmationCode')
                ? (new Booking)->generateConfirmationCode()
                : strtoupper(Str::random(8));
                
            // Set mileage limit based on car configuration or default values
            $mileageLimit = $car->mileage_limit ?? config('booking.mileage_limit', 200);
            
            // Set extra mileage cost
            $extraMileageCost = $car->extra_mileage_cost ?? config('booking.extra_mileage_cost', 2.5);

            $booking = new Booking([
                'user_id' => $user ? $user->id : null,
                'car_id' => $car->id,
                'booking_number' => $this->generateBookingNumber(),
                'confirmation_code' => $confirmationCode,
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
                'customer_name' => $user ? $user->name : $request->customer_name,
                'customer_email' => $user ? $user->email : $request->customer_email,
                'customer_phone' => $user ? ($user->phone ?? $request->customer_phone) : $request->customer_phone,
                'customer_id_number' => $user ? ($user->id_number ?? $request->customer_id_number) : $request->customer_id_number,
                'insurance_plan' => $request->insurance_plan,
                'additional_driver' => $request->boolean('additional_driver'),
                'additional_driver_name' => $request->additional_driver_name,
                'additional_driver_license' => $request->additional_driver_license,
                'delivery_option' => $request->delivery_option ?? 'none',
                'delivery_address' => $request->delivery_address,
                'fuel_policy' => config('booking.fuel_policy', 'full-to-full'),
                'mileage_limit' => $mileageLimit,
                'extra_mileage_cost' => $extraMileageCost,
                'deposit_amount' => config('booking.deposit_amount', 1000),
                'deposit_status' => 'pending',
                'language_preference' => $request->language_preference ?? app()->getLocale(),
                'gps_enabled' => $request->boolean('gps_enabled'),
                'child_seat' => $request->boolean('child_seat'),
                'start_mileage' => (int) $request->start_mileage,
            ]);

            $booking->save();
            Log::info('Booking created', ['booking_id' => $booking->id]);

            // Process payment if not cash on delivery
            if ($request->payment_method !== 'cash_on_delivery') {
                try {
                    // Simulate payment processing - in a real app, this would integrate with a payment gateway
                    $transactionId = 'TRANS-' . strtoupper(Str::random(10));
                    
                    // Create payment record
                    $payment = new Payment([
                        'booking_id' => $booking->id,
                        'amount' => $totalAmount,
                        'payment_method' => $request->payment_method,
                        'transaction_id' => $transactionId,
                        'status' => 'completed',
                        'payment_date' => now(),
                    ]);
                    $payment->save();
                    
                    // Update booking status
                    $booking->update([
                        'payment_status' => 'paid',
                        'status' => 'confirmed',
                        'transaction_id' => $transactionId
                    ]);
                    
                    Log::info('Payment processed', ['booking_id' => $booking->id, 'payment_id' => $payment->id]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Payment update failed: ' . $e->getMessage(), [
                        'booking_id' => $booking->id, 
                        'exception' => $e
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to process payment. Please try again.'
                    ], 500);
                }
            }

            // Update car status
            try {
                // Using an array to update the car instead of forceFill
                $car->update([
                    'is_available' => false,
                    'status' => 'pending'  // Will change to "rented" when pickup occurs
                ]);
                Log::info('Car status updated', ['car_id' => $car->id]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Car status update failed: ' . $e->getMessage(), [
                    'car_id' => $car->id,
                    'exception' => $e,
                    'sql_error' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to reserve the car. Please try again.'
                ], 500);
            }

            // Send confirmation email
            try {
                $this->sendBookingConfirmation($booking);
                Log::info('Booking confirmation email sent', ['email' => $booking->customer_email]);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                Log::warning('Failed to send booking confirmation email: ' . $e->getMessage(), [
                    'booking_id' => $booking->id,
                    'exception' => $e
                ]);
            }

            DB::commit();
            Log::info('Booking transaction completed', ['booking_number' => $booking->booking_number]);

            return response()->json([
                'success' => true,
                'booking_number' => $booking->booking_number,
                'message' => 'Your booking has been confirmed! Booking number: ' . $booking->booking_number,
                'data' => [
                    'id' => $booking->id,
                    'booking_number' => $booking->booking_number,
                    'confirmation_code' => $booking->confirmation_code,
                    'pickup_date' => $booking->pickup_date->format('Y-m-d'),
                    'pickup_time' => $booking->pickup_time,
                    'dropoff_date' => $booking->dropoff_date->format('Y-m-d'),
                    'dropoff_time' => $booking->dropoff_time,
                    'total_amount' => $booking->total_amount,
                    'car_name' => $car->name,
                    'car_image' => $car->main_image ? Storage::url($car->main_image) : null,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your booking. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the payment page for a booking
     */
    public function payment($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['car', 'car.brand', 'car.category'])
            ->firstOrFail();
        
        // Check if user is authorized to view this payment page
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this payment page.');
        }
        
        // Check if booking needs payment
        if ($booking->payment_status !== 'unpaid' && $booking->payment_status !== 'pending') {
            return redirect()->route('bookings.show', $booking->booking_number)->with('info', 'This booking has already been paid.');
        }
        
        return view('site.bookings.payment', compact('booking'));
    }

    /**
     * Process payment for a booking
     */
    public function processPayment(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to make this payment.');
        }
        
        // Check if booking needs payment
        if ($booking->payment_status !== 'unpaid' && $booking->payment_status !== 'pending') {
            return redirect()->route('bookings.show', $booking->booking_number)->with('info', 'This booking has already been paid.');
        }
        
        // Validate payment data
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:credit_card,paypal,bank_transfer',
            'card_number' => 'required_if:payment_method,credit_card|nullable|string',
            'card_holder' => 'required_if:payment_method,credit_card|nullable|string',
            'card_expiry' => 'required_if:payment_method,credit_card|nullable|string',
            'card_cvv' => 'required_if:payment_method,credit_card|nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // In a real application, this would integrate with a payment gateway
            // For now, we'll simulate a successful payment
            $transactionId = 'TRANS-' . strtoupper(Str::random(10));
            
            // Create payment record
            $payment = new Payment([
                'booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'payment_method' => $request->payment_method,
                'transaction_id' => $transactionId,
                'status' => 'completed',
                'payment_date' => now(),
            ]);
            $payment->save();
            
            // Update booking status
            $booking->update([
                'payment_status' => 'paid',
                'status' => 'confirmed',
                'transaction_id' => $transactionId
            ]);
            
            // If booking was pending and now confirmed, make sure car status is updated
            if ($booking->car && $booking->car->status === 'available') {
                $booking->car->update([
                    'is_available' => false,
                    'status' => 'pending'
                ]);
            }
            
            DB::commit();
            
            // Send payment confirmation email
            try {
                $this->sendPaymentConfirmation($booking, $payment);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                Log::warning('Failed to send payment confirmation email: ' . $e->getMessage());
            }
            
            return redirect()->route('bookings.confirmation', $booking->booking_number)
                ->with('success', 'Payment processed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to process payment. Please try again.');
        }
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized to cancel this booking
        if (Auth::id() !== $booking->user_id && !Auth::user()->isAdmin() && (!method_exists(Auth::user(), 'isStaff') || !Auth::user()->isStaff())) {
            return redirect()->back()->with('error', 'You are not authorized to cancel this booking.');
        }
        
        // Check if booking can be cancelled
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return redirect()->back()->with('error', 'This booking cannot be cancelled.');
        }
        
        // Get cancellation reason if provided
        $cancellationReason = $request->cancellation_reason ?? 'Cancelled by customer';
        
        try {
            DB::beginTransaction();
            
            // Update booking status
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $cancellationReason,
            ]);
            
            // Update car availability
            if ($booking->car) {
                $booking->car->update([
                    'status' => 'available',
                    'is_available' => true,
                ]);
            }
            
            // Process refund if payment was made
            if ($booking->payment_status === 'paid') {
                // In a real application, integrate with payment gateway to process refund
                // For now, just update the status
                $booking->update([
                    'payment_status' => 'refunded',
                ]);
                
                // Create refund record
                $refund = new Payment([
                    'booking_id' => $booking->id,
                    'amount' => -$booking->total_amount, // Negative amount indicates refund
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => 'REFUND-' . strtoupper(Str::random(10)),
                    'status' => 'completed',
                    'payment_date' => now(),
                    'notes' => 'Refund for cancelled booking',
                ]);
                $refund->save();
            }
            
            DB::commit();
            
            // Send cancellation email
            try {
                $this->sendCancellationEmail($booking);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                Log::warning('Failed to send cancellation email: ' . $e->getMessage());
            }
            
            return redirect()->route('bookings.show', $booking->booking_number)
                ->with('success', 'Your booking has been cancelled successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking cancellation failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to cancel the booking. Please try again.');
        }
    }
    
    /**
     * Start a booking (record pickup)
     */
    public function startRental(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized (admin or staff)
        if (!Auth::user()->isAdmin() && (!method_exists(Auth::user(), 'isStaff') || !Auth::user()->isStaff())) {
            return redirect()->back()->with('error', 'You are not authorized to start this rental.');
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'start_mileage' => 'required|integer|min:0',
            'fuel_level' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if booking is in a valid state to start
        if ($booking->status !== 'confirmed') {
            return redirect()->back()->with('error', 'This booking cannot be started. Current status: ' . $booking->status_label);
        }
        
        try {
            DB::beginTransaction();
            
            // Update start mileage if provided value is different
            if ($booking->start_mileage != $request->start_mileage) {
                $booking->start_mileage = $request->start_mileage;
            }
            
            // Start the rental
            $booking->status = 'in_progress';
            $booking->save();
            
            // Update car status
            if ($booking->car) {
                $booking->car->update([
                    'status' => 'rented',
                    'is_available' => false,
                    'mileage' => $request->start_mileage  // Update car mileage to match rental start
                ]);
            }
            
            // Add rental start notes if provided
            if ($request->filled('notes')) {
                // If a "notes" field already exists on the booking model, use it directly
                if (array_key_exists('notes', $booking->getAttributes())) {
                    $booking->notes = ($booking->notes ? $booking->notes . "\n\n" : '') . 
                        "Pickup notes (" . now()->format('Y-m-d H:i') . "): " . $request->notes;
                    $booking->save();
                } else {
                    // Otherwise create a separate rental note record if you have that model
                    // This is just placeholder code - adjust based on your actual model structure
                    if (class_exists('App\Models\RentalNote')) {
                        \App\Models\RentalNote::create([
                            'booking_id' => $booking->id,
                            'user_id' => Auth::id(),
                            'note_type' => 'pickup',
                            'content' => $request->notes,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', 'Rental started successfully. Start mileage recorded: ' . number_format($request->start_mileage) . ' km');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rental start failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to start the rental. Please try again.');
        }
    }
    
    /**
     * Complete a booking (for staff use)
     */
    public function complete(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized (admin or staff)
        if (!Auth::user()->isAdmin() && (!method_exists(Auth::user(), 'isStaff') || !Auth::user()->isStaff())) {
            return redirect()->back()->with('error', 'You are not authorized to complete this booking.');
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'end_mileage' => 'required|integer|min:' . $booking->start_mileage,
            'fuel_level' => 'required|integer|min:0|max:100',
            'notes' => 'nullable|string',
            'damage_report' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            DB::beginTransaction();
            
            // Record end mileage
            $booking->end_mileage = $request->end_mileage;
            
            // Calculate total mileage
            $totalMileage = $booking->end_mileage - $booking->start_mileage;
            
            // Calculate if there's extra mileage beyond the limit
            $allowedMileage = $booking->mileage_limit * $booking->total_days;
            $extraMileage = max(0, $totalMileage - $allowedMileage);
            
            // Calculate extra mileage charges if applicable
            $extraMileageCharges = 0;
            if ($extraMileage > 0) {
                $extraMileageCharges = $extraMileage * $booking->extra_mileage_cost;
                $booking->extra_mileage = $extraMileage;
                $booking->extra_mileage_charges = $extraMileageCharges;
            }
            
            // Complete the booking
            $booking->status = 'completed';
            $booking->completed_at = now();
            $booking->save();
            
            // Update car status and mileage
            if ($booking->car) {
                $booking->car->update([
                    'status' => 'available',
                    'is_available' => true,
                    'mileage' => $request->end_mileage  // Update car with current mileage
                ]);
            }
            
            // Add completion notes if provided
            if ($request->filled('notes') || $request->filled('damage_report')) {
                $completionNotes = "Dropoff notes (" . now()->format('Y-m-d H:i') . "): ";
                
                if ($request->filled('notes')) {
                    $completionNotes .= $request->notes;
                }
                
                if ($request->filled('damage_report')) {
                    $completionNotes .= "\n\nDamage report: " . $request->damage_report;
                }
                
                // If a "notes" field already exists on the booking model, use it directly
                if (array_key_exists('notes', $booking->getAttributes())) {
                    $booking->notes = ($booking->notes ? $booking->notes . "\n\n" : '') . $completionNotes;
                    $booking->save();
                } else {
                    // Otherwise create a separate rental note record if you have that model
                    if (class_exists('App\Models\RentalNote')) {
                        \App\Models\RentalNote::create([
                            'booking_id' => $booking->id,
                            'user_id' => Auth::id(),
                            'note_type' => 'dropoff',
                            'content' => $completionNotes,
                        ]);
                    }
                }
            }
            
            // If there are extra charges, create an additional payment record
            if ($extraMileageCharges > 0) {
                $extraChargePayment = new Payment([
                    'booking_id' => $booking->id,
                    'amount' => $extraMileageCharges,
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => 'EXTRA-' . strtoupper(Str::random(8)),
                    'status' => 'pending',  // Will need to be collected from customer
                    'payment_date' => null,
                    'notes' => 'Extra mileage charges: ' . $extraMileage . ' km @ ' . 
                        number_format($booking->extra_mileage_cost, 2) . ' MAD/km',
                ]);
                $extraChargePayment->save();
            }
            
            DB::commit();
            
            // Determine success message based on extra charges
            $successMessage = 'Booking completed successfully.';
            if ($extraMileageCharges > 0) {
                $successMessage .= ' Extra mileage charges of ' . 
                    number_format($extraMileageCharges, 2) . 
                    ' MAD have been added for ' . number_format($extraMileage) . ' km over the limit.';
            }
            
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking completion failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to complete the booking. Please try again.');
        }
    }
    
    /**
     * Extend a booking (increase rental duration)
     */
    public function extend(Request $request, $bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)->firstOrFail();
        
        // Check if user is authorized
        if (Auth::id() !== $booking->user_id && !Auth::user()->isAdmin() && (!method_exists(Auth::user(), 'isStaff') || !Auth::user()->isStaff())) {
            return redirect()->back()->with('error', 'You are not authorized to extend this booking.');
        }
        
        // Validate request
        $validator = Validator::make($request->all(), [
            'new_dropoff_date' => 'required|date|after:' . $booking->dropoff_date->format('Y-m-d'),
            'new_dropoff_time' => 'required|string',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Check if booking can be extended
        if (!in_array($booking->status, ['confirmed', 'in_progress'])) {
            return redirect()->back()->with('error', 'This booking cannot be extended. Current status: ' . $booking->status_label);
        }
        
        try {
            DB::beginTransaction();
            
            // Calculate additional days
            $oldDays = $booking->total_days;
            $oldDropoff = Carbon::parse($booking->dropoff_date->format('Y-m-d') . ' ' . $booking->dropoff_time);
            $newDropoff = Carbon::parse($request->new_dropoff_date . ' ' . $request->new_dropoff_time);
            
            // Calculate new total days
            $pickup = Carbon::parse($booking->pickup_date->format('Y-m-d') . ' ' . $booking->pickup_time);
            $newTotalDays = max(1, ceil($newDropoff->diffInHours($pickup) / 24));
            $additionalDays = $newTotalDays - $oldDays;
            
            // Calculate additional costs
            $additionalBasePrice = $booking->car->price_per_day * $additionalDays;
            $additionalDiscount = $booking->car->discount_percentage > 0 ? 
                ($additionalBasePrice * $booking->car->discount_percentage) / 100 : 0;
                
            // Add insurance costs for additional days if applicable
            $additionalInsuranceCost = 0;
            if ($booking->insurance_plan === 'standard') {
                $additionalInsuranceCost = $additionalDays * config('booking.insurance.standard', 50);
            } elseif ($booking->insurance_plan === 'premium') {
                $additionalInsuranceCost = $additionalDays * config('booking.insurance.premium', 100);
            }
            
            // Calculate additional tax
            $additionalSubtotal = $additionalBasePrice - $additionalDiscount + $additionalInsuranceCost;
            $additionalTax = ($additionalSubtotal * config('booking.tax_rate', 10)) / 100;
            
            // Calculate new total amount
            $newTotalAmount = $booking->total_amount + $additionalSubtotal + $additionalTax;
            
            // Update booking with new dates and amounts
            $booking->update([
                'dropoff_date' => $request->new_dropoff_date,
                'dropoff_time' => $request->new_dropoff_time,
                'total_days' => $newTotalDays,
                'base_price' => $booking->base_price + $additionalBasePrice,
                'discount_amount' => $booking->discount_amount + $additionalDiscount,
                'tax_amount' => $booking->tax_amount + $additionalTax,
                'total_amount' => $newTotalAmount,
            ]);
            
            // Create extension payment record if not cash on delivery
            if ($booking->payment_method !== 'cash_on_delivery' && $booking->payment_status === 'paid') {
                $extensionAmount = $additionalSubtotal + $additionalTax;
                
                // In a real application, this would integrate with a payment gateway
                $transactionId = 'EXT-' . strtoupper(Str::random(10));
                
                // Create payment record
                $payment = new Payment([
                    'booking_id' => $booking->id,
                    'amount' => $extensionAmount,
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => $transactionId,
                    'status' => 'pending',  // Will need to be collected
                    'notes' => 'Extension charge for ' . $additionalDays . ' additional days',
                ]);
                $payment->save();
            }
            
            DB::commit();
            
            // Send extension confirmation
            try {
                $this->sendExtensionConfirmation($booking, $additionalDays);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                Log::warning('Failed to send extension confirmation: ' . $e->getMessage());
            }
            
            return redirect()->route('bookings.show', $booking->booking_number)
                ->with('success', 'Booking extended successfully for ' . $additionalDays . ' additional days.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking extension failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to extend the booking. Please try again.');
        }
    }
    
    /**
     * Generate a unique booking number
     */
    private function generateBookingNumber()
    {
        do {
            $bookingNumber = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Booking::where('booking_number', $bookingNumber)->exists());
        return $bookingNumber;
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
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->whereIn('payment_status', ['paid', 'pending'])
            ->doesntExist();
    }
    
    /**
     * Send booking confirmation email
     */
    private function sendBookingConfirmation(Booking $booking)
    {
        // In a real application, implement email sending functionality
        // For example, using Laravel's Mail facade
        /*
        Mail::send('emails.booking.confirmation', [
            'booking' => $booking,
        ], function ($message) use ($booking) {
            $message->to($booking->customer_email, $booking->customer_name)
                ->subject('Booking Confirmation: ' . $booking->booking_number);
        });
        */
        
        // For now, just log the email
        Log::info('Booking confirmation email would be sent', [
            'booking_number' => $booking->booking_number,
            'email' => $booking->customer_email,
            'name' => $booking->customer_name,
        ]);
        
        return true;
    }
    
    /**
     * Send booking cancellation email
     */
    private function sendCancellationEmail(Booking $booking)
    {
        // In a real application, implement email sending functionality
        // For example, using Laravel's Mail facade
        /*
        Mail::send('emails.booking.cancellation', [
            'booking' => $booking,
        ], function ($message) use ($booking) {
            $message->to($booking->customer_email, $booking->customer_name)
                ->subject('Booking Cancellation: ' . $booking->booking_number);
        });
        */
        
        // For now, just log the email
        Log::info('Booking cancellation email would be sent', [
            'booking_number' => $booking->booking_number,
            'email' => $booking->customer_email,
            'name' => $booking->customer_name,
        ]);
        
        return true;
    }
    
    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmation(Booking $booking, Payment $payment)
    {
        // In a real application, implement email sending functionality
        // For example, using Laravel's Mail facade
        /*
        Mail::send('emails.booking.payment', [
            'booking' => $booking,
            'payment' => $payment,
        ], function ($message) use ($booking) {
            $message->to($booking->customer_email, $booking->customer_name)
                ->subject('Payment Confirmation: ' . $booking->booking_number);
        });
        */
        
        // For now, just log the email
        Log::info('Payment confirmation email would be sent', [
            'booking_number' => $booking->booking_number,
            'email' => $booking->customer_email,
            'payment_amount' => $payment->amount,
        ]);
        
        return true;
    }
    
    /**
     * Send extension confirmation email
     */
    private function sendExtensionConfirmation(Booking $booking, $additionalDays)
    {
        // In a real application, implement email sending functionality
        // For example, using Laravel's Mail facade
        /*
        Mail::send('emails.booking.extension', [
            'booking' => $booking,
            'additional_days' => $additionalDays,
        ], function ($message) use ($booking) {
            $message->to($booking->customer_email, $booking->customer_name)
                ->subject('Booking Extension: ' . $booking->booking_number);
        });
        */
        
        // For now, just log the email
        Log::info('Extension confirmation email would be sent', [
            'booking_number' => $booking->booking_number,
            'email' => $booking->customer_email,
            'additional_days' => $additionalDays,
        ]);
        
        return true;
    }
    
    /**
     * Display booking receipt
     */
    public function receipt($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['car', 'car.brand', 'car.category'])
            ->firstOrFail();
        
        // Check if user is authorized to view this receipt
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this receipt.');
        }
        
        return view('site.bookings.receipt', compact('booking'));
    }
    
    /**
     * Generate PDF invoice for a booking
     */
    public function invoice($bookingNumber)
    {
        $booking = Booking::where('booking_number', $bookingNumber)
            ->with(['car', 'car.brand', 'car.category'])
            ->firstOrFail();
        
        // Check if user is authorized to view this invoice
        if (Auth::check() && Auth::id() !== $booking->user_id && !Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'You are not authorized to view this invoice.');
        }
        
        // In a real application, generate a PDF invoice
        // For example, using a package like barryvdh/laravel-dompdf
        /*
        $pdf = PDF::loadView('pdf.invoice', compact('booking'));
        return $pdf->download('invoice-' . $booking->booking_number . '.pdf');
        */
        
        // For now, just return a view
        return view('site.bookings.invoice', compact('booking'));
    }
    
    /**
     * Calculate mileage and charges for a booking
     */
    public function calculateMileage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'end_mileage' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $booking = Booking::with('car')->findOrFail($request->booking_id);
        
        // Check if start mileage exists
        if (!$booking->start_mileage) {
            return response()->json([
                'success' => false,
                'message' => 'Start mileage has not been recorded for this booking.'
            ], 422);
        }
        
        // Calculate total mileage
        $totalMileage = max(0, $request->end_mileage - $booking->start_mileage);
        
        // Calculate allowed mileage
        $allowedMileage = $booking->mileage_limit * $booking->total_days;
        
        // Calculate extra mileage if any
        $extraMileage = max(0, $totalMileage - $allowedMileage);
        
        // Calculate extra mileage charges
        $extraMileageCharges = $extraMileage * $booking->extra_mileage_cost;
        
        return response()->json([
            'success' => true,
            'data' => [
                'start_mileage' => $booking->start_mileage,
                'end_mileage' => $request->end_mileage,
                'total_mileage' => $totalMileage,
                'allowed_mileage' => $allowedMileage,
                'extra_mileage' => $extraMileage,
                'extra_mileage_cost' => $booking->extra_mileage_cost,
                'extra_mileage_charges' => $extraMileageCharges,
                'has_extra_charges' => $extraMileage > 0,
            ]
        ]);
    }
}
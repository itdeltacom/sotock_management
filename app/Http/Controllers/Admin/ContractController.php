<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Car;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ContractController extends Controller
{
    // Fixed tax rate of 0% (similar to BookingController)
    private const TAX_RATE = 0;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.contracts.index');
    }

    /**
     * Process DataTables AJAX request.
     */
    public function datatable(Request $request)
    {
        $query = Contract::with(['client', 'car', 'payments'])
            ->select('contracts.*');

        // Add filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('contract_number', function ($contract) {
                return 'CT-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT);
            })
            ->addColumn('client_name', function ($contract) {
                return $contract->client->name;
            })
            ->addColumn('car_details', function ($contract) {
                return $contract->car->brand_name . ' ' . $contract->car->model . ' (' . $contract->car->matricule . ')';
            })
            ->addColumn('duration', function ($contract) {
                return ($contract->duration_in_days ?? 0) . ' day(s)';
            })
            ->addColumn('payment_info', function ($contract) {
                $paid = number_format($contract->total_paid, 2);
                $total = number_format($contract->total_amount, 2);
                $percentage = $contract->payment_progress;

                return "
                    <div class='payment-info'>
                        <div class='d-flex justify-content-between'>
                            <span>{$paid} / {$total} MAD</span>
                            <span>{$percentage}%</span>
                        </div>
                        <div class='progress mt-1' style='height: 5px;'>
                            <div class='progress-bar bg-success' style='width: {$percentage}%'></div>
                        </div>
                    </div>
                ";
            })
            ->addColumn('status_badge', function ($contract) {
                return '<span class="badge bg-' . $contract->status_color . '">' . ucfirst($contract->status) . '</span>';
            })
            ->addColumn('payment_badge', function ($contract) {
                return '<span class="badge bg-' . $contract->payment_status_color . '">' . ucfirst($contract->payment_status) . '</span>';
            })
            ->addColumn('actions', function ($contract) {
                $buttons = '<div class="btn-group" role="group">';

                // View button
                $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';

                // Edit button (only if active)
                if ($contract->canBeEdited()) {
                    $buttons .= '<a href="' . route('admin.contracts.edit', $contract->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                }

                // Payment button
                if ($contract->payment_status !== 'paid') {
                    $buttons .= '<button class="btn btn-sm btn-success add-payment" data-id="' . $contract->id . '" title="Add Payment"><i class="fas fa-money-bill"></i></button>';
                }

                // Complete button
                if ($contract->canBeCompleted()) {
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                }

                // Cancel button
                if ($contract->canBeCancelled()) {
                    $buttons .= '<button class="btn btn-sm btn-danger cancel-contract" data-id="' . $contract->id . '" title="Cancel"><i class="fas fa-times"></i></button>';
                }

                // Extend button
                if ($contract->canBeExtended()) {
                    $buttons .= '<button class="btn btn-sm btn-warning extend-contract" data-id="' . $contract->id . '" title="Extend"><i class="fas fa-calendar-plus"></i></button>';
                }

                // Print button
                $buttons .= '<a href="' . route('admin.contracts.print', $contract->id) . '" class="btn btn-sm btn-secondary" target="_blank" title="Print"><i class="fas fa-print"></i></a>';

                // Delete button
                if (auth()->user()->can('delete contracts')) {
                    $buttons .= '<button class="btn btn-sm btn-danger delete-record" data-id="' . $contract->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                }

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['payment_info', 'status_badge', 'payment_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new contract.
     */
    public function create()
    {
        // Get all active clients
        $clients = User::where('status', 'active')->get();

        // Get all available cars
        $availableCars = Car::where('status', 'available')->get();

        return view('admin.contracts.create', compact('clients', 'availableCars'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $creationType = $request->input('creation_type', 'booking');

            if ($creationType === 'booking') {
                // Validate booking-based input
                $validated = $request->validate([
                    'client_id' => 'required|exists:users,id',
                    'booking_id' => 'required|exists:bookings,id',
                    'payment_status' => 'required|in:pending,partial,paid',
                    'terms_agreed' => 'accepted',
                    'id_verified' => 'accepted',
                    'vehicle_inspected' => 'accepted',
                ]);

                // Fetch booking with related car and client
                $booking = Booking::with('car', 'user')->findOrFail($validated['booking_id']);

                // Verify booking ownership and status
                if ($booking->user_id != $validated['client_id'] || $booking->status != 'pending') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or non-pending booking.'
                    ], 422);
                }

                // Check car availability for the booking period
                $isBooked = Contract::where('car_id', $booking->car_id)
                    ->where('status', 'active')
                    ->where(function ($query) use ($booking) {
                        $query->whereBetween('start_date', [$booking->pickup_date, $booking->dropoff_date])
                            ->orWhereBetween('end_date', [$booking->pickup_date, $booking->dropoff_date])
                            ->orWhere(function ($q) use ($booking) {
                                $q->where('start_date', '<=', $booking->pickup_date)
                                    ->where('end_date', '>=', $booking->dropoff_date);
                            });
                    })->exists();

                if ($isBooked) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Car is not available for the selected period.'
                    ], 422);
                }

                // Calculate duration and total amount
                $duration = Carbon::parse($booking->dropoff_date)->diffInDays(Carbon::parse($booking->pickup_date)) + 1;
                
                // Use booking's total_amount directly instead of recalculating
                $totalAmount = $booking->total_amount;

                // Create contract within a transaction
                $contract = DB::transaction(function () use ($booking, $validated, $totalAmount, $duration) {
                    $contract = Contract::create([
                        'client_id' => $booking->user_id,
                        'car_id' => $booking->car_id,
                        'start_date' => $booking->pickup_date,
                        'end_date' => $booking->dropoff_date,
                        'duration_in_days' => $duration,
                        'rental_fee' => $booking->base_price / ($booking->total_days ?: 1),
                        'start_mileage' => $booking->start_mileage ?: $booking->car->mileage,
                        'deposit_amount' => $booking->deposit_amount ?? config('rental_config.default_deposit'),
                        'discount' => $booking->discount_amount ?? 0,
                        'insurance_plan' => $booking->insurance_plan,
                        'additional_driver' => $booking->additional_driver ?? false,
                        'gps_enabled' => $booking->gps_enabled ?? false,
                        'child_seat' => $booking->child_seat ?? false,
                        'total_amount' => $totalAmount,
                        'payment_status' => $validated['payment_status'],
                        'notes' => $booking->notes,
                        'status' => 'active',
                    ]);

                    // Update booking status
                    $booking->status = 'confirmed';
                    $booking->save();

                    // Update car status
                    $booking->car->is_available = false;
                    $booking->car->status = 'rented';
                    $booking->car->save();

                    return $contract;
                });

                Log::info('Contract created from booking', ['contract_id' => $contract->id, 'client_id' => $validated['client_id']]);

                return response()->json([
                    'success' => true,
                    'message' => 'Contract created successfully.',
                    'contract_id' => $contract->id,
                ]);
            } else {
                // Validate manual input
                $validated = $request->validate([
                    'client_id' => 'required|exists:users,id',
                    'car_id' => 'required|exists:cars,id',
                    'start_date' => 'required|date|after_or_equal:today',
                    'end_date' => 'required|date|after:start_date',
                    'rental_fee' => 'required|numeric|min:0',
                    'start_mileage' => 'required|integer|min:0',
                    'deposit_amount' => 'nullable|numeric|min:0',
                    'discount' => 'nullable|numeric|min:0',
                    'insurance_plan' => 'nullable|in:standard,premium',
                    'additional_driver' => 'boolean',
                    'gps_enabled' => 'boolean',
                    'child_seat' => 'boolean',
                    'payment_status' => 'required|in:pending,partial,paid',
                    'notes' => 'nullable|string|max:1000',
                    'terms_agreed' => 'accepted',
                    'id_verified' => 'accepted',
                    'vehicle_inspected' => 'accepted',
                ]);

                // Fetch car
                $car = Car::findOrFail($validated['car_id']);

                // Check car availability for the selected period
                $isBooked = Contract::where('car_id', $validated['car_id'])
                    ->where('status', 'active')
                    ->where(function ($query) use ($validated) {
                        $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                            ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                            ->orWhere(function ($q) use ($validated) {
                                $q->where('start_date', '<=', $validated['start_date'])
                                    ->where('end_date', '>=', $validated['end_date']);
                            });
                    })->exists();

                if ($isBooked) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Car is not available for the selected period.'
                    ], 422);
                }

                // Calculate duration and total amount
                $duration = Carbon::parse($validated['end_date'])->diffInDays(Carbon::parse($validated['start_date'])) + 1;
                
                // Get daily extras cost
                $dailyExtrasCost = 0;
                if (!empty($validated['additional_driver'])) {
                    $dailyExtrasCost += config('rental_config.additional_driver_fee', 0);
                }
                if (!empty($validated['gps_enabled'])) {
                    $dailyExtrasCost += config('rental_config.gps_fee', 0);
                }
                if (!empty($validated['child_seat'])) {
                    $dailyExtrasCost += config('rental_config.child_seat_fee', 0);
                }
                // Add insurance cost
                if ($validated['insurance_plan'] === 'standard') {
                    $dailyExtrasCost += 50; // Standard insurance rate
                } elseif ($validated['insurance_plan'] === 'premium') {
                    $dailyExtrasCost += 100; // Premium insurance rate
                }
                
                $extrasCost = $dailyExtrasCost * $duration;
                $subtotal = ($validated['rental_fee'] * $duration) + $extrasCost;
                
                // Using fixed tax rate of 0%
                $tax = $subtotal * self::TAX_RATE / 100;
                
                $totalAmount = $subtotal + $tax - ($validated['discount'] ?? 0);

                // Create contract within a transaction
                $contract = DB::transaction(function () use ($validated, $totalAmount, $car, $duration) {
                    $contract = Contract::create([
                        'client_id' => $validated['client_id'],
                        'car_id' => $validated['car_id'],
                        'start_date' => $validated['start_date'],
                        'end_date' => $validated['end_date'],
                        'duration_in_days' => $duration,
                        'rental_fee' => $validated['rental_fee'],
                        'start_mileage' => $validated['start_mileage'],
                        'deposit_amount' => $validated['deposit_amount'] ?? config('rental_config.default_deposit'),
                        'discount' => $validated['discount'] ?? 0,
                        'insurance_plan' => $validated['insurance_plan'],
                        'additional_driver' => $validated['additional_driver'] ?? false,
                        'gps_enabled' => $validated['gps_enabled'] ?? false,
                        'child_seat' => $validated['child_seat'] ?? false,
                        'total_amount' => $totalAmount,
                        'payment_status' => $validated['payment_status'],
                        'notes' => $validated['notes'],
                        'status' => 'active',
                    ]);

                    // Update car status
                    $car->is_available = false;
                    $car->status = 'rented';
                    $car->save();

                    return $contract;
                });

                Log::info('Contract created manually', ['contract_id' => $contract->id, 'client_id' => $validated['client_id']]);

                return response()->json([
                    'success' => true,
                    'message' => 'Contract created successfully.',
                    'contract_id' => $contract->id,
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Contract creation validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Contract creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fetch pending bookings for a client.
     */
    public function getPendingBookings(Request $request)
    {
        try {
            $clientId = $request->query('client_id');
            if (!$clientId) {
                return response()->json([
                    'success' => false,
                    'bookings' => [],
                    'message' => 'Client ID is required.'
                ], 422);
            }
    
            // Fetch pending bookings for the client using user_id
            $bookings = Booking::with(['car'])
                ->where('user_id', $clientId)
                ->where('status', 'pending')
                ->get();
    
            return response()->json([
                'success' => true,
                'bookings' => $bookings->isEmpty() ? [] : $bookings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch pending bookings: ' . $e->getMessage(), [
                'client_id' => $request->query('client_id'),
                'exception' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pending bookings: ' . $e->getMessage(),
                'bookings' => []
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Contract $contract)
    {
        $contract->load(['client', 'car', 'payments.processedBy']);
        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contract $contract)
    {
        if (!$contract->canBeEdited()) {
            return redirect()->route('admin.contracts.show', $contract->id)
                ->with('error', 'This contract cannot be edited.');
        }

        $contract->load('client', 'car');
        return view('admin.contracts.edit', compact('contract'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        try {
            // Check if the contract can be edited
            if (!$contract->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This contract cannot be edited.'
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'end_date' => 'required|date|after_or_equal:' . $contract->start_date->format('Y-m-d'),
                'rental_fee' => 'required|numeric|min:0',
                'deposit_amount' => 'nullable|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'insurance_plan' => 'nullable|in:standard,premium',
                'additional_driver' => 'boolean',
                'gps_enabled' => 'boolean',
                'child_seat' => 'boolean',
                'payment_status' => 'required|in:pending,partial,paid',
                'status' => 'required|in:active,completed,cancelled',
                'notes' => 'nullable|string|max:1000',
                'due_date' => 'nullable|date',
            ]);

            // Check car availability for the updated period (exclude current contract)
            $isBooked = Contract::where('car_id', $contract->car_id)
                ->where('id', '!=', $contract->id)
                ->where('status', 'active')
                ->where(function ($query) use ($contract, $validated) {
                    $query->whereBetween('start_date', [$contract->start_date->format('Y-m-d'), $validated['end_date']])
                        ->orWhereBetween('end_date', [$contract->start_date->format('Y-m-d'), $validated['end_date']])
                        ->orWhere(function ($q) use ($contract, $validated) {
                            $q->where('start_date', '<=', $contract->start_date->format('Y-m-d'))
                                ->where('end_date', '>=', $validated['end_date']);
                        });
                })->exists();

            if ($isBooked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Car is not available for the updated period.'
                ], 422);
            }

            // Calculate duration
            $duration = Carbon::parse($validated['end_date'])->diffInDays(Carbon::parse($contract->start_date)) + 1;
            
            // Get daily extras cost
            $dailyExtrasCost = 0;
            if (!empty($validated['additional_driver'])) {
                $dailyExtrasCost += config('rental_config.additional_driver_fee', 0);
            }
            if (!empty($validated['gps_enabled'])) {
                $dailyExtrasCost += config('rental_config.gps_fee', 0);
            }
            if (!empty($validated['child_seat'])) {
                $dailyExtrasCost += config('rental_config.child_seat_fee', 0);
            }
            // Add insurance cost
            if ($validated['insurance_plan'] === 'standard') {
                $dailyExtrasCost += 50; // Standard insurance rate
            } elseif ($validated['insurance_plan'] === 'premium') {
                $dailyExtrasCost += 100; // Premium insurance rate
            }
            
            $extrasCost = $dailyExtrasCost * $duration;
            $subtotal = ($validated['rental_fee'] * $duration) + $extrasCost;
            
            // Using fixed tax rate of 0%
            $tax = $subtotal * self::TAX_RATE / 100;
            
            $totalAmount = $subtotal + $tax - ($validated['discount'] ?? 0);

            // Update contract and related booking within a transaction
            $updatedContract = DB::transaction(function () use ($contract, $validated, $totalAmount, $duration) {
                // Update contract
                $contract->update([
                    'end_date' => $validated['end_date'],
                    'duration_in_days' => $duration,
                    'rental_fee' => $validated['rental_fee'],
                    'deposit_amount' => $validated['deposit_amount'] ?? config('rental_config.default_deposit'),
                    'discount' => $validated['discount'] ?? 0,
                    'insurance_plan' => $validated['insurance_plan'],
                    'additional_driver' => $validated['additional_driver'] ?? false,
                    'gps_enabled' => $validated['gps_enabled'] ?? false,
                    'child_seat' => $validated['child_seat'] ?? false,
                    'total_amount' => $totalAmount,
                    'payment_status' => $validated['payment_status'],
                    'status' => $validated['status'],
                    'notes' => $validated['notes'],
                    'due_date' => $validated['due_date'],
                ]);

                // Update related booking if it exists
                $booking = Booking::where('user_id', $contract->client_id)
                    ->where('car_id', $contract->car_id)
                    ->where('pickup_date', $contract->start_date)
                    ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                    ->first();

                if ($booking) {
                    $booking->dropoff_date = $validated['end_date'];
                    $booking->total_days = $duration;
                    $booking->base_price = $validated['rental_fee'] * $duration;
                    $booking->discount_amount = $validated['discount'] ?? 0;
                    $booking->insurance_plan = $validated['insurance_plan'];
                    $booking->additional_driver = $validated['additional_driver'] ?? false;
                    $booking->gps_enabled = $validated['gps_enabled'] ?? false;
                    $booking->child_seat = $validated['child_seat'] ?? false;
                    $booking->total_amount = $totalAmount;

                    // Update booking status based on contract status
                    if ($validated['status'] === 'cancelled') {
                        $booking->status = 'cancelled';
                    } elseif ($validated['status'] === 'completed') {
                        $booking->status = 'completed';
                    }

                    $booking->save();
                }

                // Update car status if contract is cancelled or completed
                if (in_array($validated['status'], ['cancelled', 'completed'])) {
                    $contract->car->is_available = true;
                    $contract->car->status = 'available';
                    $contract->car->save();
                }

                return $contract;
            });

            Log::info('Contract updated', ['contract_id' => $updatedContract->id, 'client_id' => $contract->client_id]);

            return response()->json([
                'success' => true,
                'message' => 'Contract updated successfully.',
                'contract_id' => $updatedContract->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Contract update validation failed', [
                'contract_id' => $contract->id,
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Contract update failed: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add payment to contract.
     */
    public function addPayment(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $contract->outstanding_balance,
            'payment_method' => 'required|in:cash,card,transfer,check',
            'payment_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $payment = $contract->addPayment([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'processed_by' => auth()->id(),
            ]);

            DB::commit();

            Log::info('Payment added to contract', ['contract_id' => $contract->id, 'payment_id' => $payment->id]);

            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully.',
                'payment' => $payment,
                'contract' => $contract->fresh(['payments']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to add payment: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error adding payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a contract.
     */
    public function complete(Request $request, Contract $contract)
    {
        if (!$contract->canBeCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'This contract cannot be completed.'
            ], 403);
        }

        $validated = $request->validate([
            'end_mileage' => 'required|integer|min:' . $contract->start_mileage,
            'final_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $contract->complete($validated['end_mileage']);

            if (isset($validated['final_notes'])) {
                $contract->update([
                    'notes' => $contract->notes . "\n\nCompletion Notes: " . $validated['final_notes']
                ]);
            }

            DB::commit();

            Log::info('Contract completed', ['contract_id' => $contract->id]);

            return response()->json([
                'success' => true,
                'message' => 'Contract completed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to complete contract: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a contract.
     */
    public function cancel(Request $request, Contract $contract)
    {
        if (!$contract->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This contract cannot be cancelled.'
            ], 403);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
            'refund_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $contract->cancel();
            $contract->update([
                'notes' => $contract->notes . "\n\nCancellation Reason: " . $validated['cancellation_reason']
            ]);

            // Handle refund if applicable
            if (isset($validated['refund_amount']) && $validated['refund_amount'] > 0) {
                $contract->addPayment([
                    'amount' => -$validated['refund_amount'], // Negative amount for refund
                    'payment_method' => 'refund',
                    'payment_date' => now(),
                    'notes' => 'Cancellation refund',
                    'processed_by' => auth()->id(),
                ]);
            }

            DB::commit();

            Log::info('Contract cancelled', ['contract_id' => $contract->id]);

            return response()->json([
                'success' => true,
                'message' => 'Contract cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to cancel contract: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extend a contract.
     */
    public function extend(Request $request, Contract $contract)
    {
        if (!$contract->canBeExtended()) {
            return response()->json([
                'success' => false,
                'message' => 'This contract cannot be extended.'
            ], 403);
        }

        $validated = $request->validate([
            'extension_days' => 'required|integer|min:1',
            'new_rental_fee' => 'nullable|numeric|min:0',
            'extension_notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $newEndDate = Carbon::parse($contract->end_date)->addDays($validated['extension_days']);
            $duration = Carbon::parse($newEndDate)->diffInDays(Carbon::parse($contract->start_date)) + 1;

            $contract->extend(
                $validated['extension_days'],
                $validated['new_rental_fee'] ?? null
            );

            // Update duration_in_days
            $contract->update([
                'duration_in_days' => $duration,
                'notes' => $contract->notes . "\n\nExtension Notes: " . ($validated['extension_notes'] ?? '')
            ]);

            DB::commit();

            Log::info('Contract extended', ['contract_id' => $contract->id, 'extension_days' => $validated['extension_days']]);

            return response()->json([
                'success' => true,
                'message' => 'Contract extended by ' . $validated['extension_days'] . ' days.',
                'contract' => $contract->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to extend contract: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while extending the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contract $contract)
    {
        if ($contract->payments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete contract with payment history.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // If contract is active, set car status back to available
            if ($contract->status === 'active') {
                $contract->car->update(['status' => 'available', 'is_available' => true]);
            }

            $contract->delete();

            DB::commit();

            Log::info('Contract deleted', ['contract_id' => $contract->id]);

            return response()->json([
                'success' => true,
                'message' => 'Contract deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete contract: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the contract: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show contracts ending soon.
     */
    public function endingSoon()
    {
        return view('admin.contracts.ending-soon');
    }

    /**
     * Process DataTables AJAX request for contracts ending soon.
     */
    public function endingSoonDatatable(Request $request)
    {
        $contracts = Contract::with(['client', 'car'])
            ->endingSoon()
            ->latest('end_date');

        return DataTables::of($contracts)
            ->addColumn('contract_number', function ($contract) {
                return 'CT-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT);
            })
            ->addColumn('client_name', function ($contract) {
                return $contract->client->name;
            })
            ->addColumn('car_details', function ($contract) {
                return $contract->car->brand_name . ' ' . $contract->car->model . ' (' . $contract->car->matricule . ')';
            })
            ->addColumn('days_left', function ($contract) {
                $daysLeft = now()->diffInDays($contract->end_date, false);
                if ($daysLeft < 0) {
                    return '<span class="text-danger">Overdue ' . abs($daysLeft) . ' day(s)</span>';
                } elseif ($daysLeft === 0) {
                    return '<span class="text-warning">Ends today</span>';
                } else {
                    return '<span class="text-info">' . $daysLeft . ' day(s) left</span>';
                }
            })
            ->addColumn('outstanding_balance', function ($contract) {
                $balance = $contract->outstanding_balance;
                if ($balance > 0) {
                    return '<span class="text-danger">' . number_format($balance, 2) . ' MAD</span>';
                } else {
                    return '<span class="text-success">Paid</span>';
                }
            })
            ->addColumn('actions', function ($contract) {
                $buttons = '<div class="btn-group" role="group">';

                // View button
                $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';

                // Complete button
                if ($contract->canBeCompleted()) {
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                }

                // Extend button
                if ($contract->canBeExtended()) {
                    $buttons .= '<button class="btn btn-sm btn-warning extend-contract" data-id="' . $contract->id . '" title="Extend"><i class="fas fa-calendar-plus"></i></button>';
                }

                // Notify client button
                $buttons .= '<button class="btn btn-sm btn-primary notify-client" data-id="' . $contract->id . '" title="Notify Client"><i class="fas fa-bell"></i></button>';

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['days_left', 'outstanding_balance', 'actions'])
            ->make(true);
    }

    /**
     * Show overdue contracts.
     */
    public function overdue()
    {
        return view('admin.contracts.overdue');
    }

    /**
     * Process DataTables AJAX request for overdue contracts.
     */
    public function overdueDatatable(Request $request)
    {
        $contracts = Contract::with(['client', 'car'])
            ->overdue()
            ->latest('end_date');

        return DataTables::of($contracts)
            ->addColumn('contract_number', function ($contract) {
                return 'CT-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT);
            })
            ->addColumn('client_name', function ($contract) {
                return $contract->client->name;
            })
            ->addColumn('car_details', function ($contract) {
                return $contract->car->brand_name . ' ' . $contract->car->model . ' (' . $contract->car->matricule . ')';
            })
            ->addColumn('overdue_days', function ($contract) {
                $overdueDays = $contract->overdue_days;
                return '<span class="text-danger">' . $overdueDays . ' day(s)</span>';
            })
            ->addColumn('estimated_penalty', function ($contract) {
                $estimatedPenalty = $contract->estimated_penalty;
                return '<span class="text-danger">' . number_format($estimatedPenalty, 2) . ' MAD</span>';
            })
            ->addColumn('outstanding_balance', function ($contract) {
                $balance = $contract->outstanding_balance + $contract->estimated_penalty;
                return '<span class="text-danger">' . number_format($balance, 2) . ' MAD</span>';
            })
            ->addColumn('actions', function ($contract) {
                $buttons = '<div class="btn-group" role="group">';

                // View button
                $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';

                // Complete button
                if ($contract->canBeCompleted()) {
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                }

                // Contact client button
                $buttons .= '<button class="btn btn-sm btn-warning contact-client" data-id="' . $contract->id . '" title="Contact Client"><i class="fas fa-phone"></i></button>';

                // Send reminder button
                $buttons .= '<button class="btn btn-sm btn-danger send-reminder" data-id="' . $contract->id . '" title="Send Reminder"><i class="fas fa-exclamation-triangle"></i></button>';

                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['overdue_days', 'estimated_penalty', 'outstanding_balance', 'actions'])
            ->make(true);
    }

    /**
     * Get contract statistics for the dashboard.
     */
    public function getStats()
    {
        try {
            // Count active contracts
            $active = Contract::where('status', 'active')->count();

            // Count contracts ending soon (within next 2 days)
            $endingSoon = Contract::endingSoon()->count();

            // Count overdue contracts
            $overdue = Contract::overdue()->count();

            // Count unpaid contracts
            $unpaid = Contract::unpaid()->count();

            // Calculate monthly revenue
            $monthlyRevenue = Contract::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount');

            // Calculate outstanding balance
            $outstandingBalance = Contract::unpaid()
                ->get()
                ->sum('outstanding_balance');

            // Calculate revenue by payment method (current month)
            $revenueByMethod = Payment::selectRaw('payment_method, SUM(amount) as total')
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->groupBy('payment_method')
                ->get()
                ->pluck('total', 'payment_method');

            return response()->json([
                'success' => true,
                'stats' => [
                    'active' => $active,
                    'ending_soon' => $endingSoon,
                    'overdue' => $overdue,
                    'unpaid' => $unpaid,
                    'monthly_revenue' => $monthlyRevenue,
                    'outstanding_balance' => $outstandingBalance,
                    'revenue_by_method' => $revenueByMethod,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve contract stats: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contract statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print contract.
     */
    public function printContract(Contract $contract)
    {
        $contract->load(['client', 'car', 'payments']);
        return view('admin.contracts.print', compact('contract'));
    }

    /**
     * Export contracts.
     */
    public function export(Request $request)
    {
        try {
            $query = Contract::with(['client', 'car', 'payments']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('start_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('end_date', '<=', $request->date_to);
            }

            $contracts = $query->get();

            // Placeholder for export implementation (e.g., using Maatwebsite\Excel)
            // Example: return Excel::download(new ContractsExport($contracts), 'contracts.xlsx');

            return response()->json([
                'success' => true,
                'message' => 'Export functionality not implemented. Contracts retrieved.',
                'contracts_count' => $contracts->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export contracts: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error exporting contracts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to client.
     */
    public function sendNotification(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'type' => 'required|in:ending_soon,overdue,payment_reminder',
            'message' => 'nullable|string',
        ]);

        try {
            // Placeholder for notification logic (e.g., email, SMS)
            // Example: $contract->client->notify(new ContractNotification($validated['type'], $validated['message']));

            Log::info('Notification sent to client', [
                'contract_id' => $contract->id,
                'client_id' => $contract->client_id,
                'type' => $validated['type']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'request' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ], 500);
        }
    }
}
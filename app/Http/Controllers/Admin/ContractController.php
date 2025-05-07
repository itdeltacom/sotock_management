<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\User;
use App\Models\Contract;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ContractController extends Controller
{
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
                return $contract->duration_in_days . ' day(s)';
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
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rental_fee' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'start_mileage' => 'required|integer|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'initial_payment' => 'nullable|numeric|min:0',
            'payment_method' => 'required_with:initial_payment|in:cash,card,transfer,check',
        ]);
    
        try {
            DB::beginTransaction();
            
            // Check if client can rent
            $client = User::findOrFail($validated['client_id']);
            if (!$client->canRent()) {
                throw new \Exception('Client cannot rent: ' . $client->getRentalRestrictionReason());
            }
            
            // Check if car is available
            $car = Car::findOrFail($validated['car_id']);
            if ($car->status !== 'available') {
                throw new \Exception('The selected car is not available for rent.');
            }
            
            // Calculate total amount
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $days = $startDate->diffInDays($endDate) + 1;
            
            $totalAmount = $validated['rental_fee'] * $days;
            if (isset($validated['discount'])) {
                $totalAmount -= $validated['discount'];
            }
            
            // Create the contract
            $contract = Contract::create([
                'client_id' => $validated['client_id'],
                'car_id' => $validated['car_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'rental_fee' => $validated['rental_fee'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'status' => 'active',
                'payment_status' => 'pending', // Will be updated after payment
                'notes' => $validated['notes'],
                'start_mileage' => $validated['start_mileage'],
                'total_amount' => $totalAmount,
                'discount' => $validated['discount'] ?? 0,
                'due_date' => $startDate->addDays(config('rental.payment_due_days', 5)),
            ]);
            
            // Handle initial payment if provided
            if (isset($validated['initial_payment']) && $validated['initial_payment'] > 0) {
                $contract->addPayment([
                    'amount' => $validated['initial_payment'],
                    'payment_method' => $validated['payment_method'],
                    'payment_date' => now(),
                    'notes' => 'Initial payment',
                    'processed_by' => auth()->id(),
                ]);
            }
            
            // Update car status to rented
            $car->update(['status' => 'rented']);
            
            DB::commit();
            
            return redirect()->route('admin.contracts.show', $contract->id)
                ->with('success', 'Contract created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
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
        if (!$contract->canBeEdited()) {
            return redirect()->route('admin.contracts.show', $contract->id)
                ->with('error', 'This contract cannot be edited.');
        }
        
        $validated = $request->validate([
            'end_date' => 'required|date|after_or_equal:' . $contract->start_date->format('Y-m-d'),
            'rental_fee' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();
            
            // Update the contract
            $contract->update($validated);
            
            DB::commit();
            
            return redirect()->route('admin.contracts.show', $contract->id)
                ->with('success', 'Contract updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'An error occurred while updating the contract: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Add payment to contract
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
            
            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully.',
                'payment' => $payment,
                'contract' => $contract->fresh(['payments']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
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
            ]);
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
            
            return response()->json([
                'success' => true,
                'message' => 'Contract completed successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while completing the contract: ' . $e->getMessage()
            ]);
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
            ]);
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
            
            return response()->json([
                'success' => true,
                'message' => 'Contract cancelled successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the contract: ' . $e->getMessage()
            ]);
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
            ]);
        }
        
        $validated = $request->validate([
            'extension_days' => 'required|integer|min:1',
            'new_rental_fee' => 'nullable|numeric|min:0',
            'extension_notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $contract->extend(
                $validated['extension_days'], 
                $validated['new_rental_fee'] ?? null
            );
            
            if (isset($validated['extension_notes'])) {
                $contract->update([
                    'notes' => $contract->notes . "\n\nExtension Notes: " . $validated['extension_notes']
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Contract extended by ' . $validated['extension_days'] . ' days.',
                'contract' => $contract->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while extending the contract: ' . $e->getMessage()
            ]);
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
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // If contract is active, set car status back to available
            if ($contract->status === 'active') {
                $contract->car->update(['status' => 'available']);
            }
            
            $contract->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Contract deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the contract: ' . $e->getMessage()
            ]);
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
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving contract statistics: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Print contract
     */
    public function printContract(Contract $contract)
    {
        $contract->load(['client', 'car', 'payments']);
        return view('admin.contracts.print', compact('contract'));
    }
    
    /**
     * Export contracts
     */
    public function export(Request $request)
    {
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
        
        // Generate export file (CSV, Excel, etc.)
        // Implementation depends on your preferred export method
        
        return response()->download($filePath);
    }
    
    /**
     * Send notification to client
     */
    public function sendNotification(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'type' => 'required|in:ending_soon,overdue,payment_reminder',
            'message' => 'nullable|string',
        ]);
        
        try {
            // Send notification logic here (email, SMS, etc.)
            // You would implement your notification system here
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending notification: ' . $e->getMessage()
            ]);
        }
    }
}
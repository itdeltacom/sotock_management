<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

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
        $contracts = Contract::with(['client', 'car'])->latest();
        
        return DataTables::of($contracts)
            ->addColumn('client_name', function ($contract) {
                return $contract->client->full_name;
            })
            ->addColumn('car_details', function ($contract) {
                return $contract->car->brand_name . ' ' . $contract->car->model . ' (' . $contract->car->matricule . ')';
            })
            ->addColumn('duration', function ($contract) {
                return $contract->duration_in_days . ' day(s)';
            })
            ->addColumn('status_badge', function ($contract) {
                $badges = [
                    'active' => 'success',
                    'completed' => 'primary',
                    'cancelled' => 'danger'
                ];
                
                $badge = $badges[$contract->status] ?? 'secondary';
                return '<span class="badge bg-' . $badge . '">' . ucfirst($contract->status) . '</span>';
            })
            ->addColumn('payment_badge', function ($contract) {
                $badges = [
                    'pending' => 'warning',
                    'partial' => 'info',
                    'paid' => 'success'
                ];
                
                $badge = $badges[$contract->payment_status] ?? 'secondary';
                return '<span class="badge bg-' . $badge . '">' . ucfirst($contract->payment_status) . '</span>';
            })
            ->addColumn('actions', function ($contract) {
                $buttons = '<div class="btn-group" role="group">';
                
                // View button
                $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                
                // Edit button (only if active)
                if ($contract->status === 'active') {
                    $buttons .= '<a href="' . route('admin.contracts.edit', $contract->id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>';
                }
                
                // Complete button (only if active)
                if ($contract->status === 'active') {
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                }
                
                // Cancel button (only if active)
                if ($contract->status === 'active') {
                    $buttons .= '<button class="btn btn-sm btn-danger cancel-contract" data-id="' . $contract->id . '" title="Cancel"><i class="fas fa-times"></i></button>';
                }
                
                // Extend button (only if active)
                if ($contract->status === 'active') {
                    $buttons .= '<button class="btn btn-sm btn-warning extend-contract" data-id="' . $contract->id . '" title="Extend"><i class="fas fa-calendar-plus"></i></button>';
                }
                
                // Delete button (only for admins with permission)
                $buttons .= '<button class="btn btn-sm btn-danger delete-record" data-id="' . $contract->id . '" title="Delete"><i class="fas fa-trash"></i></button>';
                
                $buttons .= '</div>';
                return $buttons;
            })
            ->rawColumns(['status_badge', 'payment_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.contracts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'car_id' => 'required|exists:cars,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rental_fee' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'start_mileage' => 'required|integer|min:0',
            'payment_status' => 'required|in:pending,partial,paid',
            'notes' => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Get the car
            $car = Car::findOrFail($request->car_id);
            
            // Check if car is available
            if ($car->status !== 'available') {
                return redirect()->back()
                    ->with('error', 'The selected car is not available for rent.')
                    ->withInput();
            }
            
            // Calculate total amount
            $startDate = new \DateTime($request->start_date);
            $endDate = new \DateTime($request->end_date);
            $days = $startDate->diff($endDate)->days + 1;
            
            $totalAmount = $request->rental_fee * $days;
            if ($request->filled('discount')) {
                $totalAmount -= $request->discount;
            }
            
            // Create the contract
            $contract = Contract::create([
                'client_id' => $request->client_id,
                'car_id' => $request->car_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'rental_fee' => $request->rental_fee,
                'deposit_amount' => $request->deposit_amount ?? 0,
                'status' => 'active',
                'payment_status' => $request->payment_status,
                'notes' => $request->notes,
                'start_mileage' => $request->start_mileage,
                'total_amount' => $totalAmount,
                'discount' => $request->discount ?? 0,
            ]);
            
            // Update car status to rented
            $car->update(['status' => 'rented']);
            
            DB::commit();
            
            return redirect()->route('admin.contracts.index')
                ->with('success', 'Contract created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                
                return redirect()->back()
                    ->with('error', 'An error occurred while creating the contract: ' . $e->getMessage())
                    ->withInput();
            }
        }
    
        /**
         * Display the specified resource.
         */
        public function show(Contract $contract)
        {
            $contract->load('client', 'car');
            return view('admin.contracts.show', compact('contract'));
        }
    
        /**
         * Show the form for editing the specified resource.
         */
        public function edit(Contract $contract)
        {
            // Only allow editing active contracts
            if ($contract->status !== 'active') {
                return redirect()->route('admin.contracts.show', $contract->id)
                    ->with('error', 'Only active contracts can be edited.');
            }
            
            $contract->load('client', 'car');
            return view('admin.contracts.edit', compact('contract'));
        }
    
        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, Contract $contract)
        {
            // Only allow editing active contracts
            if ($contract->status !== 'active') {
                return redirect()->route('admin.contracts.show', $contract->id)
                    ->with('error', 'Only active contracts can be edited.');
            }
            
            $request->validate([
                'end_date' => 'required|date|after_or_equal:' . $contract->start_date,
                'rental_fee' => 'required|numeric|min:0',
                'deposit_amount' => 'nullable|numeric|min:0',
                'payment_status' => 'required|in:pending,partial,paid',
                'notes' => 'nullable|string',
                'discount' => 'nullable|numeric|min:0',
            ]);
    
            try {
                DB::beginTransaction();
                
                // Calculate total amount
                $startDate = new \DateTime($contract->start_date);
                $endDate = new \DateTime($request->end_date);
                $days = $startDate->diff($endDate)->days + 1;
                
                $totalAmount = $request->rental_fee * $days;
                if ($request->filled('discount')) {
                    $totalAmount -= $request->discount;
                }
                
                // Update the contract
                $contract->update([
                    'end_date' => $request->end_date,
                    'rental_fee' => $request->rental_fee,
                    'deposit_amount' => $request->deposit_amount ?? 0,
                    'payment_status' => $request->payment_status,
                    'notes' => $request->notes,
                    'total_amount' => $totalAmount,
                    'discount' => $request->discount ?? 0,
                ]);
                
                DB::commit();
                
                return redirect()->route('admin.contracts.index')
                    ->with('success', 'Contract updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                
                return redirect()->back()
                    ->with('error', 'An error occurred while updating the contract: ' . $e->getMessage())
                    ->withInput();
            }
        }
    
        /**
         * Remove the specified resource from storage.
         */
        public function destroy(Contract $contract)
        {
            try {
                DB::beginTransaction();
                
                // If contract is active, set car status back to available
                if ($contract->status === 'active') {
                    $car = $contract->car;
                    $car->update(['status' => 'available']);
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
         * Complete a contract.
         */
        public function complete(Request $request, Contract $contract)
        {
            // Only allow completing active contracts
            if ($contract->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active contracts can be completed.'
                ]);
            }
            
            $request->validate([
                'end_mileage' => 'required|integer|min:' . $contract->start_mileage,
            ]);
            
            try {
                DB::beginTransaction();
                
                // Update the contract
                $contract->update([
                    'status' => 'completed',
                    'end_mileage' => $request->end_mileage,
                ]);
                
                // Update the car
                $car = $contract->car;
                $car->update([
                    'status' => 'available',
                    'mileage' => $request->end_mileage,
                ]);
                
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
            // Only allow cancelling active contracts
            if ($contract->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active contracts can be cancelled.'
                ]);
            }
            
            try {
                DB::beginTransaction();
                
                // Update the contract
                $contract->update([
                    'status' => 'cancelled',
                ]);
                
                // Update the car
                $car = $contract->car;
                $car->update([
                    'status' => 'available',
                ]);
                
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
            // Only allow extending active contracts
            if ($contract->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active contracts can be extended.'
                ]);
            }
            
            $request->validate([
                'extension_days' => 'required|integer|min:1',
            ]);
            
            try {
                DB::beginTransaction();
                
                // Calculate new end date
                $newEndDate = clone $contract->end_date;
                $newEndDate->addDays($request->extension_days);
                
                // Calculate additional cost
                $additionalCost = $contract->rental_fee * $request->extension_days;
                
                // Update the contract
                $contract->update([
                    'end_date' => $newEndDate,
                    'extension_days' => $contract->extension_days + $request->extension_days,
                    'total_amount' => $contract->total_amount + $additionalCost,
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Contract extended by ' . $request->extension_days . ' days.',
                    'new_end_date' => $newEndDate->format('Y-m-d'),
                    'total_amount' => $contract->total_amount,
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
                ->where('status', 'active')
                ->whereDate('end_date', '<=', now()->addDays(2))
                ->latest('end_date');
            
            return DataTables::of($contracts)
                ->addColumn('client_name', function ($contract) {
                    return $contract->client->full_name;
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
                ->addColumn('actions', function ($contract) {
                    $buttons = '<div class="btn-group" role="group">';
                    
                    // View button
                    $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                    
                    // Complete button
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                    
                    // Extend button
                    $buttons .= '<button class="btn btn-sm btn-warning extend-contract" data-id="' . $contract->id . '" title="Extend"><i class="fas fa-calendar-plus"></i></button>';
                    
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['days_left', 'actions'])
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
                ->where('status', 'active')
                ->whereDate('end_date', '<', now())
                ->latest('end_date');
            
            return DataTables::of($contracts)
                ->addColumn('client_name', function ($contract) {
                    return $contract->client->full_name;
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
                    return '<span class="text-danger">' . number_format($estimatedPenalty, 2) . ' DH</span>';
                })
                ->addColumn('actions', function ($contract) {
                    $buttons = '<div class="btn-group" role="group">';
                    
                    // View button
                    $buttons .= '<a href="' . route('admin.contracts.show', $contract->id) . '" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>';
                    
                    // Complete button
                    $buttons .= '<button class="btn btn-sm btn-success complete-contract" data-id="' . $contract->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                    
                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['overdue_days', 'estimated_penalty', 'actions'])
                ->make(true);
        }
    }
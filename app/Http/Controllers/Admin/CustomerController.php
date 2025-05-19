<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_customers', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_customers', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_customers', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_customers', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of customers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            return view('admin.customers.index');
        } catch (Exception $e) {
            Log::error('Error displaying customers: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing customers.');
        }
    }

    /**
     * Get customers data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = Customer::query();
            
            // Apply filters if provided
            if ($request->has('status') && $request->status !== 'all') {
                $active = $request->status === 'active';
                $query->where('active', $active);
            }
            
            $customers = $query->get();
            
            return DataTables::of($customers)
                ->addColumn('status_label', function (Customer $customer) {
                    return $customer->active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('total_sales', function (Customer $customer) {
                    $total = $customer->getTotalSales();
                    return number_format($total, 2) . ' MAD';
                })
                ->addColumn('last_sale', function (Customer $customer) {
                    $lastSale = $customer->salesOrders()
                        ->where('status', '!=', 'cancelled')
                        ->orderBy('order_date', 'desc')
                        ->first();
                    
                    return $lastSale ? $lastSale->order_date->format('Y-m-d') : '-';
                })
                ->addColumn('action', function (Customer $customer) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_customers')) {
                        $actions .= '<a href="' . route('admin.customers.show', $customer->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_customers')) {
                        $actions .= '<a href="' . route('admin.customers.edit', $customer->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_customers')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $customer->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting customer data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve customers data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new customer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.customers.create');
        } catch (Exception $e) {
            Log::error('Error displaying customer create form: ' . $e->getMessage());
            return redirect()->route('admin.customers.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Store a newly created customer.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:customers',
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'tax_id' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Set default active status if not provided
            $data = $request->all();
            if (!isset($data['active'])) {
                $data['active'] = true;
            }
            
            // Create the customer
            $customer = Customer::create($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($customer)
                    ->withProperties(['customer_id' => $customer->id, 'customer_name' => $customer->name])
                    ->log('Created customer');
            }
            
            return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            // Get sales orders summary
            $salesOrdersCount = $customer->salesOrders()->count();
            $totalSales = $customer->getTotalSales();
            
            // Get recent sales orders
            $recentSalesOrders = $customer->salesOrders()
                ->orderBy('order_date', 'desc')
                ->limit(10)
                ->get();
            
            // Get sales history by month
            $salesHistory = SalesOrder::where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereYear('order_date', date('Y'))
                ->selectRaw('MONTH(order_date) as month, SUM(total_amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');
                
            // Fill in months with no sales
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyData[$i] = isset($salesHistory[$i]) ? $salesHistory[$i]->total : 0;
            }
            
            return view('admin.customers.show', compact('customer', 'salesOrdersCount', 'totalSales', 'recentSalesOrders', 'monthlyData'));
        } catch (Exception $e) {
            Log::error('Error displaying customer: ' . $e->getMessage());
            return redirect()->route('admin.customers.index')->with('error', 'An error occurred while accessing the customer.');
        }
    }

    /**
     * Show the form for editing the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            return view('admin.customers.edit', compact('customer'));
        } catch (Exception $e) {
            Log::error('Error displaying customer edit form: ' . $e->getMessage());
            return redirect()->route('admin.customers.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified customer.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:customers,code,' . $id,
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'tax_id' => 'nullable|string|max:50',
                'notes' => 'nullable|string',
                'active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Store old values for logging
            $oldValues = [
                'name' => $customer->name,
                'code' => $customer->code,
                'active' => $customer->active
            ];
            
            // Update the customer
            $customer->update($request->all());
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($customer)
                    ->withProperties([
                        'customer_id' => $customer->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $customer->name,
                            'code' => $customer->code,
                            'active' => $customer->active
                        ]
                    ])
                    ->log('Updated customer');
            }
            
            return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update customer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified customer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::withCount('salesOrders')->findOrFail($id);
            
            // Check if customer has sales orders
            if ($customer->sales_orders_count > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete customer with associated sales orders.'
                ], 422);
            }
            
            // Store customer data for logging
            $customerData = [
                'id' => $customer->id,
                'name' => $customer->name,
                'code' => $customer->code
            ];
            
            // Soft delete the customer
            $customer->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($customerData)
                    ->log('Deleted customer');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
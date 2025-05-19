<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class SupplierController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_suppliers', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_suppliers', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_suppliers', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_suppliers', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of suppliers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            return view('admin.suppliers.index');
        } catch (Exception $e) {
            Log::error('Error displaying suppliers: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing suppliers.');
        }
    }

    /**
     * Get suppliers data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = Supplier::query();
            
            // Apply filters if provided
            if ($request->has('status') && $request->status !== 'all') {
                $active = $request->status === 'active';
                $query->where('active', $active);
            }
            
            $suppliers = $query->get();
            
            return DataTables::of($suppliers)
                ->addColumn('status_label', function (Supplier $supplier) {
                    return $supplier->active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('total_purchases', function (Supplier $supplier) {
                    $total = $supplier->getTotalPurchases();
                    return number_format($total, 2) . ' MAD';
                })
                ->addColumn('last_purchase', function (Supplier $supplier) {
                    $lastPurchase = $supplier->purchaseOrders()
                        ->where('status', '!=', 'cancelled')
                        ->orderBy('order_date', 'desc')
                        ->first();
                    
                    return $lastPurchase ? $lastPurchase->order_date->format('Y-m-d') : '-';
                })
                ->addColumn('action', function (Supplier $supplier) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_suppliers')) {
                        $actions .= '<a href="' . route('admin.suppliers.show', $supplier->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_suppliers')) {
                        $actions .= '<a href="' . route('admin.suppliers.edit', $supplier->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_suppliers')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $supplier->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting supplier data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve suppliers data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.suppliers.create');
        } catch (Exception $e) {
            Log::error('Error displaying supplier create form: ' . $e->getMessage());
            return redirect()->route('admin.suppliers.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Store a newly created supplier.
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
                'code' => 'nullable|string|max:50|unique:suppliers',
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
            
            // Create the supplier
            $supplier = Supplier::create($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($supplier)
                    ->withProperties(['supplier_id' => $supplier->id, 'supplier_name' => $supplier->name])
                    ->log('Created supplier');
            }
            
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create supplier: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified supplier.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Get purchase orders summary
            $purchaseOrdersCount = $supplier->purchaseOrders()->count();
            $totalPurchases = $supplier->getTotalPurchases();
            
            // Get recent purchase orders
            $recentPurchaseOrders = $supplier->purchaseOrders()
                ->orderBy('order_date', 'desc')
                ->limit(10)
                ->get();
            
            // Get purchase history by month
            $purchaseHistory = PurchaseOrder::where('supplier_id', $supplier->id)
                ->where('status', '!=', 'cancelled')
                ->whereYear('order_date', date('Y'))
                ->selectRaw('MONTH(order_date) as month, SUM(total_amount) as total')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month');
                
            // Fill in months with no purchases
            $monthlyData = [];
            for ($i = 1; $i <= 12; $i++) {
                $monthlyData[$i] = isset($purchaseHistory[$i]) ? $purchaseHistory[$i]->total : 0;
            }
            
            return view('admin.suppliers.show', compact('supplier', 'purchaseOrdersCount', 'totalPurchases', 'recentPurchaseOrders', 'monthlyData'));
        } catch (Exception $e) {
            Log::error('Error displaying supplier: ' . $e->getMessage());
            return redirect()->route('admin.suppliers.index')->with('error', 'An error occurred while accessing the supplier.');
        }
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            return view('admin.suppliers.edit', compact('supplier'));
        } catch (Exception $e) {
            Log::error('Error displaying supplier edit form: ' . $e->getMessage());
            return redirect()->route('admin.suppliers.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified supplier.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:suppliers,code,' . $id,
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
                'name' => $supplier->name,
                'code' => $supplier->code,
                'active' => $supplier->active
            ];
            
            // Update the supplier
            $supplier->update($request->all());
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($supplier)
                    ->withProperties([
                        'supplier_id' => $supplier->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $supplier->name,
                            'code' => $supplier->code,
                            'active' => $supplier->active
                        ]
                    ])
                    ->log('Updated supplier');
            }
            
            return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update supplier: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified supplier.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $supplier = Supplier::withCount('purchaseOrders')->findOrFail($id);
            
            // Check if supplier has purchase orders
            if ($supplier->purchase_orders_count > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete supplier with associated purchase orders.'
                ], 422);
            }
            
            // Store supplier data for logging
            $supplierData = [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'code' => $supplier->code
            ];
            
            // Soft delete the supplier
            $supplier->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($supplierData)
                    ->log('Deleted supplier');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete supplier: ' . $e->getMessage()
            ], 500);
        }
    }
}
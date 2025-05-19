<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view suppliers')) {
                abort(403, 'Unauthorized action.');
            }

            $totalSuppliers = Supplier::count();
            $activeSuppliers = Supplier::where('active', true)->count();

            return view('admin.suppliers.index', compact('totalSuppliers', 'activeSuppliers'));
        } catch (Exception $e) {
            Log::error('Error displaying suppliers: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing suppliers.');
        }
    }

    /**
     * Get suppliers data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view suppliers')) {
                return response()->json([
                    'success' => false, 
                    'error' => 'You do not have permission to view suppliers.'
                ], 403);
            }

            $query = Supplier::query();
            
            // Apply filters if provided
            if ($request->has('active') && $request->active !== '') {
                $query->where('active', $request->active);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $suppliers = $query->get();

            return DataTables::of($suppliers)
                ->addColumn('orders_count', function (Supplier $supplier) {
                    return $supplier->purchaseOrders()->count();
                })
                ->addColumn('total_purchases', function (Supplier $supplier) {
                    $total = $supplier->getTotalPurchases();
                    return number_format($total, 2);
                })
                ->addColumn('action', function (Supplier $supplier) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view suppliers')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $supplier->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('edit suppliers')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $supplier->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('delete suppliers')) {
                        $disabledClass = ($supplier->purchaseOrders()->count() > 0) ? 'disabled' : '';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete ' . $disabledClass . '" data-id="' . $supplier->id . '" ' . $disabledClass . '>
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting supplier data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve suppliers data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create suppliers')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create suppliers.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:suppliers',
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'tax_id' => 'nullable|string|max:50',
                'active' => 'boolean',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            if (!isset($data['active'])) {
                $data['active'] = true;
            }

            $supplier = Supplier::create($data);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($supplier)
                    ->withProperties(['supplier_id' => $supplier->id, 'supplier_name' => $supplier->name])
                    ->log('Created supplier');
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully.',
                'supplier' => $supplier
            ]);
        } catch (Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view suppliers')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view suppliers.'
                ], 403);
            }

            $supplier = Supplier::withCount('purchaseOrders')->findOrFail($id);
            $supplier->total_purchases = $supplier->getTotalPurchases();
            $supplier->recent_orders = $supplier->purchaseOrders()
                ->with('warehouse')
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'supplier' => $supplier
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving supplier details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve supplier details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit suppliers')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            return response()->json([
                'success' => true,
                'supplier' => $supplier
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving supplier for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve supplier for editing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit suppliers')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:suppliers,code,' . $id,
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'tax_id' => 'nullable|string|max:50',
                'active' => 'boolean',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('_method');
            
            $oldValues = [
                'code' => $supplier->code,
                'name' => $supplier->name,
                'active' => $supplier->active
            ];

            $supplier->update($data);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($supplier)
                    ->withProperties([
                        'supplier_id' => $supplier->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'code' => $supplier->code,
                            'name' => $supplier->name,
                            'active' => $supplier->active
                        ]
                    ])
                    ->log('Updated supplier');
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully.',
                'supplier' => $supplier
            ]);
        } catch (Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete suppliers')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete suppliers.'
                ], 403);
            }

            $supplier = Supplier::findOrFail($id);

            // Check if supplier has purchase orders
            if ($supplier->purchaseOrders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete supplier with purchase orders. Please deactivate it instead.'
                ], 422);
            }

            $supplierData = [
                'id' => $supplier->id,
                'code' => $supplier->code,
                'name' => $supplier->name
            ];

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

    /**
     * Get all suppliers for select dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSuppliersList()
    {
        try {
            $suppliers = Supplier::where('active', true)
                ->select('id', 'name', 'code', 'contact_person', 'email', 'phone')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'suppliers' => $suppliers
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving suppliers list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve suppliers list: ' . $e->getMessage()
            ], 500);
        }
    }
}
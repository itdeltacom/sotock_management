<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class WarehouseController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_warehouses', ['only' => ['index', 'show', 'data', 'stock', 'stockData']]);
        $this->middleware('permission:create_warehouses', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_warehouses', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_warehouses', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of warehouses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            return view('admin.warehouses.index');
        } catch (Exception $e) {
            Log::error('Error displaying warehouses: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing warehouses.');
        }
    }

    /**
     * Get warehouses data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = Warehouse::query();
            
            // Apply filters if provided
            if ($request->has('status') && $request->status !== 'all') {
                $active = $request->status === 'active';
                $query->where('active', $active);
            }
            
            $warehouses = $query->get();
            
            return DataTables::of($warehouses)
                ->addColumn('status_label', function (Warehouse $warehouse) {
                    return $warehouse->active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('products_count', function (Warehouse $warehouse) {
                    return $warehouse->productStock()->count();
                })
                ->addColumn('total_value', function (Warehouse $warehouse) {
                    $totalValue = $warehouse->productStock()
                        ->sum(DB::raw('available_quantity * cmup'));
                    return number_format($totalValue, 2) . ' MAD';
                })
                ->addColumn('action', function (Warehouse $warehouse) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_warehouses')) {
                        $actions .= '<a href="' . route('admin.warehouses.show', $warehouse->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_warehouses')) {
                        $actions .= '<a href="' . route('admin.warehouses.edit', $warehouse->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_warehouses')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $warehouse->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting warehouse data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouses data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new warehouse.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            return view('admin.warehouses.create');
        } catch (Exception $e) {
            Log::error('Error displaying warehouse create form: ' . $e->getMessage());
            return redirect()->route('admin.warehouses.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Store a newly created warehouse.
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
                'code' => 'nullable|string|max:50|unique:warehouses',
                'location' => 'nullable|string|max:255',
                'description' => 'nullable|string',
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
            
            // Create the warehouse
            $warehouse = Warehouse::create($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($warehouse)
                    ->withProperties(['warehouse_id' => $warehouse->id, 'warehouse_name' => $warehouse->name])
                    ->log('Created warehouse');
            }
            
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating warehouse: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create warehouse: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified warehouse.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            // Get warehouse statistics
            $totalProducts = $warehouse->productStock()->count();
            $totalQuantity = $warehouse->productStock()->sum('available_quantity');
            $reservedQuantity = $warehouse->productStock()->sum('reserved_quantity');
            $totalValue = $warehouse->productStock()->sum(DB::raw('available_quantity * cmup'));
            
            // Get recent stock movements
            $recentMovements = $warehouse->stockMovements()
                ->with(['product', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get low stock products
            $lowStockProducts = $warehouse->productStock()
                ->with('product')
                ->whereRaw('available_quantity < min_stock')
                ->where('min_stock', '>', 0)
                ->orderByRaw('(min_stock - available_quantity) / min_stock DESC')
                ->limit(10)
                ->get();
            
            return view('admin.warehouses.show', compact('warehouse', 'totalProducts', 'totalQuantity', 'reservedQuantity', 'totalValue', 'recentMovements', 'lowStockProducts'));
        } catch (Exception $e) {
            Log::error('Error displaying warehouse: ' . $e->getMessage());
            return redirect()->route('admin.warehouses.index')->with('error', 'An error occurred while accessing the warehouse.');
        }
    }

    /**
     * Show the form for editing the specified warehouse.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            return view('admin.warehouses.edit', compact('warehouse'));
        } catch (Exception $e) {
            Log::error('Error displaying warehouse edit form: ' . $e->getMessage());
            return redirect()->route('admin.warehouses.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified warehouse.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50|unique:warehouses,code,' . $id,
                'location' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Store old values for logging
            $oldValues = [
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'active' => $warehouse->active
            ];
            
            // Update the warehouse
            $warehouse->update($request->all());
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($warehouse)
                    ->withProperties([
                        'warehouse_id' => $warehouse->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $warehouse->name,
                            'code' => $warehouse->code,
                            'active' => $warehouse->active
                        ]
                    ])
                    ->log('Updated warehouse');
            }
            
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating warehouse: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update warehouse: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified warehouse.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::withCount(['productStock', 'stockPackages'])->findOrFail($id);
            
            // Check if warehouse has stock
            if ($warehouse->product_stock_count > 0 || $warehouse->stock_packages_count > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete warehouse with existing stock. Please transfer all stock first.'
                ], 422);
            }
            
            // Check if warehouse is used in any active purchase/sales order
            $pendingPurchaseOrders = $warehouse->purchaseOrders()
                ->whereIn('status', ['draft', 'confirmed', 'partially_received'])
                ->count();
                
            $pendingSalesOrders = $warehouse->salesOrders()
                ->whereIn('status', ['draft', 'confirmed', 'partially_delivered'])
                ->count();
                
            if ($pendingPurchaseOrders > 0 || $pendingSalesOrders > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete warehouse with pending purchase or sales orders.'
                ], 422);
            }
            
            // Store warehouse data for logging
            $warehouseData = [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code
            ];
            
            // Delete the warehouse
            $warehouse->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($warehouseData)
                    ->log('Deleted warehouse');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting warehouse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display warehouse stock.
     *
     * @return \Illuminate\View\View
     */
    public function stock()
    {
        try {
            $warehouses = Warehouse::where('active', true)->get();
            
            return view('admin.warehouses.stock', compact('warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying warehouse stock: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing warehouse stock.');
        }
    }

    /**
     * Get warehouse stock data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stockData(Request $request)
    {
        try {
            $query = ProductWarehouseStock::with(['product', 'warehouse']);
            
            // Apply warehouse filter
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            // Apply product filter
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }
            
            // Apply stock level filter
            if ($request->has('stock_level')) {
                switch ($request->stock_level) {
                    case 'low':
                        $query->whereRaw('available_quantity < min_stock')
                            ->where('min_stock', '>', 0);
                        break;
                    case 'out':
                        $query->where('available_quantity', 0);
                        break;
                    case 'over':
                        $query->whereRaw('available_quantity > max_stock')
                            ->where('max_stock', '>', 0);
                        break;
                }
            }
            
            $stockItems = $query->get();
            
            return DataTables::of($stockItems)
                ->addColumn('product_code', function (ProductWarehouseStock $item) {
                    return $item->product->code;
                })
                ->addColumn('product_name', function (ProductWarehouseStock $item) {
                    return $item->product->name;
                })
                ->addColumn('warehouse_name', function (ProductWarehouseStock $item) {
                    return $item->warehouse->name;
                })
                ->addColumn('total_quantity', function (ProductWarehouseStock $item) {
                    return $item->getTotalQuantity();
                })
                ->addColumn('stock_value', function (ProductWarehouseStock $item) {
                    return number_format($item->available_quantity * $item->cmup, 2) . ' MAD';
                })
                ->addColumn('stock_status', function (ProductWarehouseStock $item) {
                    if ($item->isLowStock()) {
                        return '<span class="badge bg-warning">Low Stock</span>';
                    } elseif ($item->available_quantity == 0) {
                        return '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($item->isOverStock()) {
                        return '<span class="badge bg-info">Over Stock</span>';
                    } else {
                        return '<span class="badge bg-success">Normal</span>';
                    }
                })
                ->addColumn('action', function (ProductWarehouseStock $item) {
                    $actions = '';
                    
                    if (Auth::guard('admin')->user()->can('view_products')) {
                        $actions .= '<a href="' . route('admin.products.show', $item->product_id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i> View Product
                        </a> ';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['stock_status', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting warehouse stock data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouse stock data.'
            ], 500);
        }
    }
}
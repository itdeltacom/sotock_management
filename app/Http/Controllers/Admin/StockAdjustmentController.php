<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouseStock;
use App\Models\StockPackage;
use App\Services\Warehouse\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class StockAdjustmentController extends Controller
{
    protected $warehouseService;

    /**
     * Create a new controller instance.
     *
     * @param WarehouseService $warehouseService
     */
    public function __construct(WarehouseService $warehouseService)
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_stock_adjustments', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_stock_adjustments', ['only' => ['create', 'store']]);
        $this->middleware('permission:process_stock_adjustments', ['only' => ['confirm']]);
        $this->middleware('permission:delete_stock_adjustments', ['only' => ['destroy']]);

        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of stock adjustments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-adjustments.index', compact('warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock adjustments: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock adjustments.');
        }
    }

    /**
     * Get stock adjustments data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = StockAdjustment::with(['warehouse', 'createdBy']);
            
            // Apply filters
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            if ($request->has('type') && $request->type != 'all') {
                $query->where('type', $request->type);
            }
            
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('adjustment_date', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('adjustment_date', '<=', $request->date_to);
            }
            
            $adjustments = $query->orderBy('created_at', 'desc')->get();
            
            return DataTables::of($adjustments)
                ->editColumn('adjustment_date', function (StockAdjustment $adjustment) {
                    return $adjustment->adjustment_date->format('Y-m-d');
                })
                ->addColumn('warehouse_name', function (StockAdjustment $adjustment) {
                    return $adjustment->warehouse->name;
                })
                ->addColumn('created_by', function (StockAdjustment $adjustment) {
                    return $adjustment->createdBy ? $adjustment->createdBy->name : 'System';
                })
                ->addColumn('items_count', function (StockAdjustment $adjustment) {
                    return $adjustment->items->count();
                })
                ->addColumn('type_label', function (StockAdjustment $adjustment) {
                    $typeMap = [
                        'addition' => '<span class="badge bg-success">Addition</span>',
                        'subtraction' => '<span class="badge bg-danger">Subtraction</span>'
                    ];
                    return $typeMap[$adjustment->type] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('status_label', function (StockAdjustment $adjustment) {
                    $statusMap = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'confirmed' => '<span class="badge bg-success">Confirmed</span>'
                    ];
                    return $statusMap[$adjustment->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (StockAdjustment $adjustment) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_stock_adjustments')) {
                        $actions .= '<a href="' . route('admin.stock-adjustments.show', $adjustment->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Confirm button (for draft adjustments)
                    if (Auth::guard('admin')->user()->can('process_stock_adjustments') && $adjustment->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-confirm me-1" data-id="' . $adjustment->id . '">
                            <i class="fas fa-check"></i> Confirm
                        </button> ';
                    }
                    
                    // Delete button (only for draft adjustments)
                    if (Auth::guard('admin')->user()->can('delete_stock_adjustments') && $adjustment->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $adjustment->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['type_label', 'status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock adjustments data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock adjustments data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new stock adjustment.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-adjustments.create', compact('warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying stock adjustment create form: ' . $e->getMessage());
            return redirect()->route('admin.stock-adjustments.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Get available packages for a product in a warehouse.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailablePackages(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'required|exists:warehouses,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $packages = StockPackage::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->where('available', true)
                ->where('quantity', '>', 0)
                ->select('id', 'lot_number', 'quantity', 'expiry_date')
                ->get()
                ->map(function($package) {
                    return [
                        'id' => $package->id,
                        'lot_number' => $package->lot_number ? $package->lot_number : 'No Lot',
                        'quantity' => $package->quantity,
                        'expiry_date' => $package->expiry_date ? $package->expiry_date->format('Y-m-d') : null
                    ];
                });
                
            $stock = ProductWarehouseStock::where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
                
            $availableQuantity = $stock ? $stock->available_quantity : 0;
            $product = Product::find($request->product_id);
            
            return response()->json([
                'success' => true,
                'packages' => $packages,
                'available_quantity' => $availableQuantity,
                'unit' => $product->unit
            ]);
        } catch (Exception $e) {
            Log::error('Error getting available packages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve available packages.'
            ], 500);
        }
    }

    /**
     * Store a newly created stock adjustment.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'nullable|string|max:50|unique:stock_adjustments',
                'warehouse_id' => 'required|exists:warehouses,id',
                'adjustment_date' => 'required|date',
                'type' => 'required|in:addition,subtraction',
                'reason' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'required|in:draft,confirmed',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.stock_package_id' => 'nullable|exists:stock_packages,id',
                'items.*.lot_number' => 'nullable|string|max:100',
                'items.*.unit_cost' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Validate stock availability for subtractions
            if ($request->type == 'subtraction') {
                $warehouseId = $request->warehouse_id;
                
                foreach ($request->items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    $packageId = $item['stock_package_id'] ?? null;
                    
                    if ($packageId) {
                        // Check package availability
                        $package = StockPackage::find($packageId);
                        if (!$package || $package->product_id != $productId || $package->warehouse_id != $warehouseId || !$package->available || $package->quantity < $quantity) {
                            $product = Product::find($productId);
                            return redirect()->back()
                                ->with('error', 'Insufficient stock in selected package for product: ' . $product->name)
                                ->withInput();
                        }
                    } else {
                        // Check general stock availability
                        $stock = ProductWarehouseStock::where('product_id', $productId)
                            ->where('warehouse_id', $warehouseId)
                            ->first();
                            
                        if (!$stock || $stock->available_quantity < $quantity) {
                            $product = Product::find($productId);
                            return redirect()->back()
                                ->with('error', 'Insufficient stock for product: ' . $product->name)
                                ->withInput();
                        }
                    }
                }
            }
            
            // Create the adjustment
            $data = $request->all();
            $adjustment = $this->warehouseService->createStockAdjustment($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($adjustment)
                    ->withProperties([
                        'adjustment_id' => $adjustment->id,
                        'reference_no' => $adjustment->reference_no,
                        'warehouse_id' => $adjustment->warehouse_id,
                        'type' => $adjustment->type
                    ])
                    ->log('Created stock adjustment');
            }
            
            return redirect()->route('admin.stock-adjustments.show', $adjustment->id)
                ->with('success', 'Stock adjustment created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock adjustment: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock adjustment: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified stock adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $adjustment = StockAdjustment::with(['items.product', 'warehouse', 'createdBy'])->findOrFail($id);
            
            return view('admin.stock-adjustments.show', compact('adjustment'));
        } catch (Exception $e) {
            Log::error('Error displaying stock adjustment: ' . $e->getMessage());
            return redirect()->route('admin.stock-adjustments.index')->with('error', 'An error occurred while accessing the stock adjustment.');
        }
    }

    /**
     * Confirm the stock adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            $adjustment = StockAdjustment::findOrFail($id);
            
            // Check if adjustment can be confirmed
            if ($adjustment->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft adjustments can be confirmed.'
                ], 422);
            }
            
            // Confirm adjustment
            $confirmedAdjustment = $this->warehouseService->confirmAdjustment($adjustment);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($confirmedAdjustment)
                    ->withProperties([
                        'adjustment_id' => $confirmedAdjustment->id,
                        'reference_no' => $confirmedAdjustment->reference_no
                    ])
                    ->log('Confirmed stock adjustment');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment confirmed successfully.',
                'adjustment' => $confirmedAdjustment
            ]);
        } catch (Exception $e) {
            Log::error('Error confirming stock adjustment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm stock adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stock adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $adjustment = StockAdjustment::findOrFail($id);
            
            // Check if adjustment can be deleted
            if ($adjustment->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft adjustments can be deleted.'
                ], 422);
            }
            
            // Store adjustment data for logging
            $adjustmentData = [
                'id' => $adjustment->id,
                'reference_no' => $adjustment->reference_no,
                'warehouse_id' => $adjustment->warehouse_id,
                'type' => $adjustment->type
            ];
            
            // Delete the adjustment (will cascade to items)
            $adjustment->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($adjustmentData)
                    ->log('Deleted stock adjustment');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting stock adjustment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete stock adjustment: ' . $e->getMessage()
            ], 500);
        }
    }
}
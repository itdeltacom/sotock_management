<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
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

class StockTransferController extends Controller
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
        $this->middleware('permission:view_stock_transfers', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_stock_transfers', ['only' => ['create', 'store']]);
        $this->middleware('permission:process_stock_transfers', ['only' => ['confirm']]);
        $this->middleware('permission:delete_stock_transfers', ['only' => ['destroy']]);

        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of stock transfers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-transfers.index', compact('warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock transfers: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock transfers.');
        }
    }

    /**
     * Get stock transfers data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = StockTransfer::with(['sourceWarehouse', 'destinationWarehouse', 'createdBy']);
            
            // Apply filters
            if ($request->has('source_warehouse_id') && $request->source_warehouse_id) {
                $query->where('source_warehouse_id', $request->source_warehouse_id);
            }
            
            if ($request->has('destination_warehouse_id') && $request->destination_warehouse_id) {
                $query->where('destination_warehouse_id', $request->destination_warehouse_id);
            }
            
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('transfer_date', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('transfer_date', '<=', $request->date_to);
            }
            
            $transfers = $query->orderBy('created_at', 'desc')->get();
            
            return DataTables::of($transfers)
                ->editColumn('transfer_date', function (StockTransfer $transfer) {
                    return $transfer->transfer_date->format('Y-m-d');
                })
                ->addColumn('source_warehouse', function (StockTransfer $transfer) {
                    return $transfer->sourceWarehouse->name;
                })
                ->addColumn('destination_warehouse', function (StockTransfer $transfer) {
                    return $transfer->destinationWarehouse->name;
                })
                ->addColumn('created_by', function (StockTransfer $transfer) {
                    return $transfer->createdBy ? $transfer->createdBy->name : 'System';
                })
                ->addColumn('items_count', function (StockTransfer $transfer) {
                    return $transfer->items->count();
                })
                ->addColumn('status_label', function (StockTransfer $transfer) {
                    $statusMap = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'completed' => '<span class="badge bg-success">Completed</span>'
                    ];
                    return $statusMap[$transfer->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (StockTransfer $transfer) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_stock_transfers')) {
                        $actions .= '<a href="' . route('admin.stock-transfers.show', $transfer->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Confirm button (for draft transfers)
                    if (Auth::guard('admin')->user()->can('process_stock_transfers') && $transfer->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-confirm me-1" data-id="' . $transfer->id . '">
                            <i class="fas fa-check"></i> Confirm
                        </button> ';
                    }
                    
                    // Delete button (only for draft transfers)
                    if (Auth::guard('admin')->user()->can('delete_stock_transfers') && $transfer->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $transfer->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock transfers data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock transfers data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new stock transfer.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-transfers.create', compact('warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying stock transfer create form: ' . $e->getMessage());
            return redirect()->route('admin.stock-transfers.index')->with('error', 'An error occurred while accessing the create form.');
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
     * Store a newly created stock transfer.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'nullable|string|max:50|unique:stock_transfers',
                'source_warehouse_id' => 'required|exists:warehouses,id',
                'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
                'transfer_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'required|in:draft,completed',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.source_package_id' => 'nullable|exists:stock_packages,id',
                'items.*.lot_number' => 'nullable|string|max:100',
                'items.*.expiry_date' => 'nullable|date',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Validate stock availability
            $sourceWarehouseId = $request->source_warehouse_id;
            
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $packageId = $item['source_package_id'] ?? null;
                
                if ($packageId) {
                    // Check package availability
                    $package = StockPackage::find($packageId);
                    if (!$package || $package->product_id != $productId || $package->warehouse_id != $sourceWarehouseId || !$package->available || $package->quantity < $quantity) {
                        $product = Product::find($productId);
                        return redirect()->back()
                            ->with('error', 'Insufficient stock in selected package for product: ' . $product->name)
                            ->withInput();
                    }
                } else {
                    // Check general stock availability
                    $stock = ProductWarehouseStock::where('product_id', $productId)
                        ->where('warehouse_id', $sourceWarehouseId)
                        ->first();
                        
                    if (!$stock || $stock->available_quantity < $quantity) {
                        $product = Product::find($productId);
                        return redirect()->back()
                            ->with('error', 'Insufficient stock for product: ' . $product->name)
                            ->withInput();
                    }
                }
            }
            
            // Create the transfer
            $data = $request->all();
            $transfer = $this->warehouseService->createStockTransfer($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($transfer)
                    ->withProperties([
                        'transfer_id' => $transfer->id,
                        'reference_no' => $transfer->reference_no,
                        'source_warehouse_id' => $transfer->source_warehouse_id,
                        'destination_warehouse_id' => $transfer->destination_warehouse_id
                    ])
                    ->log('Created stock transfer');
            }
            
            return redirect()->route('admin.stock-transfers.show', $transfer->id)
                ->with('success', 'Stock transfer created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock transfer: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock transfer: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified stock transfer.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $transfer = StockTransfer::with(['items.product', 'sourceWarehouse', 'destinationWarehouse', 'createdBy'])->findOrFail($id);
            
            return view('admin.stock-transfers.show', compact('transfer'));
        } catch (Exception $e) {
            Log::error('Error displaying stock transfer: ' . $e->getMessage());
            return redirect()->route('admin.stock-transfers.index')->with('error', 'An error occurred while accessing the stock transfer.');
        }
    }

    /**
     * Confirm the stock transfer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            $transfer = StockTransfer::findOrFail($id);
            
            // Check if transfer can be confirmed
            if ($transfer->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft transfers can be confirmed.'
                ], 422);
            }
            
            // Confirm transfer
            $confirmedTransfer = $this->warehouseService->confirmTransfer($transfer);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($confirmedTransfer)
                    ->withProperties([
                        'transfer_id' => $confirmedTransfer->id,
                        'reference_no' => $confirmedTransfer->reference_no
                    ])
                    ->log('Confirmed stock transfer');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock transfer confirmed successfully.',
                'transfer' => $confirmedTransfer
            ]);
        } catch (Exception $e) {
            Log::error('Error confirming stock transfer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm stock transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stock transfer.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $transfer = StockTransfer::findOrFail($id);
            
            // Check if transfer can be deleted
            if ($transfer->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft transfers can be deleted.'
                ], 422);
            }
            
            // Store transfer data for logging
            $transferData = [
                'id' => $transfer->id,
                'reference_no' => $transfer->reference_no,
                'source_warehouse_id' => $transfer->source_warehouse_id,
                'destination_warehouse_id' => $transfer->destination_warehouse_id
            ];
            
            // Delete the transfer (will cascade to items)
            $transfer->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($transferData)
                    ->log('Deleted stock transfer');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock transfer deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting stock transfer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete stock transfer: ' . $e->getMessage()
            ], 500);
        }
    }
}
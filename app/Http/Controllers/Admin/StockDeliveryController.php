<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockDelivery;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Sales\StockDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class StockDeliveryController extends Controller
{
    protected $stockDeliveryService;

    /**
     * Create a new controller instance.
     *
     * @param StockDeliveryService $stockDeliveryService
     */
    public function __construct(StockDeliveryService $stockDeliveryService)
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_stock_deliveries', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_stock_deliveries', ['only' => ['create', 'createFromSO', 'store', 'storeFromSO']]);
        $this->middleware('permission:process_stock_deliveries', ['only' => ['process']]);
        $this->middleware('permission:delete_stock_deliveries', ['only' => ['destroy']]);

        $this->stockDeliveryService = $stockDeliveryService;
    }

    /**
     * Display a listing of stock deliveries.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $customers = Customer::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-deliveries.index', compact('customers', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock deliveries: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock deliveries.');
        }
    }

    /**
     * Get stock deliveries data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            // Apply filters
            $filters = [
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'sales_order_id' => $request->sales_order_id,
                'status' => $request->status != 'all' ? $request->status : null,
                'reference_no' => $request->reference_no,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Get deliveries from service
            $deliveries = $this->stockDeliveryService->getDeliveries($filters);
            
            return DataTables::of($deliveries)
                ->editColumn('delivery_date', function (StockDelivery $delivery) {
                    return $delivery->delivery_date->format('Y-m-d');
                })
                ->addColumn('customer_name', function (StockDelivery $delivery) {
                    return $delivery->customer->name;
                })
                ->addColumn('warehouse_name', function (StockDelivery $delivery) {
                    return $delivery->warehouse->name;
                })
                ->addColumn('sales_order_ref', function (StockDelivery $delivery) {
                    return $delivery->salesOrder ? $delivery->salesOrder->reference_no : 'Direct Delivery';
                })
                ->addColumn('items_count', function (StockDelivery $delivery) {
                    return $delivery->items->count();
                })
                ->addColumn('status_label', function (StockDelivery $delivery) {
                    $statusMap = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'completed' => '<span class="badge bg-success">Completed</span>',
                        'partial' => '<span class="badge bg-info">Partial</span>'
                    ];
                    return $statusMap[$delivery->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (StockDelivery $delivery) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_stock_deliveries')) {
                        $actions .= '<a href="' . route('admin.stock-deliveries.show', $delivery->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Process button (for pending deliveries)
                    if (Auth::guard('admin')->user()->can('process_stock_deliveries') && $delivery->status == 'pending') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-process me-1" data-id="' . $delivery->id . '">
                            <i class="fas fa-check"></i> Process
                        </button> ';
                    }
                    
                    // Delete button (only for pending deliveries)
                    if (Auth::guard('admin')->user()->can('delete_stock_deliveries') && $delivery->status == 'pending') {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $delivery->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock deliveries data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock deliveries data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new direct stock delivery.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $customers = Customer::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            // Generate reference number
            $reference = StockDelivery::generateReferenceNumber();
            
            return view('admin.stock-deliveries.create', compact('customers', 'warehouses', 'products', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying stock delivery create form: ' . $e->getMessage());
            return redirect()->route('admin.stock-deliveries.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Show the form for creating a stock delivery from sales order.
     *
     * @param int $salesOrderId
     * @return \Illuminate\View\View
     */
    public function createFromSO($salesOrderId)
    {
        try {
            $salesOrder = SalesOrder::with(['items.product', 'customer', 'warehouse'])->findOrFail($salesOrderId);
            
            // Check if SO can be delivered
            if (!$salesOrder->canBeDelivered()) {
                return redirect()->route('admin.sales-orders.show', $salesOrderId)
                    ->with('error', 'This sales order cannot be delivered.');
            }
            
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            // Generate reference number
            $reference = StockDelivery::generateReferenceNumber();
            
            return view('admin.stock-deliveries.create-from-so', compact('salesOrder', 'warehouses', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying stock delivery create form from SO: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.show', $salesOrderId)
                ->with('error', 'An error occurred while creating delivery from sales order.');
        }
    }

    /**
     * Get product stock information for AJAX requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductStock(Request $request)
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
            
            $stock = DB::table('product_warehouse_stock')
                ->where('product_id', $request->product_id)
                ->where('warehouse_id', $request->warehouse_id)
                ->first();
            
            $availableQuantity = $stock ? $stock->available_quantity : 0;
            $product = Product::find($request->product_id);
            
            return response()->json([
                'success' => true,
                'available_quantity' => $availableQuantity,
                'unit' => $product->unit
            ]);
        } catch (Exception $e) {
            Log::error('Error getting product stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve product stock information.'
            ], 500);
        }
    }

    /**
     * Store a newly created direct stock delivery.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:stock_deliveries',
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'delivery_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.lot_number' => 'nullable|string|max:100',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Validate stock availability
            $warehouseId = $request->warehouse_id;
            
            foreach ($request->items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                
                $stock = DB::table('product_warehouse_stock')
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->first();
                    
                if (!$stock || $stock->available_quantity < $quantity) {
                    $product = Product::find($productId);
                    return redirect()->back()
                        ->with('error', 'Insufficient stock for product: ' . $product->name)
                        ->withInput();
                }
            }
            
            // Prepare data
            $data = $request->except('items');
            $data['items'] = [];
            
            // Format items
            foreach ($request->items as $item) {
                $data['items'][] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            
            // Create the delivery
            $delivery = $this->stockDeliveryService->createDirectDelivery($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($delivery)
                    ->withProperties([
                        'delivery_id' => $delivery->id,
                        'reference_no' => $delivery->reference_no,
                        'customer_id' => $delivery->customer_id
                    ])
                    ->log('Created direct stock delivery');
            }
            
            return redirect()->route('admin.stock-deliveries.show', $delivery->id)
                ->with('success', 'Stock delivery created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock delivery: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock delivery: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store a stock delivery from sales order.
     *
     * @param Request $request
     * @param int $salesOrderId
     * @return \Illuminate\Http\Response
     */
    public function storeFromSO(Request $request, $salesOrderId)
    {
        try {
            $salesOrder = SalesOrder::findOrFail($salesOrderId);
            
            // Check if SO can be delivered
            if (!$salesOrder->canBeDelivered()) {
                return redirect()->route('admin.sales-orders.show', $salesOrderId)
                    ->with('error', 'This sales order cannot be delivered.');
            }
            
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:stock_deliveries',
                'warehouse_id' => 'required|exists:warehouses,id',
                'delivery_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed',
                'items' => 'required|array',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.lot_number' => 'nullable|string|max:100',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Create the delivery
            $delivery = $this->stockDeliveryService->createDeliveryFromSO($salesOrder, $request->all());
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($delivery)
                    ->withProperties([
                        'delivery_id' => $delivery->id,
                        'reference_no' => $delivery->reference_no,
                        'sales_order_id' => $delivery->sales_order_id
                    ])
                    ->log('Created stock delivery from sales order');
            }
            
            return redirect()->route('admin.stock-deliveries.show', $delivery->id)
                ->with('success', 'Stock delivery created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock delivery from SO: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock delivery: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified stock delivery.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $delivery = StockDelivery::with(['items.product', 'customer', 'warehouse', 'salesOrder', 'deliveredBy'])->findOrFail($id);
            
            return view('admin.stock-deliveries.show', compact('delivery'));
        } catch (Exception $e) {
            Log::error('Error displaying stock delivery: ' . $e->getMessage());
            return redirect()->route('admin.stock-deliveries.index')->with('error', 'An error occurred while accessing the stock delivery.');
        }
    }

    /**
     * Process the pending stock delivery.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function process($id)
    {
        try {
            $delivery = StockDelivery::findOrFail($id);
            
            // Check if delivery can be processed
            if ($delivery->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending deliveries can be processed.'
                ], 422);
            }
            
            // Process the delivery
            $processedDelivery = $this->stockDeliveryService->processDelivery($delivery);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($processedDelivery)
                    ->withProperties([
                        'delivery_id' => $processedDelivery->id,
                        'reference_no' => $processedDelivery->reference_no
                    ])
                    ->log('Processed stock delivery');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock delivery processed successfully.',
                'delivery' => $processedDelivery
            ]);
        } catch (Exception $e) {
            Log::error('Error processing stock delivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process stock delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stock delivery.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $delivery = StockDelivery::findOrFail($id);
            
            // Check if delivery can be deleted
            if ($delivery->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending deliveries can be deleted.'
                ], 422);
            }
            
            // Store delivery data for logging
            $deliveryData = [
                'id' => $delivery->id,
                'reference_no' => $delivery->reference_no,
                'customer_id' => $delivery->customer_id,
                'sales_order_id' => $delivery->sales_order_id
            ];
            
            // Delete the delivery (will cascade to items)
            $delivery->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($deliveryData)
                    ->log('Deleted stock delivery');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock delivery deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting stock delivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete stock delivery: ' . $e->getMessage()
            ], 500);
        }
    }
}
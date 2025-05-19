<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class SalesOrderController extends Controller
{
    protected $salesOrderService;

    /**
     * Create a new controller instance.
     *
     * @param SalesOrderService $salesOrderService
     */
    public function __construct(SalesOrderService $salesOrderService)
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_sales_orders', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_sales_orders', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_sales_orders', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_sales_orders', ['only' => ['destroy']]);
        $this->middleware('permission:confirm_sales_orders', ['only' => ['confirm']]);
        $this->middleware('permission:cancel_sales_orders', ['only' => ['cancel']]);

        $this->salesOrderService = $salesOrderService;
    }

    /**
     * Display a listing of sales orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $customers = Customer::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.sales-orders.index', compact('customers', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying sales orders: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing sales orders.');
        }
    }

    /**
     * Get sales orders data for DataTables.
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
                'status' => $request->status != 'all' ? $request->status : null,
                'reference_no' => $request->reference_no,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Get orders from service
            $salesOrders = $this->salesOrderService->getOrders($filters);
            
            return DataTables::of($salesOrders)
                ->editColumn('order_date', function (SalesOrder $order) {
                    return $order->order_date->format('Y-m-d');
                })
                ->editColumn('expected_delivery_date', function (SalesOrder $order) {
                    return $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : '-';
                })
                ->editColumn('total_amount', function (SalesOrder $order) {
                    return number_format($order->total_amount, 2) . ' MAD';
                })
                ->addColumn('customer_name', function (SalesOrder $order) {
                    return $order->customer->name;
                })
                ->addColumn('warehouse_name', function (SalesOrder $order) {
                    return $order->warehouse->name;
                })
                ->addColumn('items_count', function (SalesOrder $order) {
                    return $order->items->count();
                })
                ->addColumn('status_label', function (SalesOrder $order) {
                    $statusMap = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'confirmed' => '<span class="badge bg-primary">Confirmed</span>',
                        'partially_delivered' => '<span class="badge bg-info">Partially Delivered</span>',
                        'delivered' => '<span class="badge bg-success">Delivered</span>',
                        'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
                    ];
                    return $statusMap[$order->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (SalesOrder $order) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_sales_orders')) {
                        $actions .= '<a href="' . route('admin.sales-orders.show', $order->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_sales_orders') && $order->canBeEdited()) {
                        $actions .= '<a href="' . route('admin.sales-orders.edit', $order->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Confirm button
                    if (Auth::guard('admin')->user()->can('confirm_sales_orders') && $order->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-confirm me-1" data-id="' . $order->id . '">
                            <i class="fas fa-check"></i>
                        </button> ';
                    }
                    
                    // Cancel button
                    if (Auth::guard('admin')->user()->can('cancel_sales_orders') && $order->canBeCancelled()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-warning btn-cancel me-1" data-id="' . $order->id . '">
                            <i class="fas fa-times"></i>
                        </button> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_sales_orders') && $order->canBeEdited()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $order->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting sales orders data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve sales orders data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new sales order.
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
            $reference = SalesOrder::generateReferenceNumber();
            
            return view('admin.sales-orders.create', compact('customers', 'warehouses', 'products', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying sales order create form: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.index')->with('error', 'An error occurred while accessing the create form.');
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
            
            $stock = ProductWarehouseStock::where('product_id', $request->product_id)
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
     * Store a newly created sales order.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:sales_orders',
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Validate stock availability if requested
            if ($request->check_stock) {
                $warehouseId = $request->warehouse_id;
                
                foreach ($request->items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    
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
            
            // Prepare data
            $data = $request->except('items', 'check_stock');
            $data['items'] = [];
            
            // Format items
            foreach ($request->items as $item) {
                $data['items'][] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            
            // Create the sales order
            $salesOrder = $this->salesOrderService->createOrder($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($salesOrder)
                    ->withProperties([
                        'sales_order_id' => $salesOrder->id,
                        'reference_no' => $salesOrder->reference_no,
                        'customer_id' => $salesOrder->customer_id
                    ])
                    ->log('Created sales order');
            }
            
            return redirect()->route('admin.sales-orders.show', $salesOrder->id)
                ->with('success', 'Sales order created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating sales order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create sales order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified sales order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $salesOrder = SalesOrder::with(['items.product', 'customer', 'warehouse', 'deliveries'])->findOrFail($id);
            
            return view('admin.sales-orders.show', compact('salesOrder'));
        } catch (Exception $e) {
            Log::error('Error displaying sales order: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.index')->with('error', 'An error occurred while accessing the sales order.');
        }
    }

    /**
     * Show the form for editing the specified sales order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $salesOrder = SalesOrder::with(['items.product'])->findOrFail($id);
            
            // Check if order can be edited
            if (!$salesOrder->canBeEdited()) {
                return redirect()->route('admin.sales-orders.show', $id)
                    ->with('error', 'This sales order cannot be edited anymore.');
            }
            
            $customers = Customer::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            return view('admin.sales-orders.edit', compact('salesOrder', 'customers', 'warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying sales order edit form: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified sales order.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $salesOrder = SalesOrder::findOrFail($id);
            
            // Check if order can be edited
            if (!$salesOrder->canBeEdited()) {
                return redirect()->route('admin.sales-orders.show', $id)
                    ->with('error', 'This sales order cannot be edited anymore.');
            }
            
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Validate stock availability if requested
            if ($request->check_stock) {
                $warehouseId = $request->warehouse_id;
                
                foreach ($request->items as $item) {
                    $productId = $item['product_id'];
                    $quantity = $item['quantity'];
                    
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
            
            // Prepare data
            $data = $request->except(['_method', '_token', 'items', 'check_stock']);
            $data['items'] = [];
            
            // Format items
            foreach ($request->items as $item) {
                $data['items'][] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            
            // Update the sales order
            $updatedOrder = $this->salesOrderService->updateOrder($salesOrder, $data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($updatedOrder)
                    ->withProperties([
                        'sales_order_id' => $updatedOrder->id,
                        'reference_no' => $updatedOrder->reference_no,
                        'customer_id' => $updatedOrder->customer_id
                    ])
                    ->log('Updated sales order');
            }
            
            return redirect()->route('admin.sales-orders.show', $updatedOrder->id)
                ->with('success', 'Sales order updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating sales order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update sales order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Confirm the sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            $salesOrder = SalesOrder::findOrFail($id);
            
            // Check if order can be confirmed
            if ($salesOrder->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft orders can be confirmed.'
                ], 422);
            }
            
            // Check stock availability
            $insufficientStock = $salesOrder->checkStock();
            if (!empty($insufficientStock)) {
                $errorMsg = 'Insufficient stock for the following products: ';
                foreach ($insufficientStock as $item) {
                    $errorMsg .= $item['product'] . ' (Required: ' . $item['required'] . ', Available: ' . $item['available'] . '), ';
                }
                $errorMsg = rtrim($errorMsg, ', ');
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMsg
                ], 422);
            }
            
            // Confirm order
            $confirmedOrder = $this->salesOrderService->confirmOrder($salesOrder);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($confirmedOrder)
                    ->withProperties([
                        'sales_order_id' => $confirmedOrder->id,
                        'reference_no' => $confirmedOrder->reference_no
                    ])
                    ->log('Confirmed sales order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sales order confirmed successfully.',
                'order' => $confirmedOrder
            ]);
        } catch (Exception $e) {
            Log::error('Error confirming sales order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            $salesOrder = SalesOrder::findOrFail($id);
            
            // Check if order can be cancelled
            if (!$salesOrder->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This sales order cannot be cancelled.'
                ], 422);
            }
            
            // Cancel order
            $cancelledOrder = $this->salesOrderService->cancelOrder($salesOrder);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($cancelledOrder)
                    ->withProperties([
                        'sales_order_id' => $cancelledOrder->id,
                        'reference_no' => $cancelledOrder->reference_no
                    ])
                    ->log('Cancelled sales order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sales order cancelled successfully.',
                'order' => $cancelledOrder
            ]);
        } catch (Exception $e) {
            Log::error('Error cancelling sales order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $salesOrder = SalesOrder::findOrFail($id);
            
            // Check if order can be deleted
            if (!$salesOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft sales orders can be deleted.'
                ], 422);
            }
            
            // Store order data for logging
            $orderData = [
                'id' => $salesOrder->id,
                'reference_no' => $salesOrder->reference_no,
                'customer_id' => $salesOrder->customer_id
            ];
            
            // Delete the order (will cascade to items)
            $salesOrder->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($orderData)
                    ->log('Deleted sales order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sales order deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting sales order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete sales order: ' . $e->getMessage()
            ], 500);
        }
    }
}
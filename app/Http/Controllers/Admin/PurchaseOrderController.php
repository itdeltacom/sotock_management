<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Purchase\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    /**
     * Create a new controller instance.
     *
     * @param PurchaseOrderService $purchaseOrderService
     */
    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_purchase_orders', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_purchase_orders', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_purchase_orders', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_purchase_orders', ['only' => ['destroy']]);
        $this->middleware('permission:confirm_purchase_orders', ['only' => ['confirm']]);
        $this->middleware('permission:cancel_purchase_orders', ['only' => ['cancel']]);

        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display a listing of purchase orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $suppliers = Supplier::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.purchase-orders.index', compact('suppliers', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase orders: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing purchase orders.');
        }
    }

    /**
     * Get purchase orders data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            // Apply filters
            $filters = [
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'status' => $request->status != 'all' ? $request->status : null,
                'reference_no' => $request->reference_no,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Get orders from service
            $purchaseOrders = $this->purchaseOrderService->getOrders($filters);
            
            return DataTables::of($purchaseOrders)
                ->editColumn('order_date', function (PurchaseOrder $order) {
                    return $order->order_date->format('Y-m-d');
                })
                ->editColumn('expected_delivery_date', function (PurchaseOrder $order) {
                    return $order->expected_delivery_date ? $order->expected_delivery_date->format('Y-m-d') : '-';
                })
                ->editColumn('total_amount', function (PurchaseOrder $order) {
                    return number_format($order->total_amount, 2) . ' MAD';
                })
                ->addColumn('supplier_name', function (PurchaseOrder $order) {
                    return $order->supplier->name;
                })
                ->addColumn('warehouse_name', function (PurchaseOrder $order) {
                    return $order->warehouse->name;
                })
                ->addColumn('items_count', function (PurchaseOrder $order) {
                    return $order->items->count();
                })
                ->addColumn('status_label', function (PurchaseOrder $order) {
                    $statusMap = [
                        'draft' => '<span class="badge bg-secondary">Draft</span>',
                        'confirmed' => '<span class="badge bg-primary">Confirmed</span>',
                        'partially_received' => '<span class="badge bg-info">Partially Received</span>',
                        'received' => '<span class="badge bg-success">Received</span>',
                        'cancelled' => '<span class="badge bg-danger">Cancelled</span>'
                    ];
                    return $statusMap[$order->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (PurchaseOrder $order) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_purchase_orders')) {
                        $actions .= '<a href="' . route('admin.purchase-orders.show', $order->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_purchase_orders') && $order->canBeEdited()) {
                        $actions .= '<a href="' . route('admin.purchase-orders.edit', $order->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Confirm button
                    if (Auth::guard('admin')->user()->can('confirm_purchase_orders') && $order->status == 'draft') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-confirm me-1" data-id="' . $order->id . '">
                            <i class="fas fa-check"></i>
                        </button> ';
                    }
                    
                    // Cancel button
                    if (Auth::guard('admin')->user()->can('cancel_purchase_orders') && $order->canBeCancelled()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-warning btn-cancel me-1" data-id="' . $order->id . '">
                            <i class="fas fa-times"></i>
                        </button> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_purchase_orders') && $order->canBeEdited()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $order->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting purchase orders data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve purchase orders data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new purchase order.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $suppliers = Supplier::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            // Generate reference number
            $reference = PurchaseOrder::generateReferenceNumber();
            
            return view('admin.purchase-orders.create', compact('suppliers', 'warehouses', 'products', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase order create form: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Store a newly created purchase order.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:purchase_orders',
                'supplier_id' => 'required|exists:suppliers,id',
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
            
            // Prepare data
            $data = $request->except('items');
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
            
            // Create the purchase order
            $purchaseOrder = $this->purchaseOrderService->createOrder($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($purchaseOrder)
                    ->withProperties([
                        'purchase_order_id' => $purchaseOrder->id,
                        'reference_no' => $purchaseOrder->reference_no,
                        'supplier_id' => $purchaseOrder->supplier_id
                    ])
                    ->log('Created purchase order');
            }
            
            return redirect()->route('admin.purchase-orders.show', $purchaseOrder->id)
                ->with('success', 'Purchase order created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating purchase order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create purchase order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier', 'warehouse', 'receptions'])->findOrFail($id);
            
            return view('admin.purchase-orders.show', compact('purchaseOrder'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase order: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')->with('error', 'An error occurred while accessing the purchase order.');
        }
    }

    /**
     * Show the form for editing the specified purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['items.product'])->findOrFail($id);
            
            // Check if order can be edited
            if (!$purchaseOrder->canBeEdited()) {
                return redirect()->route('admin.purchase-orders.show', $id)
                    ->with('error', 'This purchase order cannot be edited anymore.');
            }
            
            $suppliers = Supplier::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $products = Product::where('active', true)->orderBy('name')->get();
            
            return view('admin.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase order edit form: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified purchase order.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            // Check if order can be edited
            if (!$purchaseOrder->canBeEdited()) {
                return redirect()->route('admin.purchase-orders.show', $id)
                    ->with('error', 'This purchase order cannot be edited anymore.');
            }
            
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
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
            
            // Prepare data
            $data = $request->except(['_method', '_token', 'items']);
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
            
            // Update the purchase order
            $updatedOrder = $this->purchaseOrderService->updateOrder($purchaseOrder, $data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($updatedOrder)
                    ->withProperties([
                        'purchase_order_id' => $updatedOrder->id,
                        'reference_no' => $updatedOrder->reference_no,
                        'supplier_id' => $updatedOrder->supplier_id
                    ])
                    ->log('Updated purchase order');
            }
            
            return redirect()->route('admin.purchase-orders.show', $updatedOrder->id)
                ->with('success', 'Purchase order updated successfully.');
        } catch (Exception $e) {
            Log::error('Error updating purchase order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update purchase order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Confirm the purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            // Check if order can be confirmed
            if ($purchaseOrder->status != 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft orders can be confirmed.'
                ], 422);
            }
            
            // Confirm order
            $confirmedOrder = $this->purchaseOrderService->confirmOrder($purchaseOrder);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($confirmedOrder)
                    ->withProperties([
                        'purchase_order_id' => $confirmedOrder->id,
                        'reference_no' => $confirmedOrder->reference_no
                    ])
                    ->log('Confirmed purchase order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order confirmed successfully.',
                'order' => $confirmedOrder
            ]);
        } catch (Exception $e) {
            Log::error('Error confirming purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to confirm purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel the purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            // Check if order can be cancelled
            if (!$purchaseOrder->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This purchase order cannot be cancelled.'
                ], 422);
            }
            
            // Cancel order
            $cancelledOrder = $this->purchaseOrderService->cancelOrder($purchaseOrder);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($cancelledOrder)
                    ->withProperties([
                        'purchase_order_id' => $cancelledOrder->id,
                        'reference_no' => $cancelledOrder->reference_no
                    ])
                    ->log('Cancelled purchase order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order cancelled successfully.',
                'order' => $cancelledOrder
            ]);
        } catch (Exception $e) {
            Log::error('Error cancelling purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($id);
            
            // Check if order can be deleted
            if (!$purchaseOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft purchase orders can be deleted.'
                ], 422);
            }
            
            // Store order data for logging
            $orderData = [
                'id' => $purchaseOrder->id,
                'reference_no' => $purchaseOrder->reference_no,
                'supplier_id' => $purchaseOrder->supplier_id
            ];
            
            // Delete the order (will cascade to items)
            $purchaseOrder->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($orderData)
                    ->log('Deleted purchase order');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Purchase order deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete purchase order: ' . $e->getMessage()
            ], 500);
        }
    }
}
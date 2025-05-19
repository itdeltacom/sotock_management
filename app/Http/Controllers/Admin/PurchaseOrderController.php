<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\Purchase\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display a listing of the purchase orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view purchase-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $suppliers = Supplier::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $statuses = [
                PurchaseOrder::STATUS_DRAFT => 'Draft',
                PurchaseOrder::STATUS_CONFIRMED => 'Confirmed',
                PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
                PurchaseOrder::STATUS_RECEIVED => 'Received',
                PurchaseOrder::STATUS_CANCELLED => 'Cancelled'
            ];

            return view('admin.purchase-orders.index', compact('suppliers', 'warehouses', 'statuses'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase orders: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing purchase orders.');
        }
    }

    /**
     * Get purchase orders data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view purchase orders.'
                ], 403);
            }

            $filters = [
                'reference_no' => $request->input('reference_no'),
                'supplier_id' => $request->input('supplier_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'order_by' => $request->input('order_by', 'created_at'),
                'order_dir' => $request->input('order_dir', 'desc'),
                'per_page' => $request->input('length', 15)
            ];

            $purchaseOrders = $this->purchaseOrderService->getOrders($filters);

            return DataTables::of($purchaseOrders)
                ->addColumn('supplier_name', function (PurchaseOrder $po) {
                    return $po->supplier ? $po->supplier->name : '-';
                })
                ->addColumn('warehouse_name', function (PurchaseOrder $po) {
                    return $po->warehouse ? $po->warehouse->name : '-';
                })
                ->addColumn('created_by_name', function (PurchaseOrder $po) {
                    return $po->createdBy ? $po->createdBy->name : '-';
                })
                ->addColumn('status_label', function (PurchaseOrder $po) {
                    $statusClasses = [
                        PurchaseOrder::STATUS_DRAFT => 'bg-secondary',
                        PurchaseOrder::STATUS_CONFIRMED => 'bg-info',
                        PurchaseOrder::STATUS_PARTIALLY_RECEIVED => 'bg-warning',
                        PurchaseOrder::STATUS_RECEIVED => 'bg-success',
                        PurchaseOrder::STATUS_CANCELLED => 'bg-danger'
                    ];

                    $class = $statusClasses[$po->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst($po->status) . '</span>';
                })
                ->addColumn('action', function (PurchaseOrder $po) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view purchase-orders')) {
                        $actions .= '<a href="' . route('admin.purchase-orders.show', $po->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                    }

                    if (Auth::guard('admin')->user()->can('edit purchase-orders')) {
                        if ($po->canBeEdited()) {
                            $actions .= '<a href="' . route('admin.purchase-orders.edit', $po->id) . '" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>';
                        }

                        if ($po->status === PurchaseOrder::STATUS_DRAFT) {
                            $actions .= '<button type="button" class="btn btn-sm btn-success me-1 btn-confirm" data-id="' . $po->id . '">
                                <i class="fas fa-check"></i>
                            </button>';
                        }

                        if ($po->canBeCancelled()) {
                            $actions .= '<button type="button" class="btn btn-sm btn-warning me-1 btn-cancel" data-id="' . $po->id . '">
                                <i class="fas fa-ban"></i>
                            </button>';
                        }
                    }

                    if (Auth::guard('admin')->user()->can('delete purchase-orders')) {
                        if ($po->status === PurchaseOrder::STATUS_DRAFT) {
                            $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $po->id . '">
                                <i class="fas fa-trash"></i>
                            </button>';
                        }
                    }

                    if (Auth::guard('admin')->user()->can('manage stock-receptions') && $po->canBeReceived()) {
                        $actions .= '<a href="' . route('admin.purchase-orders.receptions.create', $po->id) . '" class="btn btn-sm btn-secondary ms-1">
                            <i class="fas fa-truck-loading"></i>
                        </a>';
                    }

                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting purchase orders data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve purchase orders data: ' . $e->getMessage()
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
            if (!auth()->guard('admin')->user()->can('create purchase-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $suppliers = Supplier::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $products = Product::where('active', true)->get();

            $refNo = PurchaseOrder::generateReferenceNumber();

            return view('admin.purchase-orders.create', compact('suppliers', 'warehouses', 'products', 'refNo'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase order creation form: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')->with('error', 'An error occurred while loading the purchase order form.');
        }
    }

    /**
     * Store a newly created purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create purchase orders.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $purchaseOrder = $this->purchaseOrderService->createOrder($request->all());

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($purchaseOrder)
                    ->withProperties(['purchase_order_id' => $purchaseOrder->id, 'reference_no' => $purchaseOrder->reference_no])
                    ->log('Created purchase order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully.',
                'purchase_order' => $purchaseOrder,
                'redirect_url' => route('admin.purchase-orders.show', $purchaseOrder->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error creating purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
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
            if (!auth()->guard('admin')->user()->can('view purchase-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'createdBy', 'items.product', 'receptions.items'])
                ->findOrFail($id);

            $totalReceivedItems = $purchaseOrder->getTotalReceivedItems();

            return view('admin.purchase-orders.show', compact('purchaseOrder', 'totalReceivedItems'));
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
            if (!auth()->guard('admin')->user()->can('edit purchase-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'items.product'])
                ->findOrFail($id);

            if (!$purchaseOrder->canBeEdited()) {
                return redirect()->route('admin.purchase-orders.show', $id)
                    ->with('error', 'This purchase order cannot be edited anymore.');
            }

            $suppliers = Supplier::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $products = Product::where('active', true)->get();

            return view('admin.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying purchase order edit form: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.index')->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit purchase orders.'
                ], 403);
            }

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This purchase order cannot be edited anymore.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'supplier_id' => 'required|exists:suppliers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'order_date' => 'required|date',
                'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updatedPurchaseOrder = $this->purchaseOrderService->updateOrder($purchaseOrder, $request->all());

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($purchaseOrder)
                    ->withProperties(['purchase_order_id' => $purchaseOrder->id, 'reference_no' => $purchaseOrder->reference_no])
                    ->log('Updated purchase order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully.',
                'purchase_order' => $updatedPurchaseOrder,
                'redirect_url' => route('admin.purchase-orders.show', $updatedPurchaseOrder->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error updating purchase order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm the specified purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to confirm purchase orders.'
                ], 403);
            }

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This purchase order cannot be confirmed.'
                ], 422);
            }

            $confirmedPurchaseOrder = $this->purchaseOrderService->confirmOrder($purchaseOrder);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($purchaseOrder)
                    ->withProperties(['purchase_order_id' => $purchaseOrder->id, 'reference_no' => $purchaseOrder->reference_no])
                    ->log('Confirmed purchase order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order confirmed successfully.',
                'purchase_order' => $confirmedPurchaseOrder
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
     * Cancel the specified purchase order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to cancel purchase orders.'
                ], 403);
            }

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if (!$purchaseOrder->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This purchase order cannot be cancelled.'
                ], 422);
            }

            $cancelledPurchaseOrder = $this->purchaseOrderService->cancelOrder($purchaseOrder);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($purchaseOrder)
                    ->withProperties(['purchase_order_id' => $purchaseOrder->id, 'reference_no' => $purchaseOrder->reference_no])
                    ->log('Cancelled purchase order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase order cancelled successfully.',
                'purchase_order' => $cancelledPurchaseOrder
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
     * Remove the specified purchase order from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete purchase-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete purchase orders.'
                ], 403);
            }

            $purchaseOrder = PurchaseOrder::findOrFail($id);

            if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft purchase orders can be deleted.'
                ], 422);
            }

            // Store reference for logging
            $reference = $purchaseOrder->reference_no;
            $purchaseOrderId = $purchaseOrder->id;

            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties(['purchase_order_id' => $purchaseOrderId, 'reference_no' => $reference])
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
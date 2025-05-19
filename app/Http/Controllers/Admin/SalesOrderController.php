<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Services\Sales\SalesOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class SalesOrderController extends Controller
{
    protected $salesOrderService;

    public function __construct(SalesOrderService $salesOrderService)
    {
        $this->salesOrderService = $salesOrderService;
    }

    /**
     * Display a listing of the sales orders.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view sales-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $customers = Customer::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $statuses = [
                SalesOrder::STATUS_DRAFT => 'Draft',
                SalesOrder::STATUS_CONFIRMED => 'Confirmed',
                SalesOrder::STATUS_PARTIALLY_DELIVERED => 'Partially Delivered',
                SalesOrder::STATUS_DELIVERED => 'Delivered',
                SalesOrder::STATUS_CANCELLED => 'Cancelled'
            ];

            return view('admin.sales-orders.index', compact('customers', 'warehouses', 'statuses'));
        } catch (Exception $e) {
            Log::error('Error displaying sales orders: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing sales orders.');
        }
    }

    /**
     * Get sales orders data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view sales orders.'
                ], 403);
            }

            $filters = [
                'reference_no' => $request->input('reference_no'),
                'customer_id' => $request->input('customer_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'order_by' => $request->input('order_by', 'created_at'),
                'order_dir' => $request->input('order_dir', 'desc'),
                'per_page' => $request->input('length', 15)
            ];

            $salesOrders = $this->salesOrderService->getOrders($filters);

            return DataTables::of($salesOrders)
                ->addColumn('customer_name', function (SalesOrder $so) {
                    return $so->customer ? $so->customer->name : '-';
                })
                ->addColumn('warehouse_name', function (SalesOrder $so) {
                    return $so->warehouse ? $so->warehouse->name : '-';
                })
                ->addColumn('created_by_name', function (SalesOrder $so) {
                    return $so->createdBy ? $so->createdBy->name : '-';
                })
                ->addColumn('status_label', function (SalesOrder $so) {
                    $statusClasses = [
                        SalesOrder::STATUS_DRAFT => 'bg-secondary',
                        SalesOrder::STATUS_CONFIRMED => 'bg-info',
                        SalesOrder::STATUS_PARTIALLY_DELIVERED => 'bg-warning',
                        SalesOrder::STATUS_DELIVERED => 'bg-success',
                        SalesOrder::STATUS_CANCELLED => 'bg-danger'
                    ];

                    $class = $statusClasses[$so->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst($so->status) . '</span>';
                })
                ->addColumn('action', function (SalesOrder $so) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view sales-orders')) {
                        $actions .= '<a href="' . route('admin.sales-orders.show', $so->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                    }

                    if (Auth::guard('admin')->user()->can('edit sales-orders')) {
                        if ($so->canBeEdited()) {
                            $actions .= '<a href="' . route('admin.sales-orders.edit', $so->id) . '" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>';
                        }

                        if ($so->status === SalesOrder::STATUS_DRAFT) {
                            $actions .= '<button type="button" class="btn btn-sm btn-success me-1 btn-confirm" data-id="' . $so->id . '">
                                <i class="fas fa-check"></i>
                            </button>';
                        }

                        if ($so->canBeCancelled()) {
                            $actions .= '<button type="button" class="btn btn-sm btn-warning me-1 btn-cancel" data-id="' . $so->id . '">
                                <i class="fas fa-ban"></i>
                            </button>';
                        }
                    }

                    if (Auth::guard('admin')->user()->can('delete sales-orders')) {
                        if ($so->status === SalesOrder::STATUS_DRAFT) {
                            $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $so->id . '">
                                <i class="fas fa-trash"></i>
                            </button>';
                        }
                    }

                    if (Auth::guard('admin')->user()->can('manage stock-deliveries') && $so->canBeDelivered()) {
                        $actions .= '<a href="' . route('admin.sales-orders.deliveries.create', $so->id) . '" class="btn btn-sm btn-secondary ms-1">
                            <i class="fas fa-truck"></i>
                        </a>';
                    }

                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting sales orders data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve sales orders data: ' . $e->getMessage()
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
            if (!auth()->guard('admin')->user()->can('create sales-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $customers = Customer::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $products = Product::with('stock')->where('active', true)->get();

            $refNo = SalesOrder::generateReferenceNumber();

            return view('admin.sales-orders.create', compact('customers', 'warehouses', 'products', 'refNo'));
        } catch (Exception $e) {
            Log::error('Error displaying sales order creation form: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.index')->with('error', 'An error occurred while loading the sales order form.');
        }
    }

    /**
     * Store a newly created sales order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create sales orders.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
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

            $salesOrder = $this->salesOrderService->createOrder($request->all());

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($salesOrder)
                    ->withProperties(['sales_order_id' => $salesOrder->id, 'reference_no' => $salesOrder->reference_no])
                    ->log('Created sales order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales order created successfully.',
                'sales_order' => $salesOrder,
                'redirect_url' => route('admin.sales-orders.show', $salesOrder->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error creating sales order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create sales order: ' . $e->getMessage()
            ], 500);
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
            if (!auth()->guard('admin')->user()->can('view sales-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $salesOrder = SalesOrder::with(['customer', 'warehouse', 'createdBy', 'items.product', 'deliveries.items'])
                ->findOrFail($id);

            $totalDeliveredItems = $salesOrder->getTotalDeliveredItems();

            return view('admin.sales-orders.show', compact('salesOrder', 'totalDeliveredItems'));
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
            if (!auth()->guard('admin')->user()->can('edit sales-orders')) {
                abort(403, 'Unauthorized action.');
            }

            $salesOrder = SalesOrder::with(['customer', 'warehouse', 'items.product'])
                ->findOrFail($id);

            if (!$salesOrder->canBeEdited()) {
                return redirect()->route('admin.sales-orders.show', $id)
                    ->with('error', 'This sales order cannot be edited anymore.');
            }

            $customers = Customer::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $products = Product::with('stock')->where('active', true)->get();

            return view('admin.sales-orders.edit', compact('salesOrder', 'customers', 'warehouses', 'products'));
        } catch (Exception $e) {
            Log::error('Error displaying sales order edit form: ' . $e->getMessage());
            return redirect()->route('admin.sales-orders.index')->with('error', 'An error occurred while loading the edit form.');
        }
    }

    /**
     * Update the specified sales order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit sales orders.'
                ], 403);
            }

            $salesOrder = SalesOrder::findOrFail($id);

            if (!$salesOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This sales order cannot be edited anymore.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
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

            $updatedSalesOrder = $this->salesOrderService->updateOrder($salesOrder, $request->all());

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($salesOrder)
                    ->withProperties(['sales_order_id' => $salesOrder->id, 'reference_no' => $salesOrder->reference_no])
                    ->log('Updated sales order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales order updated successfully.',
                'sales_order' => $updatedSalesOrder,
                'redirect_url' => route('admin.sales-orders.show', $updatedSalesOrder->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error updating sales order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm the specified sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to confirm sales orders.'
                ], 403);
            }

            $salesOrder = SalesOrder::findOrFail($id);

            if (!$salesOrder->canBeEdited()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This sales order cannot be confirmed.'
                ], 422);
            }

            try {
                $confirmedSalesOrder = $this->salesOrderService->confirmOrder($salesOrder);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 422);
            }

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($salesOrder)
                    ->withProperties(['sales_order_id' => $salesOrder->id, 'reference_no' => $salesOrder->reference_no])
                    ->log('Confirmed sales order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales order confirmed successfully.',
                'sales_order' => $confirmedSalesOrder
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
     * Cancel the specified sales order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to cancel sales orders.'
                ], 403);
            }

            $salesOrder = SalesOrder::findOrFail($id);

            if (!$salesOrder->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This sales order cannot be cancelled.'
                ], 422);
            }

            $cancelledSalesOrder = $this->salesOrderService->cancelOrder($salesOrder);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($salesOrder)
                    ->withProperties(['sales_order_id' => $salesOrder->id, 'reference_no' => $salesOrder->reference_no])
                    ->log('Cancelled sales order');
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales order cancelled successfully.',
                'sales_order' => $cancelledSalesOrder
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
     * Remove the specified sales order from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete sales-orders')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete sales orders.'
                ], 403);
            }

            $salesOrder = SalesOrder::findOrFail($id);

            if ($salesOrder->status !== SalesOrder::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only draft sales orders can be deleted.'
                ], 422);
            }

            // Store reference for logging
            $reference = $salesOrder->reference_no;
            $salesOrderId = $salesOrder->id;

            $salesOrder->items()->delete();
            $salesOrder->delete();

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties(['sales_order_id' => $salesOrderId, 'reference_no' => $reference])
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
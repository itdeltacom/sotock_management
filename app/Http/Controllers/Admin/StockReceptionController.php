<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\StockReception;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\Purchase\StockReceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class StockReceptionController extends Controller
{
    protected $stockReceptionService;

    public function __construct(StockReceptionService $stockReceptionService)
    {
        $this->stockReceptionService = $stockReceptionService;
    }

    /**
     * Display a listing of the stock receptions.
     *
     * @param  int|null  $purchaseOrderId
     * @return \Illuminate\View\View
     */
    public function index($purchaseOrderId = null)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view stock-receptions')) {
                abort(403, 'Unauthorized action.');
            }

            $purchaseOrder = null;
            if ($purchaseOrderId) {
                $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse'])
                    ->findOrFail($purchaseOrderId);
            }

            $suppliers = Supplier::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $statuses = [
                StockReception::STATUS_PENDING => 'Pending',
                StockReception::STATUS_COMPLETED => 'Completed',
                StockReception::STATUS_PARTIAL => 'Partial'
            ];

            return view('admin.stock-receptions.index', compact('purchaseOrder', 'suppliers', 'warehouses', 'statuses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock receptions: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock receptions.');
        }
    }

    /**
     * Get stock receptions data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view stock-receptions')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view stock receptions.'
                ], 403);
            }

            $filters = [
                'reference_no' => $request->input('reference_no'),
                'purchase_order_id' => $request->input('purchase_order_id'),
                'supplier_id' => $request->input('supplier_id'),
                'warehouse_id' => $request->input('warehouse_id'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'order_by' => $request->input('order_by', 'created_at'),
                'order_dir' => $request->input('order_dir', 'desc'),
                'per_page' => $request->input('length', 15)
            ];

            $stockReceptions = $this->stockReceptionService->getReceptions($filters);

            return DataTables::of($stockReceptions)
                ->addColumn('supplier_name', function (StockReception $sr) {
                    return $sr->supplier ? $sr->supplier->name : '-';
                })
                ->addColumn('warehouse_name', function (StockReception $sr) {
                    return $sr->warehouse ? $sr->warehouse->name : '-';
                })
                ->addColumn('purchase_order_ref', function (StockReception $sr) {
                    if ($sr->purchaseOrder) {
                        return '<a href="' . route('admin.purchase-orders.show', $sr->purchase_order_id) . '">' . $sr->purchaseOrder->reference_no . '</a>';
                    }
                    return 'Direct Reception';
                })
                ->addColumn('received_by_name', function (StockReception $sr) {
                    return $sr->receivedBy ? $sr->receivedBy->name : '-';
                })
                ->addColumn('status_label', function (StockReception $sr) {
                    $statusClasses = [
                        StockReception::STATUS_PENDING => 'bg-warning',
                        StockReception::STATUS_PARTIAL => 'bg-info',
                        StockReception::STATUS_COMPLETED => 'bg-success'
                    ];

                    $class = $statusClasses[$sr->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst($sr->status) . '</span>';
                })
                ->addColumn('action', function (StockReception $sr) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view stock-receptions')) {
                        $actions .= '<a href="' . route('admin.stock-receptions.show', $sr->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                    }

                    if (Auth::guard('admin')->user()->can('process stock-receptions') && $sr->status === StockReception::STATUS_PENDING) {
                        $actions .= '<button type="button" class="btn btn-sm btn-success me-1 btn-process" data-id="' . $sr->id . '">
                            <i class="fas fa-check"></i>
                        </button>';
                    }

                    if (Auth::guard('admin')->user()->can('delete stock-receptions') && $sr->status === StockReception::STATUS_PENDING) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $sr->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $actions;
                })
                ->rawColumns(['purchase_order_ref', 'status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock receptions data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock receptions data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new stock reception from purchase order.
     *
     * @param  int  $purchaseOrderId
     * @return \Illuminate\View\View
     */
    public function create($purchaseOrderId)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create stock-receptions')) {
                abort(403, 'Unauthorized action.');
            }

            $purchaseOrder = PurchaseOrder::with(['supplier', 'warehouse', 'items.product', 'receptions.items'])
                ->findOrFail($purchaseOrderId);

            if (!$purchaseOrder->canBeReceived()) {
                return redirect()->route('admin.purchase-orders.show', $purchaseOrderId)
                    ->with('error', 'This purchase order cannot be received.');
            }

            $receivableItems = [];
            $totalReceivedItems = $purchaseOrder->getTotalReceivedItems();

            foreach ($purchaseOrder->items as $item) {
                $received = $totalReceivedItems[$item->product_id] ?? 0;
                $remaining = $item->quantity - $received;

                if ($remaining > 0) {
                    $receivableItems[] = [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_code' => $item->product->code,
                        'unit' => $item->product->unit,
                        'ordered_quantity' => $item->quantity,
                        'received_quantity' => $received,
                        'remaining_quantity' => $remaining,
                        'unit_price' => $item->unit_price
                    ];
                }
            }

            $refNo = StockReception::generateReferenceNumber();
            $warehouses = Warehouse::where('active', true)->get();

            return view('admin.stock-receptions.create', compact('purchaseOrder', 'receivableItems', 'refNo', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock reception creation form: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.show', $purchaseOrderId)
                ->with('error', 'An error occurred while loading the stock reception form.');
        }
    }

    /**
     * Store a newly created stock reception from purchase order in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $purchaseOrderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $purchaseOrderId)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create stock-receptions')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create stock receptions.'
                ], 403);
            }

            $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);

            if (!$purchaseOrder->canBeReceived()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This purchase order cannot be received.'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'reference_no' => 'nullable|string|max:50',
                'warehouse_id' => 'required|exists:warehouses,id',
                'reception_date' => 'required|date',
                'status' => 'required|in:' . implode(',', [StockReception::STATUS_PENDING, StockReception::STATUS_COMPLETED]),
                'items' => 'required|array|min:1',
                'items.*' => 'required|array',
                'items.*.id' => 'required|exists:purchase_order_items,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.lot_number' => 'nullable|string|max:50',
                'items.*.expiry_date' => 'nullable|date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            // Format items for processing by service
            $formattedItems = [];
            foreach ($data['items'] as $item) {
                $formattedItems[$item['id']] = [
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            $data['items'] = $formattedItems;

            $stockReception = $this->stockReceptionService->createReceptionFromPO($purchaseOrder, $data);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($stockReception)
                    ->withProperties([
                        'stock_reception_id' => $stockReception->id, 
                        'reference_no' => $stockReception->reference_no,
                        'purchase_order_id' => $purchaseOrder->id,
                        'purchase_order_reference' => $purchaseOrder->reference_no
                    ])
                    ->log('Created stock reception');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock reception created successfully.',
                'stock_reception' => $stockReception,
                'redirect_url' => route('admin.stock-receptions.show', $stockReception->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error creating stock reception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create stock reception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a direct stock reception (without purchase order).
     *
     * @return \Illuminate\View\View
     */
    public function createDirect()
    {
        try {
            if (!auth()->guard('admin')->user()->can('create stock-receptions')) {
                abort(403, 'Unauthorized action.');
            }

            $suppliers = Supplier::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();
            $products = Product::where('active', true)->get();

            $refNo = StockReception::generateReferenceNumber();

            return view('admin.stock-receptions.create-direct', compact('suppliers', 'warehouses', 'products', 'refNo'));
        } catch (Exception $e) {
            Log::error('Error displaying direct stock reception creation form: ' . $e->getMessage());
            return redirect()->route('admin.stock-receptions.index')
                ->with('error', 'An error occurred while loading the direct stock reception form.');
        }
    }

    /**
     * Store a newly created direct stock reception in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDirect(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create stock-receptions')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create stock receptions.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'reference_no' => 'nullable|string|max:50',
                'supplier_id' => 'required|exists:suppliers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'reception_date' => 'required|date',
                'status' => 'required|in:' . implode(',', [StockReception::STATUS_PENDING, StockReception::STATUS_COMPLETED]),
                'items' => 'required|array|min:1',
                'items.*' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.001',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.lot_number' => 'nullable|string|max:50',
                'items.*.expiry_date' => 'nullable|date',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $stockReception = $this->stockReceptionService->createDirectReception($request->all());

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($stockReception)
                    ->withProperties([
                        'stock_reception_id' => $stockReception->id, 
                        'reference_no' => $stockReception->reference_no,
                        'type' => 'direct'
                    ])
                    ->log('Created direct stock reception');
            }

            return response()->json([
                'success' => true,
                'message' => 'Direct stock reception created successfully.',
                'stock_reception' => $stockReception,
                'redirect_url' => route('admin.stock-receptions.show', $stockReception->id)
            ]);
        } catch (Exception $e) {
            Log::error('Error creating direct stock reception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create direct stock reception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified stock reception.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view stock-receptions')) {
                abort(403, 'Unauthorized action.');
            }

            $stockReception = StockReception::with(['supplier', 'warehouse', 'purchaseOrder', 'receivedBy', 'items.product', 'items.stockPackage'])
                ->findOrFail($id);

            return view('admin.stock-receptions.show', compact('stockReception'));
        } catch (Exception $e) {
            Log::error('Error displaying stock reception: ' . $e->getMessage());
            return redirect()->route('admin.stock-receptions.index')->with('error', 'An error occurred while accessing the stock reception.');
        }
    }

    /**
     * Process a pending stock reception.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function process($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('process stock-receptions')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to process stock receptions.'
                ], 403);
            }

            $stockReception = StockReception::findOrFail($id);

            if ($stockReception->status !== StockReception::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'error' => 'This stock reception has already been processed.'
                ], 422);
            }

            $processedReception = $this->stockReceptionService->processReception($stockReception);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($stockReception)
                    ->withProperties([
                        'stock_reception_id' => $stockReception->id, 
                        'reference_no' => $stockReception->reference_no
                    ])
                    ->log('Processed stock reception');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock reception processed successfully.',
                'stock_reception' => $processedReception
            ]);
        } catch (Exception $e) {
            Log::error('Error processing stock reception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process stock reception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified stock reception from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete stock-receptions')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete stock receptions.'
                ], 403);
            }

            $stockReception = StockReception::findOrFail($id);

            if ($stockReception->status !== StockReception::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending stock receptions can be deleted.'
                ], 422);
            }

            // Store reference for logging
            $reference = $stockReception->reference_no;
            $receptionId = $stockReception->id;
            $purchaseOrderId = $stockReception->purchase_order_id;

            $stockReception->items()->delete();
            $stockReception->delete();

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties([
                        'stock_reception_id' => $receptionId, 
                        'reference_no' => $reference,
                        'purchase_order_id' => $purchaseOrderId
                    ])
                    ->log('Deleted stock reception');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock reception deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting stock reception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete stock reception: ' . $e->getMessage()
            ], 500);
        }
    }
}
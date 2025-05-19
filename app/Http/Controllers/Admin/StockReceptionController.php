<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockReception;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\Purchase\StockReceptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class StockReceptionController extends Controller
{
    protected $stockReceptionService;

    /**
     * Create a new controller instance.
     *
     * @param StockReceptionService $stockReceptionService
     */
    public function __construct(StockReceptionService $stockReceptionService)
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_stock_receptions', ['only' => ['index', 'show', 'data']]);
        $this->middleware('permission:create_stock_receptions', ['only' => ['create', 'createFromPO', 'store', 'storeFromPO']]);
        $this->middleware('permission:process_stock_receptions', ['only' => ['process']]);
        $this->middleware('permission:delete_stock_receptions', ['only' => ['destroy']]);

        $this->stockReceptionService = $stockReceptionService;
    }

    /**
     * Display a listing of stock receptions.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $suppliers = Supplier::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-receptions.index', compact('suppliers', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock receptions: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock receptions.');
        }
    }

    /**
     * Get stock receptions data for DataTables.
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
                'purchase_order_id' => $request->purchase_order_id,
                'status' => $request->status != 'all' ? $request->status : null,
                'reference_no' => $request->reference_no,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to
            ];
            
            // Get receptions from service
            $receptions = $this->stockReceptionService->getReceptions($filters);
            
            return DataTables::of($receptions)
                ->editColumn('reception_date', function (StockReception $reception) {
                    return $reception->reception_date->format('Y-m-d');
                })
                ->addColumn('supplier_name', function (StockReception $reception) {
                    return $reception->supplier->name;
                })
                ->addColumn('warehouse_name', function (StockReception $reception) {
                    return $reception->warehouse->name;
                })
                ->addColumn('purchase_order_ref', function (StockReception $reception) {
                    return $reception->purchaseOrder ? $reception->purchaseOrder->reference_no : 'Direct Reception';
                })
                ->addColumn('items_count', function (StockReception $reception) {
                    return $reception->items->count();
                })
                ->addColumn('status_label', function (StockReception $reception) {
                    $statusMap = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'completed' => '<span class="badge bg-success">Completed</span>',
                        'partial' => '<span class="badge bg-info">Partial</span>'
                    ];
                    return $statusMap[$reception->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('action', function (StockReception $reception) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_stock_receptions')) {
                        $actions .= '<a href="' . route('admin.stock-receptions.show', $reception->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Process button (for pending receptions)
                    if (Auth::guard('admin')->user()->can('process_stock_receptions') && $reception->status == 'pending') {
                        $actions .= '<button type="button" class="btn btn-sm btn-success btn-process me-1" data-id="' . $reception->id . '">
                            <i class="fas fa-check"></i> Process
                        </button> ';
                    }
                    
                    // Delete button (only for pending receptions)
                    if (Auth::guard('admin')->user()->can('delete_stock_receptions') && $reception->status == 'pending') {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $reception->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock receptions data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock receptions data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new direct stock reception.
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
            $reference = StockReception::generateReferenceNumber();
            
            return view('admin.stock-receptions.create', compact('suppliers', 'warehouses', 'products', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying stock reception create form: ' . $e->getMessage());
            return redirect()->route('admin.stock-receptions.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Show the form for creating a stock reception from purchase order.
     *
     * @param int $purchaseOrderId
     * @return \Illuminate\View\View
     */
    public function createFromPO($purchaseOrderId)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier', 'warehouse'])->findOrFail($purchaseOrderId);
            
            // Check if PO can be received
            if (!$purchaseOrder->canBeReceived()) {
                return redirect()->route('admin.purchase-orders.show', $purchaseOrderId)
                    ->with('error', 'This purchase order cannot be received.');
            }
            
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            // Generate reference number
            $reference = StockReception::generateReferenceNumber();
            
            return view('admin.stock-receptions.create-from-po', compact('purchaseOrder', 'warehouses', 'reference'));
        } catch (Exception $e) {
            Log::error('Error displaying stock reception create form from PO: ' . $e->getMessage());
            return redirect()->route('admin.purchase-orders.show', $purchaseOrderId)
                ->with('error', 'An error occurred while creating reception from purchase order.');
        }
    }

    /**
     * Store a newly created direct stock reception.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:stock_receptions',
                'supplier_id' => 'required|exists:suppliers,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'reception_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.lot_number' => 'nullable|string|max:100',
                'items.*.expiry_date' => 'nullable|date|after:reception_date',
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
                    'unit_cost' => $item['unit_cost'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => !empty($item['expiry_date']) ? $item['expiry_date'] : null,
                    'notes' => $item['notes'] ?? null,
                ];
            }
            
            // Create the reception
            $reception = $this->stockReceptionService->createDirectReception($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($reception)
                    ->withProperties([
                        'reception_id' => $reception->id,
                        'reference_no' => $reception->reference_no,
                        'supplier_id' => $reception->supplier_id
                    ])
                    ->log('Created direct stock reception');
            }
            
            return redirect()->route('admin.stock-receptions.show', $reception->id)
                ->with('success', 'Stock reception created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock reception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock reception: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Store a stock reception from purchase order.
     *
     * @param Request $request
     * @param int $purchaseOrderId
     * @return \Illuminate\Http\Response
     */
    public function storeFromPO(Request $request, $purchaseOrderId)
    {
        try {
            $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
            
            // Check if PO can be received
            if (!$purchaseOrder->canBeReceived()) {
                return redirect()->route('admin.purchase-orders.show', $purchaseOrderId)
                    ->with('error', 'This purchase order cannot be received.');
            }
            
            // Validate basic info
            $validator = Validator::make($request->all(), [
                'reference_no' => 'required|string|max:50|unique:stock_receptions',
                'warehouse_id' => 'required|exists:warehouses,id',
                'reception_date' => 'required|date',
                'notes' => 'nullable|string',
                'status' => 'required|in:pending,completed',
                'items' => 'required|array',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.lot_number' => 'nullable|string|max:100',
                'items.*.expiry_date' => 'nullable|date|after:reception_date',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Create the reception
            $reception = $this->stockReceptionService->createReceptionFromPO($purchaseOrder, $request->all());
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($reception)
                    ->withProperties([
                        'reception_id' => $reception->id,
                        'reference_no' => $reception->reference_no,
                        'purchase_order_id' => $reception->purchase_order_id
                    ])
                    ->log('Created stock reception from purchase order');
            }
            
            return redirect()->route('admin.stock-receptions.show', $reception->id)
                ->with('success', 'Stock reception created successfully.');
        } catch (Exception $e) {
            Log::error('Error creating stock reception from PO: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create stock reception: ' . $e->getMessage())->withInput();
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
            $reception = StockReception::with(['items.product', 'supplier', 'warehouse', 'purchaseOrder', 'receivedBy'])->findOrFail($id);
            
            return view('admin.stock-receptions.show', compact('reception'));
        } catch (Exception $e) {
            Log::error('Error displaying stock reception: ' . $e->getMessage());
            return redirect()->route('admin.stock-receptions.index')->with('error', 'An error occurred while accessing the stock reception.');
        }
    }

    /**
     * Process the pending stock reception.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function process($id)
    {
        try {
            $reception = StockReception::findOrFail($id);
            
            // Check if reception can be processed
            if ($reception->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending receptions can be processed.'
                ], 422);
            }
            
            // Process the reception
            $processedReception = $this->stockReceptionService->processReception($reception);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($processedReception)
                    ->withProperties([
                        'reception_id' => $processedReception->id,
                        'reference_no' => $processedReception->reference_no
                    ])
                    ->log('Processed stock reception');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock reception processed successfully.',
                'reception' => $processedReception
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
     * Remove the specified stock reception.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $reception = StockReception::findOrFail($id);
            
            // Check if reception can be deleted
            if ($reception->status != 'pending') {
                return response()->json([
                    'success' => false,
                    'error' => 'Only pending receptions can be deleted.'
                ], 422);
            }
            
            // Store reception data for logging
            $receptionData = [
                'id' => $reception->id,
                'reference_no' => $reception->reference_no,
                'supplier_id' => $reception->supplier_id,
                'purchase_order_id' => $reception->purchase_order_id
            ];
            
            // Delete the reception (will cascade to items)
            $reception->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($receptionData)
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
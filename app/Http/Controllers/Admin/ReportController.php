<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductWarehouseStock;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;
use Carbon\Carbon;
use PDF;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_reports');
        $this->middleware('permission:generate_reports');
    }

    /**
     * Display the report page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            $suppliers = Supplier::where('active', true)->orderBy('name')->get();
            $customers = Customer::where('active', true)->orderBy('name')->get();
            $categories = ProductCategory::orderBy('name')->get();
            
            return view('admin.reports.index', compact('warehouses', 'suppliers', 'customers', 'categories'));
        } catch (Exception $e) {
            Log::error('Error displaying reports page: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing reports.');
        }
    }

    /**
     * Generate inventory valuation report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function inventoryValuation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'category_id' => 'nullable|exists:product_categories,id',
                'include_zero_stock' => 'nullable|boolean',
                'date' => 'nullable|date',
                'format' => 'required|in:html,pdf,excel',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Build query
            $query = ProductWarehouseStock::with(['product', 'warehouse']);
            
            // Apply warehouse filter
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            // Apply category filter
            if ($request->has('category_id') && $request->category_id) {
                $category = ProductCategory::find($request->category_id);
                
                // Get all descendant category IDs
                $categoryIds = [$category->id];
                $descendants = $category->descendants();
                foreach ($descendants as $descendant) {
                    $categoryIds[] = $descendant->id;
                }
                
                $query->whereHas('product.categories', function($q) use ($categoryIds) {
                    $q->whereIn('product_categories.id', $categoryIds);
                });
            }
            
            // Apply zero stock filter
            if (!$request->has('include_zero_stock') || !$request->include_zero_stock) {
                $query->where('available_quantity', '>', 0);
            }
            
            // Get data
            $stockItems = $query->get();
            
            // Calculate totals
            $totalValue = 0;
            foreach ($stockItems as $item) {
                $totalValue += $item->available_quantity * $item->cmup;
            }
            
            // Generate report based on format
            $date = $request->date ? Carbon::parse($request->date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;
            $category = $request->category_id ? ProductCategory::find($request->category_id) : null;
            
            $reportData = [
                'stockItems' => $stockItems,
                'totalValue' => $totalValue,
                'date' => $date,
                'warehouse' => $warehouse,
                'category' => $category,
                'includeZeroStock' => $request->has('include_zero_stock') && $request->include_zero_stock,
            ];
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->log('Generated Inventory Valuation Report');
            }
            
            switch ($request->format) {
                case 'pdf':
                    $pdf = PDF::loadView('admin.reports.inventory_valuation_pdf', $reportData);
                    return $pdf->download('inventory_valuation_' . $date . '.pdf');
                    
                case 'excel':
                    return Excel::download(new InventoryValuationExport($reportData), 'inventory_valuation_' . $date . '.xlsx');
                    
                case 'html':
                default:
                    return view('admin.reports.inventory_valuation', $reportData);
            }
        } catch (Exception $e) {
            Log::error('Error generating inventory valuation report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate stock movement report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function stockMovement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'product_id' => 'nullable|exists:products,id',
                'movement_type' => 'nullable|in:in,out,all',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'format' => 'required|in:html,pdf,excel',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Build query
            $query = StockMovement::with(['product', 'warehouse', 'createdBy']);
            
            // Apply warehouse filter
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            // Apply product filter
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }
            
            // Apply movement type filter
            if ($request->has('movement_type') && $request->movement_type != 'all') {
                $query->where('movement_type', $request->movement_type);
            }
            
            // Apply date range
            $query->whereDate('created_at', '>=', $request->date_from)
                ->whereDate('created_at', '<=', $request->date_to);
            
            // Get data
            $movements = $query->orderBy('created_at', 'desc')->get();
            
            // Generate report based on format
            $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
            $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;
            $product = $request->product_id ? Product::find($request->product_id) : null;
            
            $reportData = [
                'movements' => $movements,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'warehouse' => $warehouse,
                'product' => $product,
                'movementType' => $request->movement_type ?? 'all',
            ];
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->log('Generated Stock Movement Report');
            }
            
            switch ($request->format) {
                case 'pdf':
                    $pdf = PDF::loadView('admin.reports.stock_movement_pdf', $reportData);
                    return $pdf->download('stock_movement_' . $dateFrom . '_' . $dateTo . '.pdf');
                    
                case 'excel':
                    return Excel::download(new StockMovementExport($reportData), 'stock_movement_' . $dateFrom . '_' . $dateTo . '.xlsx');
                    
                case 'html':
                default:
                    return view('admin.reports.stock_movement', $reportData);
            }
        } catch (Exception $e) {
            Log::error('Error generating stock movement report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate purchase report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function purchase(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'supplier_id' => 'nullable|exists:suppliers,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'status' => 'nullable|in:draft,confirmed,partially_received,received,cancelled,all',
                'group_by' => 'required|in:supplier,product,date,none',
                'format' => 'required|in:html,pdf,excel',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Build base query
            $query = PurchaseOrder::with(['supplier', 'warehouse', 'items.product']);
            
            // Apply supplier filter
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }
            
            // Apply warehouse filter
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            // Apply date range
            $query->whereDate('order_date', '>=', $request->date_from)
                ->whereDate('order_date', '<=', $request->date_to);
            
            // Apply status filter
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            // Get orders
            $purchaseOrders = $query->orderBy('order_date', 'desc')->get();
            
            // Group data if needed
            $groupedData = [];
            $totalAmount = 0;
            
            foreach ($purchaseOrders as $order) {
                $totalAmount += $order->total_amount;
                
                switch ($request->group_by) {
                    case 'supplier':
                        $key = $order->supplier_id;
                        $name = $order->supplier->name;
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'orders_count' => 0,
                                'total_amount' => 0
                            ];
                        }
                        
                        $groupedData[$key]['orders_count']++;
                        $groupedData[$key]['total_amount'] += $order->total_amount;
                        break;
                        
                    case 'product':
                        foreach ($order->items as $item) {
                            $key = $item->product_id;
                            $name = $item->product->name;
                            
                            if (!isset($groupedData[$key])) {
                                $groupedData[$key] = [
                                    'name' => $name,
                                    'quantity' => 0,
                                    'total_amount' => 0
                                ];
                            }
                            
                            $groupedData[$key]['quantity'] += $item->quantity;
                            $groupedData[$key]['total_amount'] += $item->subtotal;
                        }
                        break;
                        
                    case 'date':
                        $key = $order->order_date->format('Y-m-d');
                        $name = $order->order_date->format('Y-m-d');
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'orders_count' => 0,
                                'total_amount' => 0
                            ];
                        }
                        
                        $groupedData[$key]['orders_count']++;
                        $groupedData[$key]['total_amount'] += $order->total_amount;
                        break;
                        
                    case 'none':
                    default:
                        // No grouping needed
                        break;
                }
            }
            
            // Generate report based on format
            $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
            $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');
            $supplier = $request->supplier_id ? Supplier::find($request->supplier_id) : null;
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;
            
            $reportData = [
                'purchaseOrders' => $purchaseOrders,
                'groupedData' => $groupedData,
                'totalAmount' => $totalAmount,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'supplier' => $supplier,
                'warehouse' => $warehouse,
                'status' => $request->status ?? 'all',
                'groupBy' => $request->group_by,
            ];
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->log('Generated Purchase Report');
            }
            
            switch ($request->format) {
                case 'pdf':
                    $pdf = PDF::loadView('admin.reports.purchase_pdf', $reportData);
                    return $pdf->download('purchase_report_' . $dateFrom . '_' . $dateTo . '.pdf');
                    
                case 'excel':
                    return Excel::download(new PurchaseReportExport($reportData), 'purchase_report_' . $dateFrom . '_' . $dateTo . '.xlsx');
                    
                case 'html':
                default:
                    return view('admin.reports.purchase', $reportData);
            }
        } catch (Exception $e) {
            Log::error('Error generating purchase report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate sales report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'status' => 'nullable|in:draft,confirmed,partially_delivered,delivered,cancelled,all',
                'group_by' => 'required|in:customer,product,date,none',
                'format' => 'required|in:html,pdf,excel',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Build base query
            $query = SalesOrder::with(['customer', 'warehouse', 'items.product']);
            
            // Apply customer filter
            if ($request->has('customer_id') && $request->customer_id) {
                $query->where('customer_id', $request->customer_id);
            }
            
            // Apply warehouse filter
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            // Apply date range
            $query->whereDate('order_date', '>=', $request->date_from)
                ->whereDate('order_date', '<=', $request->date_to);
            
            // Apply status filter
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }
            
            // Get orders
            $salesOrders = $query->orderBy('order_date', 'desc')->get();
            
            // Group data if needed
            $groupedData = [];
            $totalAmount = 0;
            
            foreach ($salesOrders as $order) {
                $totalAmount += $order->total_amount;
                
                switch ($request->group_by) {
                    case 'customer':
                        $key = $order->customer_id;
                        $name = $order->customer->name;
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'orders_count' => 0,
                                'total_amount' => 0
                            ];
                        }
                        
                        $groupedData[$key]['orders_count']++;
                        $groupedData[$key]['total_amount'] += $order->total_amount;
                        break;
                        
                    case 'product':
                        foreach ($order->items as $item) {
                            $key = $item->product_id;
                            $name = $item->product->name;
                            
                            if (!isset($groupedData[$key])) {
                                $groupedData[$key] = [
                                    'name' => $name,
                                    'quantity' => 0,
                                    'total_amount' => 0
                                ];
                            }
                            
                            $groupedData[$key]['quantity'] += $item->quantity;
                            $groupedData[$key]['total_amount'] += $item->subtotal;
                        }
                        break;
                        
                    case 'date':
                        $key = $order->order_date->format('Y-m-d');
                        $name = $order->order_date->format('Y-m-d');
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'orders_count' => 0,
                                'total_amount' => 0
                            ];
                        }
                        
                        $groupedData[$key]['orders_count']++;
                        $groupedData[$key]['total_amount'] += $order->total_amount;
                        break;
                        
                    case 'none':
                    default:
                        // No grouping needed
                        break;
                }
            }
            
            // Generate report based on format
            $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
            $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');
            $customer = $request->customer_id ? Customer::find($request->customer_id) : null;
            $warehouse = $request->warehouse_id ? Warehouse::find($request->warehouse_id) : null;
            
            $reportData = [
                'salesOrders' => $salesOrders,
                'groupedData' => $groupedData,
                'totalAmount' => $totalAmount,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'customer' => $customer,
                'warehouse' => $warehouse,
                'status' => $request->status ?? 'all',
                'groupBy' => $request->group_by,
            ];
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->log('Generated Sales Report');
            }
            
            switch ($request->format) {
                case 'pdf':
                    $pdf = PDF::loadView('admin.reports.sales_pdf', $reportData);
                    return $pdf->download('sales_report_' . $dateFrom . '_' . $dateTo . '.pdf');
                    
                case 'excel':
                    return Excel::download(new SalesReportExport($reportData), 'sales_report_' . $dateFrom . '_' . $dateTo . '.xlsx');
                    
                case 'html':
                default:
                    return view('admin.reports.sales', $reportData);
            }
        } catch (Exception $e) {
            Log::error('Error generating sales report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Generate profit margin report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function profitMargin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date_from' => 'required|date',
                'date_to' => 'required|date|after_or_equal:date_from',
                'group_by' => 'required|in:product,category,customer,date,none',
                'format' => 'required|in:html,pdf,excel',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Calculate profit based on stock delivery items
            $query = DB::table('stock_delivery_items')
                ->join('stock_deliveries', 'stock_deliveries.id', '=', 'stock_delivery_items.stock_delivery_id')
                ->join('products', 'products.id', '=', 'stock_delivery_items.product_id')
                ->leftJoin('customers', 'customers.id', '=', 'stock_deliveries.customer_id')
                ->whereDate('stock_deliveries.delivery_date', '>=', $request->date_from)
                ->whereDate('stock_deliveries.delivery_date', '<=', $request->date_to)
                ->where('stock_deliveries.status', 'completed')
                ->select(
                    'stock_delivery_items.id',
                    'stock_delivery_items.product_id',
                    'products.name as product_name',
                    'products.code as product_code',
                    'stock_deliveries.customer_id',
                    'customers.name as customer_name',
                    'stock_deliveries.delivery_date',
                    'stock_delivery_items.delivered_quantity',
                    'stock_delivery_items.unit_cost',
                    'stock_delivery_items.unit_price',
                    DB::raw('stock_delivery_items.delivered_quantity * stock_delivery_items.unit_cost as cost_amount'),
                    DB::raw('stock_delivery_items.delivered_quantity * stock_delivery_items.unit_price as sales_amount'),
                    DB::raw('(stock_delivery_items.delivered_quantity * stock_delivery_items.unit_price) - (stock_delivery_items.delivered_quantity * stock_delivery_items.unit_cost) as profit_amount'),
                    DB::raw('CASE WHEN stock_delivery_items.unit_price > 0 THEN (((stock_delivery_items.unit_price - stock_delivery_items.unit_cost) / stock_delivery_items.unit_price) * 100) ELSE 0 END as profit_margin_percentage')
                );
            
            // Get results
            $deliveryItems = $query->get();
            
            // Group data if needed
            $groupedData = [];
            $totalCost = 0;
            $totalSales = 0;
            $totalProfit = 0;
            
            foreach ($deliveryItems as $item) {
                $totalCost += $item->cost_amount;
                $totalSales += $item->sales_amount;
                $totalProfit += $item->profit_amount;
                
                switch ($request->group_by) {
                    case 'product':
                        $key = $item->product_id;
                        $name = $item->product_name . ' (' . $item->product_code . ')';
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'quantity' => 0,
                                'cost_amount' => 0,
                                'sales_amount' => 0,
                                'profit_amount' => 0,
                                'profit_margin_percentage' => 0
                            ];
                        }
                        
                        $groupedData[$key]['quantity'] += $item->delivered_quantity;
                        $groupedData[$key]['cost_amount'] += $item->cost_amount;
                        $groupedData[$key]['sales_amount'] += $item->sales_amount;
                        $groupedData[$key]['profit_amount'] += $item->profit_amount;
                        break;
                        
                    case 'category':
                        // Get product categories
                        $product = Product::find($item->product_id);
                        $categories = $product->categories;
                        
                        if ($categories->isEmpty()) {
                            $key = 0;
                            $name = 'Uncategorized';
                            
                            if (!isset($groupedData[$key])) {
                                $groupedData[$key] = [
                                    'name' => $name,
                                    'quantity' => 0,
                                    'cost_amount' => 0,
                                    'sales_amount' => 0,
                                    'profit_amount' => 0,
                                    'profit_margin_percentage' => 0
                                ];
                            }
                            
                            $groupedData[$key]['quantity'] += $item->delivered_quantity;
                            $groupedData[$key]['cost_amount'] += $item->cost_amount;
                            $groupedData[$key]['sales_amount'] += $item->sales_amount;
                            $groupedData[$key]['profit_amount'] += $item->profit_amount;
                        } else {
                            foreach ($categories as $category) {
                                $key = $category->id;
                                $name = $category->name;
                                
                                if (!isset($groupedData[$key])) {
                                    $groupedData[$key] = [
                                        'name' => $name,
                                        'quantity' => 0,
                                        'cost_amount' => 0,
                                        'sales_amount' => 0,
                                        'profit_amount' => 0,
                                        'profit_margin_percentage' => 0
                                    ];
                                }
                                
                                $groupedData[$key]['quantity'] += $item->delivered_quantity;
                                $groupedData[$key]['cost_amount'] += $item->cost_amount;
                                $groupedData[$key]['sales_amount'] += $item->sales_amount;
                                $groupedData[$key]['profit_amount'] += $item->profit_amount;
                            }
                        }
                        break;
                        
                    case 'customer':
                        $key = $item->customer_id ?: 0;
                        $name = $item->customer_name ?: 'Direct Sale';
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'cost_amount' => 0,
                                'sales_amount' => 0,
                                'profit_amount' => 0,
                                'profit_margin_percentage' => 0
                            ];
                        }
                        
                        $groupedData[$key]['cost_amount'] += $item->cost_amount;
                        $groupedData[$key]['sales_amount'] += $item->sales_amount;
                        $groupedData[$key]['profit_amount'] += $item->profit_amount;
                        break;
                        
                    case 'date':
                        $date = Carbon::parse($item->delivery_date);
                        $key = $date->format('Y-m-d');
                        $name = $date->format('Y-m-d');
                        
                        if (!isset($groupedData[$key])) {
                            $groupedData[$key] = [
                                'name' => $name,
                                'cost_amount' => 0,
                                'sales_amount' => 0,
                                'profit_amount' => 0,
                                'profit_margin_percentage' => 0
                            ];
                        }
                        
                        $groupedData[$key]['cost_amount'] += $item->cost_amount;
                        $groupedData[$key]['sales_amount'] += $item->sales_amount;
                        $groupedData[$key]['profit_amount'] += $item->profit_amount;
                        break;
                        
                    case 'none':
                    default:
                        // No grouping needed
                        break;
                }
            }
            
            // Calculate profit margin percentages for grouped data
            foreach ($groupedData as &$group) {
                if ($group['sales_amount'] > 0) {
                    $group['profit_margin_percentage'] = ($group['profit_amount'] / $group['sales_amount']) * 100;
                } else {
                    $group['profit_margin_percentage'] = 0;
                }
            }
            
            // Sort grouped data by profit amount (descending)
            if (!empty($groupedData)) {
                uasort($groupedData, function ($a, $b) {
                    return $b['profit_amount'] <=> $a['profit_amount'];
                });
            }
            
            // Generate report based on format
            $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
            $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');
            
            $reportData = [
                'deliveryItems' => $deliveryItems,
                'groupedData' => $groupedData,
                'totalCost' => $totalCost,
                'totalSales' => $totalSales,
                'totalProfit' => $totalProfit,
                'totalProfitMargin' => $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'groupBy' => $request->group_by,
            ];
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->log('Generated Profit Margin Report');
            }
            
            switch ($request->format) {
                case 'pdf':
                    $pdf = PDF::loadView('admin.reports.profit_margin_pdf', $reportData);
                    return $pdf->download('profit_margin_report_' . $dateFrom . '_' . $dateTo . '.pdf');
                    
                case 'excel':
                    return Excel::download(new ProfitMarginReportExport($reportData), 'profit_margin_report_' . $dateFrom . '_' . $dateTo . '.xlsx');
                    
                case 'html':
                default:
                    return view('admin.reports.profit_margin', $reportData);
            }
        } catch (Exception $e) {
            Log::error('Error generating profit margin report: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate report: ' . $e->getMessage())->withInput();
        }
    }
}
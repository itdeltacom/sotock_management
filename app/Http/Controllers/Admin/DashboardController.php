<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\StockDelivery;
use App\Models\StockReception;
use App\Models\StockPackage;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard.
     */
    public function index()
    {
        // User must have permission to access dashboard
        if (!auth()->guard('admin')->user()->can('access dashboard')) {
            abort(403, 'Unauthorized action.');
        }
        
        // Date ranges
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $previousMonth = Carbon::now()->subMonth();
        $startOfPreviousMonth = $previousMonth->startOfMonth();
        $endOfPreviousMonth = $previousMonth->endOfMonth();

        // Initialize variables to prevent errors
        $recentMovements = collect([]);
        $stockStats = [
            'totalMovementsToday' => 0,
            'totalMovementsMonth' => 0,
            'incomingMonth' => 0,
            'outgoingMonth' => 0,
        ];
        $totalProducts = 0;
        $totalStockValue = 0;
        $lowStockProducts = 0;
        $outOfStockProducts = 0;
        $pendingPurchaseOrders = 0;
        $thisMonthPurchaseOrders = 0;
        $receivedPurchaseOrders = 0;
        $totalPurchaseOrders = 0;
        $totalPurchaseValue = 0;
        $thisMonthPurchaseValue = 0;
        $pendingSalesOrders = 0;
        $thisMonthSalesOrders = 0;
        $deliveredSalesOrders = 0;
        $totalSalesOrders = 0;
        $totalSalesValue = 0;
        $thisMonthSalesValue = 0;
        $recentPurchaseOrders = collect([]);
        $recentSalesOrders = collect([]);
        $movementTypes = collect([]);
        $topProducts = collect([]);
        $warehouseStats = collect([]);
        $lowStockAlerts = collect([]);
        $expiringProducts = collect([]);

        // Check if tables exist before querying
        if (Schema::hasTable('products')) {
            $totalProducts = Product::where('active', true)->count();
        }

        // For product_warehouse_stock table
        $productWarehouseStockExists = Schema::hasTable('product_warehouse_stock');
        
        if ($productWarehouseStockExists) {
            // Warehouse stock statistics
            $totalStockValue = DB::table('product_warehouse_stock')->sum(DB::raw('available_quantity * cmup'));
            $lowStockProducts = DB::table('product_warehouse_stock')
                ->whereRaw('available_quantity < min_stock AND min_stock > 0')
                ->count();
            $outOfStockProducts = DB::table('product_warehouse_stock')
                ->where('available_quantity', 0)
                ->count();
                
            // Get low stock alerts
            $lowStockAlerts = DB::table('product_warehouse_stock')
                ->whereRaw('available_quantity < min_stock AND min_stock > 0')
                ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
                ->join('warehouses', 'product_warehouse_stock.warehouse_id', '=', 'warehouses.id')
                ->select(
                    'product_warehouse_stock.id',
                    'products.name as product_name',
                    'products.code as product_code',
                    'warehouses.name as warehouse_name',
                    'product_warehouse_stock.available_quantity',
                    'product_warehouse_stock.min_stock'
                )
                ->orderByRaw('available_quantity / min_stock ASC')
                ->limit(5)
                ->get()
                ->map(function($item) {
                    return [
                        'product' => $item->product_name,
                        'warehouse' => $item->warehouse_name,
                        'available' => $item->available_quantity,
                        'min_stock' => $item->min_stock,
                        'percentage' => $item->min_stock > 0 ? round(($item->available_quantity / $item->min_stock) * 100, 1) : 0,
                    ];
                });
                
            // Get warehouse usage statistics
            if (Schema::hasTable('warehouses')) {
                $warehouseStats = DB::table('warehouses')
                    ->select(
                        'warehouses.id', 
                        'warehouses.name', 
                        DB::raw('COUNT(DISTINCT product_warehouse_stock.product_id) as product_count'), 
                        DB::raw('SUM(product_warehouse_stock.available_quantity) as total_quantity'),
                        DB::raw('SUM(product_warehouse_stock.available_quantity * product_warehouse_stock.cmup) as total_value')
                    )
                    ->leftJoin('product_warehouse_stock', 'warehouses.id', '=', 'product_warehouse_stock.warehouse_id')
                    ->groupBy('warehouses.id', 'warehouses.name')
                    ->orderBy('total_value', 'desc')
                    ->limit(5)
                    ->get();
            }
        }

        // For stock_movements table
        if (Schema::hasTable('stock_movements')) {
            // Recent stock movements
            $recentMovements = StockMovement::with(['product', 'warehouse', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->each(function($movement) {
                    $movement->created_at_diff = $movement->created_at->diffForHumans();
                    $movement->description = $this->formatStockMovementDescription($movement);
                });
                
            // Stock activity statistics for dashboard cards
            $stockStats = [
                'totalMovementsToday' => StockMovement::whereDate('created_at', $today)->count(),
                'totalMovementsMonth' => StockMovement::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
                'incomingMonth' => StockMovement::where('movement_type', 'in')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
                'outgoingMonth' => StockMovement::where('movement_type', 'out')
                    ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                    ->count(),
            ];
            
            // Get stock movement types distribution
            $movementTypes = StockMovement::select('movement_type', DB::raw('COUNT(*) as count'))
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->groupBy('movement_type')
                ->orderBy('count', 'desc')
                ->get();
                
            // Get top products by movement
            $topProducts = StockMovement::select('product_id', DB::raw('SUM(quantity) as total_qty'))
                ->where('movement_type', 'out')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->groupBy('product_id')
                ->orderBy('total_qty', 'desc')
                ->limit(5)
                ->with('product')
                ->get();
        }

        // For purchase_orders table
        if (Schema::hasTable('purchase_orders')) {
            // Purchase order statistics
            $pendingPurchaseOrders = PurchaseOrder::whereIn('status', ['draft', 'confirmed'])->count();
            $thisMonthPurchaseOrders = PurchaseOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $receivedPurchaseOrders = PurchaseOrder::where('status', 'received')->count();
            $totalPurchaseOrders = PurchaseOrder::count();
            
            // Calculate purchase value
            $totalPurchaseValue = PurchaseOrder::where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            $thisMonthPurchaseValue = PurchaseOrder::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');
                
            // Get recent purchase orders
            $recentPurchaseOrders = PurchaseOrder::with(['supplier', 'warehouse', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        // For sales_orders table
        if (Schema::hasTable('sales_orders')) {
            // Sales order statistics
            $pendingSalesOrders = SalesOrder::whereIn('status', ['draft', 'confirmed'])->count();
            $thisMonthSalesOrders = SalesOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $deliveredSalesOrders = SalesOrder::where('status', 'delivered')->count();
            $totalSalesOrders = SalesOrder::count();
            
            // Calculate sales value
            $totalSalesValue = SalesOrder::where('status', '!=', 'cancelled')
                ->sum('total_amount');
            
            $thisMonthSalesValue = SalesOrder::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('total_amount');
                
            // Get recent sales orders
            $recentSalesOrders = SalesOrder::with(['customer', 'warehouse', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        // For stock_packages table
        if (Schema::hasTable('stock_packages')) {
            // Get expiring products
            $expiringProducts = DB::table('stock_packages')
                ->where('quantity', '>', 0)
                ->where('available', true)
                ->where('expiry_date', '!=', null)
                ->where('expiry_date', '<=', now()->addDays(30))
                ->join('products', 'stock_packages.product_id', '=', 'products.id')
                ->join('warehouses', 'stock_packages.warehouse_id', '=', 'warehouses.id')
                ->select(
                    'stock_packages.id',
                    'products.name as product_name',
                    'warehouses.name as warehouse_name',
                    'stock_packages.lot_number',
                    'stock_packages.quantity',
                    'stock_packages.expiry_date'
                )
                ->orderBy('expiry_date', 'asc')
                ->limit(5)
                ->get()
                ->map(function($package) {
                    $daysLeft = now()->diffInDays(Carbon::parse($package->expiry_date), false);
                    return [
                        'product' => $package->product_name,
                        'warehouse' => $package->warehouse_name,
                        'lot_number' => $package->lot_number,
                        'quantity' => $package->quantity,
                        'expiry_date' => Carbon::parse($package->expiry_date)->format('Y-m-d'),
                        'days_left' => $daysLeft,
                        'status' => $daysLeft < 0 ? 'expired' : ($daysLeft <= 7 ? 'critical' : 'warning')
                    ];
                });
        }

        return view('admin.dashboard', compact(
            'recentMovements',
            'stockStats',
            'totalProducts',
            'totalStockValue',
            'lowStockProducts',
            'outOfStockProducts',
            'pendingPurchaseOrders',
            'thisMonthPurchaseOrders',
            'receivedPurchaseOrders',
            'totalPurchaseOrders',
            'totalPurchaseValue',
            'thisMonthPurchaseValue',
            'pendingSalesOrders',
            'thisMonthSalesOrders',
            'deliveredSalesOrders',
            'totalSalesOrders',
            'totalSalesValue',
            'thisMonthSalesValue',
            'recentPurchaseOrders',
            'recentSalesOrders',
            'movementTypes',
            'topProducts',
            'warehouseStats',
            'lowStockAlerts',
            'expiringProducts'
        ));
    }

    /**
     * Format a stock movement description
     */
    private function formatStockMovementDescription($movement)
    {
        $productName = $movement->product ? $movement->product->name : 'Produit inconnu';
        $warehouseName = $movement->warehouse ? $movement->warehouse->name : 'Entrepôt inconnu';
        
        $description = $movement->movement_type == 'in' 
            ? "Entrée de {$movement->quantity} {$productName} à {$warehouseName}" 
            : "Sortie de {$movement->quantity} {$productName} de {$warehouseName}";
            
        // Add reference context
        switch ($movement->reference_type) {
            case 'purchase_reception':
                $description .= " (Réception d'achat)";
                break;
            case 'sales_delivery':
                $description .= " (Livraison vente)";
                break;
            case 'transfer':
                $description .= " (Transfert de stock)";
                break;
            case 'adjustment':
                $description .= " (Ajustement de stock)";
                break;
        }
        
        return $description;
    }

    /**
     * Get chart data for AJAX requests
     */
    public function getChartData(Request $request)
    {
        // User must have permission to access dashboard
        if (!auth()->guard('admin')->user()->can('access dashboard')) {
            abort(403, 'Unauthorized action.');
        }
        
        $period = $request->input('period', 'month');
        $labels = [];
        $purchaseData = [];
        $salesData = [];
        $stockInData = [];
        $stockOutData = [];
        
        // Current date for reference
        $now = Carbon::now();
        
        // Check if tables exist
        $stockMovementsExist = Schema::hasTable('stock_movements');
        $purchaseOrdersExist = Schema::hasTable('purchase_orders');
        $salesOrdersExist = Schema::hasTable('sales_orders');
        
        if ($period === 'week') {
            // Last 7 days data
            $startDate = Carbon::now()->subDays(6);
            
            for ($i = 0; $i < 7; $i++) {
                $date = clone $startDate;
                $date->addDays($i);
                $labels[] = $date->format('D');
                
                // Stock movement counts
                $stockInCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('movement_type', 'in')
                        ->count() : 0;
                $stockInData[] = $stockInCount;
                
                $stockOutCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('movement_type', 'out')
                        ->count() : 0;
                $stockOutData[] = $stockOutCount;
                
                // Purchase data
                $dailyPurchaseValue = $purchaseOrdersExist ? 
                    DB::table('purchase_orders')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $purchaseData[] = round($dailyPurchaseValue, 2);
                
                // Sales data
                $dailySalesValue = $salesOrdersExist ? 
                    DB::table('sales_orders')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $salesData[] = round($dailySalesValue, 2);
            }
        } elseif ($period === 'month') {
            // Last 30 days data
            $startDate = Carbon::now()->subDays(29);
            
            for ($i = 0; $i < 30; $i++) {
                $date = clone $startDate;
                $date->addDays($i);
                $labels[] = $date->format('j'); // Day of month without leading zeros
                
                // Stock movement counts
                $stockInCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('movement_type', 'in')
                        ->count() : 0;
                $stockInData[] = $stockInCount;
                
                $stockOutCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('movement_type', 'out')
                        ->count() : 0;
                $stockOutData[] = $stockOutCount;
                
                // Purchase data
                $dailyPurchaseValue = $purchaseOrdersExist ? 
                    DB::table('purchase_orders')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $purchaseData[] = round($dailyPurchaseValue, 2);
                
                // Sales data
                $dailySalesValue = $salesOrdersExist ? 
                    DB::table('sales_orders')
                        ->whereDate('created_at', $date->format('Y-m-d'))
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $salesData[] = round($dailySalesValue, 2);
            }
        } else {
            // Monthly data for the current year
            $year = Carbon::now()->year;
            
            for ($month = 1; $month <= 12; $month++) {
                $date = Carbon::createFromDate($year, $month, 1);
                $labels[] = $date->format('M');
                
                // Stock movement counts
                $stockInCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('movement_type', 'in')
                        ->count() : 0;
                $stockInData[] = $stockInCount;
                
                $stockOutCount = $stockMovementsExist ? 
                    DB::table('stock_movements')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('movement_type', 'out')
                        ->count() : 0;
                $stockOutData[] = $stockOutCount;
                
                // Purchase data
                $monthlyPurchaseValue = $purchaseOrdersExist ? 
                    DB::table('purchase_orders')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $purchaseData[] = round($monthlyPurchaseValue, 2);
                
                // Sales data
                $monthlySalesValue = $salesOrdersExist ? 
                    DB::table('sales_orders')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month)
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount') : 0;
                $salesData[] = round($monthlySalesValue, 2);
            }
        }
        
        return response()->json([
            'labels' => $labels,
            'stockInData' => $stockInData,
            'stockOutData' => $stockOutData,
            'purchaseData' => $purchaseData,
            'salesData' => $salesData
        ]);
    }

    /**
     * Get inventory alerts for dashboard
     *
     * @return array
     */
    public function getInventoryAlerts()
    {
        // Check if product_warehouse_stock table exists
        if (!Schema::hasTable('product_warehouse_stock')) {
            return response()->json([
                'critical' => ['count' => 0, 'items' => []],
                'warning' => ['count' => 0, 'items' => []],
                'notice' => ['count' => 0, 'items' => []]
            ]);
        }
        
        // Get all low stock items
        $lowStock = DB::table('product_warehouse_stock')
            ->whereRaw('available_quantity < min_stock AND min_stock > 0')
            ->join('products', 'product_warehouse_stock.product_id', '=', 'products.id')
            ->join('warehouses', 'product_warehouse_stock.warehouse_id', '=', 'warehouses.id')
            ->select(
                'product_warehouse_stock.id',
                'product_warehouse_stock.product_id',
                'products.name as product_name',
                'products.code as product_code',
                'warehouses.name as warehouse_name',
                'product_warehouse_stock.available_quantity',
                'product_warehouse_stock.min_stock'
            )
            ->get();
        
        // Group by urgency
        $critical = $lowStock->filter(function($item) {
            return ($item->available_quantity / $item->min_stock) < 0.25; // Less than 25% of min stock
        });
        
        $warning = $lowStock->filter(function($item) {
            $ratio = $item->available_quantity / $item->min_stock;
            return $ratio >= 0.25 && $ratio < 0.5; // Between 25% and 50% of min stock
        });
        
        $notice = $lowStock->filter(function($item) {
            $ratio = $item->available_quantity / $item->min_stock;
            return $ratio >= 0.5 && $ratio < 1; // Between 50% and 100% of min stock
        });
        
        // Format for dashboard display
        $formattedCritical = $this->formatInventoryAlerts($critical);
        $formattedWarning = $this->formatInventoryAlerts($warning);
        $formattedNotice = $this->formatInventoryAlerts($notice);
        
        return response()->json([
            'critical' => [
                'count' => $critical->count(),
                'items' => $formattedCritical
            ],
            'warning' => [
                'count' => $warning->count(),
                'items' => $formattedWarning
            ],
            'notice' => [
                'count' => $notice->count(),
                'items' => $formattedNotice
            ]
        ]);
    }

    /**
     * Format inventory alerts for dashboard display
     *
     * @param \Illuminate\Support\Collection $items
     * @return array
     */
    private function formatInventoryAlerts($items)
    {
        return $items->map(function($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'warehouse_name' => $item->warehouse_name,
                'available' => $item->available_quantity,
                'min_stock' => $item->min_stock,
                'percentage' => $item->min_stock > 0 ? round(($item->available_quantity / $item->min_stock) * 100, 1) : 0,
                'url' => route('admin.products.show', $item->product_id)
            ];
        })->take(5)->toArray(); // Limit to 5 items for dashboard
    }

    /**
     * Get expiry alerts for products with expiry dates
     * 
     * @return array
     */
    public function getExpiryAlerts()
    {
        // Check if stock_packages table exists
        if (!Schema::hasTable('stock_packages')) {
            return response()->json([
                'expired' => ['count' => 0, 'items' => []],
                'expiring_soon' => ['count' => 0, 'items' => []],
                'approaching' => ['count' => 0, 'items' => []]
            ]);
        }
        
        // Get stock packages with expiry dates that are approaching or expired
        $packages = DB::table('stock_packages')
            ->where('quantity', '>', 0)
            ->where('available', true)
            ->where('expiry_date', '!=', null)
            ->where('expiry_date', '<=', now()->addMonths(3))
            ->join('products', 'stock_packages.product_id', '=', 'products.id')
            ->join('warehouses', 'stock_packages.warehouse_id', '=', 'warehouses.id')
            ->select(
                'stock_packages.id',
                'stock_packages.product_id',
                'products.name as product_name',
                'warehouses.name as warehouse_name',
                'stock_packages.lot_number',
                'stock_packages.quantity',
                'stock_packages.expiry_date'
            )
            ->get();
            
        // Group by urgency
        $expired = $packages->filter(function($package) {
            return Carbon::parse($package->expiry_date) < Carbon::now();
        });
        
        $expiringSoon = $packages->filter(function($package) {
            $expiryDate = Carbon::parse($package->expiry_date);
            return $expiryDate >= Carbon::now() && $expiryDate <= Carbon::now()->addMonth();
        });
        
        $approaching = $packages->filter(function($package) {
            $expiryDate = Carbon::parse($package->expiry_date);
            return $expiryDate > Carbon::now()->addMonth() && $expiryDate <= Carbon::now()->addMonths(3);
        });
        
        // Format for dashboard display
        $formattedExpired = $this->formatExpiryAlerts($expired);
        $formattedExpiringSoon = $this->formatExpiryAlerts($expiringSoon);
        $formattedApproaching = $this->formatExpiryAlerts($approaching);
        
        return response()->json([
            'expired' => [
                'count' => $expired->count(),
                'items' => $formattedExpired
            ],
            'expiring_soon' => [
                'count' => $expiringSoon->count(),
                'items' => $formattedExpiringSoon
            ],
            'approaching' => [
                'count' => $approaching->count(),
                'items' => $formattedApproaching
            ]
        ]);
    }

    /**
     * Format expiry alerts for dashboard display
     *
     * @param \Illuminate\Support\Collection $packages
     * @return array
     */
    private function formatExpiryAlerts($packages)
    {
        return $packages->map(function($package) {
            $expiryDate = Carbon::parse($package->expiry_date);
            $daysLeft = Carbon::now()->diffInDays($expiryDate, false);
            
            return [
                'id' => $package->id,
                'product_id' => $package->product_id,
                'product_name' => $package->product_name,
                'lot_number' => $package->lot_number,
                'warehouse_name' => $package->warehouse_name,
                'quantity' => $package->quantity,
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'days_left' => $daysLeft,
                'days_text' => $daysLeft < 0 ? 'Expiré depuis ' . abs($daysLeft) . ' jours' : $daysLeft . ' jours restants',
                'url' => route('admin.products.show', $package->product_id)
            ];
        })->take(5)->toArray(); // Limit to 5 items for dashboard
    }
}
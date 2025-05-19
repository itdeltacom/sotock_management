<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Carbon\Carbon;

class StockMovementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_stock_movements', ['only' => ['index', 'data']]);
    }

    /**
     * Display a listing of stock movements.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $products = Product::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.stock-movements.index', compact('products', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying stock movements: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing stock movements.');
        }
    }

    /**
     * Get stock movements data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = StockMovement::with(['product', 'warehouse', 'createdBy']);
            
            // Apply filters
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }
            
            if ($request->has('warehouse_id') && $request->warehouse_id) {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            
            if ($request->has('movement_type') && $request->movement_type != 'all') {
                $query->where('movement_type', $request->movement_type);
            }
            
            if ($request->has('reference_type') && $request->reference_type != 'all') {
                $query->where('reference_type', $request->reference_type);
            }
            
            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $movements = $query->orderBy('created_at', 'desc')->get();
            
            return DataTables::of($movements)
                ->editColumn('created_at', function (StockMovement $movement) {
                    return $movement->created_at->format('Y-m-d H:i');
                })
                ->addColumn('product_name', function (StockMovement $movement) {
                    return $movement->product->name . ' (' . $movement->product->code . ')';
                })
                ->addColumn('warehouse_name', function (StockMovement $movement) {
                    return $movement->warehouse->name;
                })
                ->addColumn('created_by', function (StockMovement $movement) {
                    return $movement->createdBy ? $movement->createdBy->name : 'System';
                })
                ->addColumn('stock_change', function (StockMovement $movement) {
                    return $movement->movement_type == 'in' ? 
                        '<span class="text-success">+' . $movement->quantity . '</span>' : 
                        '<span class="text-danger">-' . $movement->quantity . '</span>';
                })
                ->addColumn('reference_info', function (StockMovement $movement) {
                    $referenceText = ucfirst(str_replace('_', ' ', $movement->reference_type));
                    
                    switch ($movement->reference_type) {
                        case 'purchase_reception':
                            $receptionItem = DB::table('stock_reception_items')
                                ->join('stock_receptions', 'stock_receptions.id', '=', 'stock_reception_items.stock_reception_id')
                                ->where('stock_reception_items.id', $movement->reference_id)
                                ->select('stock_receptions.reference_no', 'stock_receptions.id as reception_id')
                                ->first();
                            
                            if ($receptionItem) {
                                return $referenceText . ' - ' . $receptionItem->reference_no;
                            }
                            break;
                            
                        case 'sales_delivery':
                            $deliveryItem = DB::table('stock_delivery_items')
                                ->join('stock_deliveries', 'stock_deliveries.id', '=', 'stock_delivery_items.stock_delivery_id')
                                ->where('stock_delivery_items.id', $movement->reference_id)
                                ->select('stock_deliveries.reference_no', 'stock_deliveries.id as delivery_id')
                                ->first();
                            
                            if ($deliveryItem) {
                                return $referenceText . ' - ' . $deliveryItem->reference_no;
                            }
                            break;
                            
                        case 'transfer':
                            $transferItem = DB::table('stock_transfer_items')
                                ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_items.stock_transfer_id')
                                ->where('stock_transfer_items.id', $movement->reference_id)
                                ->select('stock_transfers.reference_no', 'stock_transfers.id as transfer_id')
                                ->first();
                            
                            if ($transferItem) {
                                return $referenceText . ' - ' . $transferItem->reference_no;
                            }
                            break;
                            
                        case 'adjustment':
                            $adjustmentItem = DB::table('stock_adjustment_items')
                                ->join('stock_adjustments', 'stock_adjustments.id', '=', 'stock_adjustment_items.stock_adjustment_id')
                                ->where('stock_adjustment_items.id', $movement->reference_id)
                                ->select('stock_adjustments.reference_no', 'stock_adjustments.id as adjustment_id', 'stock_adjustments.type')
                                ->first();
                            
                            if ($adjustmentItem) {
                                return $referenceText . ' (' . ucfirst($adjustmentItem->type) . ') - ' . $adjustmentItem->reference_no;
                            }
                            break;
                    }
                    
                    return $referenceText;
                })
                ->addColumn('movement_type_label', function (StockMovement $movement) {
                    return $movement->movement_type == 'in' ? 
                        '<span class="badge bg-success">In</span>' : 
                        '<span class="badge bg-danger">Out</span>';
                })
                ->addColumn('details', function (StockMovement $movement) {
                    $details = [];
                    
                    $details[] = 'Quantity: ' . $movement->quantity . ' ' . $movement->product->unit;
                    $details[] = 'Unit Cost: ' . number_format($movement->unit_cost, 2) . ' MAD';
                    $details[] = 'Total Cost: ' . number_format($movement->total_cost, 2) . ' MAD';
                    $details[] = 'Previous Stock: ' . $movement->stock_before . ' ' . $movement->product->unit;
                    $details[] = 'New Stock: ' . $movement->stock_after . ' ' . $movement->product->unit;
                    
                    if ($movement->cmup_before != $movement->cmup_after) {
                        $details[] = 'Previous CMUP: ' . number_format($movement->cmup_before, 2) . ' MAD';
                        $details[] = 'New CMUP: ' . number_format($movement->cmup_after, 2) . ' MAD';
                    }
                    
                    if ($movement->notes) {
                        $details[] = 'Notes: ' . $movement->notes;
                    }
                    
                    return implode('<br>', $details);
                })
                ->rawColumns(['stock_change', 'movement_type_label', 'details'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting stock movements data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve stock movements data.'
            ], 500);
        }
    }
}
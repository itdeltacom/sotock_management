<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use App\Services\Warehouse\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view warehouses')) {
                abort(403, 'Unauthorized action.');
            }

            $totalWarehouses = Warehouse::count();
            $activeWarehouses = Warehouse::where('active', true)->count();

            return view('admin.warehouses.index', compact('totalWarehouses', 'activeWarehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying warehouses: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing warehouses.');
        }
    }

    /**
     * Get warehouses data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view warehouses.'
                ], 403);
            }

            $query = Warehouse::query();
            
            // Apply filters if provided
            if ($request->has('active') && $request->active !== '') {
                $query->where('active', $request->active);
            }
            
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            }

            $warehouses = $query->get();

            return DataTables::of($warehouses)
                ->addColumn('products_count', function (Warehouse $warehouse) {
                    return $warehouse->productStock()->count();
                })
                ->addColumn('stock_value', function (Warehouse $warehouse) {
                    $value = $warehouse->productStock()
                        ->selectRaw('SUM(available_quantity * cmup) as total_value')
                        ->first()
                        ->total_value ?? 0;
                    
                    return number_format($value, 2);
                })
                ->addColumn('action', function (Warehouse $warehouse) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view warehouses')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $warehouse->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('edit warehouses')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $warehouse->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('delete warehouses')) {
                        $disabledClass = ($warehouse->productStock()->count() > 0) ? 'disabled' : '';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete ' . $disabledClass . '" data-id="' . $warehouse->id . '" ' . $disabledClass . '>
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting warehouse data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouses data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create warehouses.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:warehouses',
                'name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            
            if (!isset($data['active'])) {
                $data['active'] = true;
            }

            $warehouse = Warehouse::create($data);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($warehouse)
                    ->withProperties(['warehouse_id' => $warehouse->id, 'warehouse_name' => $warehouse->name])
                    ->log('Created warehouse');
            }

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully.',
                'warehouse' => $warehouse
            ]);
        } catch (Exception $e) {
            Log::error('Error creating warehouse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view warehouses.'
                ], 403);
            }

            $warehouse = Warehouse::findOrFail($id);
            
            // Get warehouse statistics
            $stockData = $this->warehouseService->getWarehouseStock($warehouse);
            
            // Get recent movements
            $recentMovements = $warehouse->stockMovements()
                ->with(['product'])
                ->latest()
                ->take(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'warehouse' => $warehouse,
                'stockData' => [
                    'total_products' => $stockData['total_products'],
                    'total_quantity' => $stockData['total_quantity'],
                    'total_value' => $stockData['total_value'],
                ],
                'recentMovements' => $recentMovements
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving warehouse details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouse details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit warehouses.'
                ], 403);
            }

            $warehouse = Warehouse::findOrFail($id);

            return response()->json([
                'success' => true,
                'warehouse' => $warehouse
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving warehouse for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouse for editing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit warehouses.'
                ], 403);
            }

            $warehouse = Warehouse::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50|unique:warehouses,code,' . $id,
                'name' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except('_method');
            
            $oldValues = [
                'code' => $warehouse->code,
                'name' => $warehouse->name,
                'active' => $warehouse->active
            ];

            $warehouse->update($data);

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($warehouse)
                    ->withProperties([
                        'warehouse_id' => $warehouse->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'code' => $warehouse->code,
                            'name' => $warehouse->name,
                            'active' => $warehouse->active
                        ]
                    ])
                    ->log('Updated warehouse');
            }

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully.',
                'warehouse' => $warehouse
            ]);
        } catch (Exception $e) {
            Log::error('Error updating warehouse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete warehouses.'
                ], 403);
            }

            $warehouse = Warehouse::findOrFail($id);

            // Check if warehouse has stock
            if ($warehouse->productStock()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete warehouse with stock. Please transfer all stock first.'
                ], 422);
            }

            $warehouseData = [
                'id' => $warehouse->id,
                'code' => $warehouse->code,
                'name' => $warehouse->name
            ];

            $warehouse->delete();

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($warehouseData)
                    ->log('Deleted warehouse');
            }

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting warehouse: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete warehouse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get warehouse stock information.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getStockInfo($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view warehouses')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view warehouse stock.'
                ], 403);
            }

            $warehouse = Warehouse::findOrFail($id);
            $stockData = $this->warehouseService->getWarehouseStock($warehouse);
            
            return response()->json([
                'success' => true,
                'warehouse' => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                    'code' => $warehouse->code
                ],
                'stock_info' => $stockData
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving warehouse stock info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouse stock information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all warehouses for select dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getWarehousesList()
    {
        try {
            $warehouses = Warehouse::where('active', true)
                ->select('id', 'name', 'code', 'location')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'warehouses' => $warehouses
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving warehouses list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve warehouses list: ' . $e->getMessage()
            ], 500);
        }
    }
}
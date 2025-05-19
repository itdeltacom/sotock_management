<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\ProductWarehouseStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_products', ['only' => ['index', 'show', 'data', 'getStockInfo']]);
        $this->middleware('permission:create_products', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_products', ['only' => ['edit', 'update', 'updateStock']]);
        $this->middleware('permission:delete_products', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of products.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $categories = ProductCategory::orderBy('name')->get();
            $brands = ProductBrand::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.products.index', compact('categories', 'brands', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying products: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing products.');
        }
    }

    /**
     * Get products data for DataTables.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            $query = Product::with(['categories']);
            
            // Apply filters if provided
            if ($request->has('category_id') && $request->category_id) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('product_categories.id', $request->category_id);
                });
            }
            
            if ($request->has('brand_id') && $request->brand_id) {
                $query->where('brand_id', $request->brand_id);
            }
            
            if ($request->has('status') && $request->status !== 'all') {
                $active = $request->status === 'active';
                $query->where('active', $active);
            }
            
            // Get all products
            $products = $query->get();
            
            return DataTables::of($products)
                ->addColumn('categories', function (Product $product) {
                    return $product->categories->pluck('name')->implode(', ');
                })
                ->addColumn('brand_name', function (Product $product) {
                    // Assuming there's a brand relationship. If not, this would need to be adjusted.
                    return $product->brand ? $product->brand->name : '-';
                })
                ->addColumn('stock', function (Product $product) {
                    $totalStock = $product->getTotalStock();
                    return number_format($totalStock, 2) . ' ' . $product->unit;
                })
                ->addColumn('image_thumbnail', function (Product $product) {
                    if ($product->image) {
                        return '<img src="' . asset('storage/' . $product->image) . '" alt="' . $product->name . '" class="product-thumbnail" width="50">';
                    }
                    return '<span class="text-muted">No image</span>';
                })
                ->addColumn('status_label', function (Product $product) {
                    return $product->active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('action', function (Product $product) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_products')) {
                        $actions .= '<a href="' . route('admin.products.show', $product->id) . '" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_products')) {
                        $actions .= '<a href="' . route('admin.products.edit', $product->id) . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_products')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $product->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['image_thumbnail', 'status_label', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting product data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve products data.'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new product.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $categories = ProductCategory::orderBy('name')->get();
            $brands = ProductBrand::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.products.create', compact('categories', 'brands', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying product create form: ' . $e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'An error occurred while accessing the create form.');
        }
    }

    /**
     * Store a newly created product.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:100|unique:products',
                'description' => 'nullable|string',
                'unit' => 'required|string|max:50',
                'barcode' => 'nullable|string|max:100|unique:products',
                'sku' => 'nullable|string|max:100|unique:products',
                'brand_id' => 'nullable|exists:product_brands,id',
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'exists:product_categories,id',
                'image' => 'nullable|image|max:2048',
                'active' => 'boolean',
                'attributes' => 'nullable|array',
                'warehouse_stock' => 'nullable|array',
                'warehouse_stock.*.warehouse_id' => 'required|exists:warehouses,id',
                'warehouse_stock.*.min_stock' => 'nullable|numeric|min:0',
                'warehouse_stock.*.max_stock' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Start transaction
            DB::beginTransaction();
            
            // Prepare product data
            $productData = $request->except(['image', 'category_ids', 'warehouse_stock', 'attributes']);
            
            // Set default status if not provided
            if (!isset($productData['active'])) {
                $productData['active'] = true;
            }
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $productData['image'] = $request->file('image')->store('products', 'public');
            }
            
            // Process attributes if provided
            if ($request->has('attributes') && is_array($request->attributes)) {
                $productData['attributes'] = $request->attributes;
            }
            
            // Create the product
            $product = Product::create($productData);
            
            // Attach categories
            $product->categories()->attach($request->category_ids);
            
            // Process warehouse stock settings
            if ($request->has('warehouse_stock') && is_array($request->warehouse_stock)) {
                foreach ($request->warehouse_stock as $stock) {
                    if (isset($stock['warehouse_id'])) {
                        ProductWarehouseStock::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $stock['warehouse_id'],
                            'available_quantity' => 0,
                            'reserved_quantity' => 0,
                            'cmup' => 0,
                            'min_stock' => $stock['min_stock'] ?? null,
                            'max_stock' => $stock['max_stock'] ?? null,
                        ]);
                    }
                }
            }
            
            // Commit transaction
            DB::commit();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($product)
                    ->withProperties(['product_id' => $product->id, 'product_name' => $product->name])
                    ->log('Created product');
            }
            
            return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
        } catch (Exception $e) {
            // Rollback transaction
            DB::rollBack();
            
            Log::error('Error creating product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create product: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $product = Product::with(['categories', 'stock.warehouse'])->findOrFail($id);
            
            // Get stock packages
            $stockPackages = $product->stockPackages()
                ->where('available', true)
                ->where('quantity', '>', 0)
                ->with('warehouse')
                ->orderBy('expiry_date')
                ->get();
            
            // Get recent movements
            $recentMovements = $product->stockMovements()
                ->with(['warehouse', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            return view('admin.products.show', compact('product', 'stockPackages', 'recentMovements'));
        } catch (Exception $e) {
            Log::error('Error displaying product: ' . $e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'An error occurred while accessing the product.');
        }
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $product = Product::with(['categories', 'stock.warehouse'])->findOrFail($id);
            $categories = ProductCategory::orderBy('name')->get();
            $brands = ProductBrand::where('active', true)->orderBy('name')->get();
            $warehouses = Warehouse::where('active', true)->orderBy('name')->get();
            
            return view('admin.products.edit', compact('product', 'categories', 'brands', 'warehouses'));
        } catch (Exception $e) {
            Log::error('Error displaying product edit form: ' . $e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'An error occurred while accessing the edit form.');
        }
    }

    /**
     * Update the specified product.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:100|unique:products,code,' . $id,
                'description' => 'nullable|string',
                'unit' => 'required|string|max:50',
                'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $id,
                'sku' => 'nullable|string|max:100|unique:products,sku,' . $id,
                'brand_id' => 'nullable|exists:product_brands,id',
                'category_ids' => 'required|array|min:1',
                'category_ids.*' => 'exists:product_categories,id',
                'image' => 'nullable|image|max:2048',
                'active' => 'boolean',
                'attributes' => 'nullable|array',
                'warehouse_stock' => 'nullable|array',
                'warehouse_stock.*.warehouse_id' => 'required|exists:warehouses,id',
                'warehouse_stock.*.min_stock' => 'nullable|numeric|min:0',
                'warehouse_stock.*.max_stock' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Start transaction
            DB::beginTransaction();
            
            // Prepare product data
            $productData = $request->except(['_method', '_token', 'image', 'category_ids', 'warehouse_stock', 'attributes']);
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                
                $productData['image'] = $request->file('image')->store('products', 'public');
            }
            
            // Process attributes if provided
            if ($request->has('attributes') && is_array($request->attributes)) {
                $productData['attributes'] = $request->attributes;
            }
            
            // Store old values for logging
            $oldValues = [
                'name' => $product->name,
                'code' => $product->code,
                'active' => $product->active,
                'categories' => $product->categories->pluck('id')->toArray()
            ];
            
            // Update the product
            $product->update($productData);
            
            // Sync categories
            $product->categories()->sync($request->category_ids);
            
            // Process warehouse stock settings
            if ($request->has('warehouse_stock') && is_array($request->warehouse_stock)) {
                foreach ($request->warehouse_stock as $stock) {
                    if (isset($stock['warehouse_id'])) {
                        ProductWarehouseStock::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'warehouse_id' => $stock['warehouse_id']
                            ],
                            [
                                'min_stock' => $stock['min_stock'] ?? null,
                                'max_stock' => $stock['max_stock'] ?? null,
                            ]
                        );
                    }
                }
            }
            
            // Commit transaction
            DB::commit();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($product)
                    ->withProperties([
                        'product_id' => $product->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $product->name,
                            'code' => $product->code,
                            'active' => $product->active,
                            'categories' => $request->category_ids
                        ]
                    ])
                    ->log('Updated product');
            }
            
            return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
        } catch (Exception $e) {
            // Rollback transaction
            DB::rollBack();
            
            Log::error('Error updating product: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update product: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Check if product has stock
            $totalStock = $product->getTotalStock();
            if ($totalStock > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete product with existing stock. Please deplete or transfer stock first.'
                ], 422);
            }
            
            // Check if product is used in any purchase or sales order
            $purchaseOrderItemsCount = $product->purchaseOrderItems()->count();
            $salesOrderItemsCount = $product->salesOrderItems()->count();
            
            if ($purchaseOrderItemsCount > 0 || $salesOrderItemsCount > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete product that has been used in purchase or sales orders.'
                ], 422);
            }
            
            // Store product data for logging
            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code
            ];
            
            // Delete product image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            
            // Delete the product (using soft delete)
            $product->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($productData)
                    ->log('Deleted product');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock information for a specific product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStockInfo($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Get stock data using the helper method from your WarehouseService
            $stockInfo = app(\App\Services\Warehouse\WarehouseService::class)->getProductStock($product);
            
            return response()->json([
                'success' => true,
                'data' => $stockInfo
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving product stock info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve product stock info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock min/max values
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStock(Request $request, $id)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'warehouse_id' => 'required|exists:warehouses,id',
                'min_stock' => 'nullable|numeric|min:0',
                'max_stock' => 'nullable|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Find product
            $product = Product::findOrFail($id);
            
            // Update or create stock record
            $stock = ProductWarehouseStock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $request->warehouse_id
                ],
                [
                    'min_stock' => $request->min_stock,
                    'max_stock' => $request->max_stock,
                ]
            );
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($product)
                    ->withProperties([
                        'product_id' => $product->id,
                        'warehouse_id' => $request->warehouse_id,
                        'min_stock' => $request->min_stock,
                        'max_stock' => $request->max_stock
                    ])
                    ->log('Updated product stock settings');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Stock settings updated successfully.',
                'data' => $stock
            ]);
        } catch (Exception $e) {
            Log::error('Error updating product stock settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update stock settings: ' . $e->getMessage()
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductWarehouseStock;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view products')) {
                abort(403, 'Unauthorized action.');
            }

            $totalProducts = Product::count();
            $activeProducts = Product::where('active', true)->count();
            $categories = ProductCategory::where('active', true)->get();
            $brands = ProductBrand::where('active', true)->get();
            $warehouses = Warehouse::where('active', true)->get();

            return view('admin.products.index', compact(
                'totalProducts',
                'activeProducts',
                'categories',
                'brands',
                'warehouses'
            ));
        } catch (Exception $e) {
            Log::error('Error displaying products: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing products.');
        }
    }

    /**
     * Get products data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view products.'
                ], 403);
            }

            $query = Product::with(['brand', 'categories']);

            // Apply filters if provided
            if ($request->has('category_id') && $request->category_id) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            if ($request->has('brand_id') && $request->brand_id) {
                $query->where('brand_id', $request->brand_id);
            }

            if ($request->has('active') && $request->active !== '') {
                $query->where('active', $request->active);
            }

            $products = $query->get();

            return DataTables::of($products)
                ->addColumn('image_preview', function (Product $product) {
                    if ($product->image) {
                        return '<img src="' . asset('storage/' . $product->image) . '" alt="' . $product->name . '" class="product-thumbnail" width="50">';
                    }
                    return '<span class="text-muted">No image</span>';
                })
                ->addColumn('brand_name', function (Product $product) {
                    return $product->brand ? $product->brand->name : '-';
                })
                ->addColumn('categories_list', function (Product $product) {
                    return $product->categories->pluck('name')->implode(', ');
                })
                ->addColumn('stock_status', function (Product $product) {
                    $totalStock = $product->getTotalStock();
                    
                    if ($totalStock <= 0) {
                        return '<span class="badge bg-danger">Out of Stock</span>';
                    } elseif ($totalStock < 10) {
                        return '<span class="badge bg-warning">Low Stock: ' . $totalStock . '</span>';
                    } else {
                        return '<span class="badge bg-success">In Stock: ' . $totalStock . '</span>';
                    }
                })
                ->addColumn('action', function (Product $product) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view products')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $product->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('edit products')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $product->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('delete products')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $product->id . '">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $actions;
                })
                ->rawColumns(['image_preview', 'stock_status', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting product data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve products data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create products.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:products',
                'description' => 'nullable|string',
                'unit' => 'required|string|max:20',
                'barcode' => 'nullable|string|max:50|unique:products',
                'sku' => 'nullable|string|max:50|unique:products',
                'brand_id' => 'nullable|exists:brands,id',
                'image' => 'nullable|image|max:2048',
                'active' => 'boolean',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:product_categories,id',
                'attributes' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['image', 'categories', 'attributes']);
            
            // Handle attributes array
            if ($request->has('attributes')) {
                $data['attributes'] = json_encode($request->attributes);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('product-images', 'public');
            }

            if (!isset($data['active'])) {
                $data['active'] = true;
            }

            // Create the product
            $product = Product::create($data);

            // Attach categories
            if ($request->has('categories') && is_array($request->categories)) {
                $product->categories()->attach($request->categories);
            }

            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($product)
                    ->withProperties(['product_id' => $product->id, 'product_name' => $product->name, 'code' => $product->code])
                    ->log('Created product');
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'product' => $product
            ]);
        } catch (Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view products.'
                ], 403);
            }

            $product = Product::with(['brand', 'categories', 'stock.warehouse'])->findOrFail($id);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            $product->categories_list = $product->categories->pluck('name')->implode(', ');
            $product->total_stock = $product->getTotalStock();
            $product->average_cost = $product->getCMUP();

            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving product details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve product details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit products.'
                ], 403);
            }

            $product = Product::with(['brand', 'categories'])->findOrFail($id);
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            $product->category_ids = $product->categories->pluck('id')->toArray();

            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving product for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve product for editing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified product.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit products.'
                ], 403);
            }

            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:products,code,' . $id,
                'description' => 'nullable|string',
                'unit' => 'required|string|max:20',
                'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $id,
                'sku' => 'nullable|string|max:50|unique:products,sku,' . $id,
                'brand_id' => 'nullable|exists:brands,id',
                'image' => 'nullable|image|max:2048',
                'active' => 'boolean',
                'categories' => 'nullable|array',
                'categories.*' => 'exists:product_categories,id',
                'attributes' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->except(['image', 'categories', '_method', 'attributes']);
            
            // Handle attributes array
            if ($request->has('attributes')) {
                $data['attributes'] = json_encode($request->attributes);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('product-images', 'public');
            }

            $oldValues = [
                'name' => $product->name,
                'code' => $product->code,
                'active' => $product->active,
                'brand_id' => $product->brand_id
            ];

            // Update the product
            $product->update($data);

            // Sync categories
            if ($request->has('categories')) {
                $product->categories()->sync($request->categories);
            }

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
                            'brand_id' => $product->brand_id
                        ]
                    ])
                    ->log('Updated product');
            }

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'product' => $product
            ]);
        } catch (Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete products.'
                ], 403);
            }

            $product = Product::findOrFail($id);

            // Check if product has stock
            if ($product->getTotalStock() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete product with existing stock. Please adjust stock first.'
                ], 422);
            }

            // Check if product has purchase or sales order items
            if ($product->purchaseOrderItems()->count() > 0 || $product->salesOrderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete product with existing orders. Consider deactivating it instead.'
                ], 422);
            }

            $productData = [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code
            ];

            // Delete image if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Detach categories before deleting
            $product->categories()->detach();
            
            // Delete the product
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
     * Get product stock information.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStockInfo($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view products')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view product stock.'
                ], 403);
            }

            $product = Product::with(['stock.warehouse'])->findOrFail($id);
            
            $stockInfo = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code
                ],
                'total_stock' => $product->getTotalStock(),
                'global_cmup' => $product->getCMUP(),
                'warehouses' => []
            ];

            foreach ($product->stock as $stock) {
                $stockInfo['warehouses'][] = [
                    'warehouse_id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse->name,
                    'available' => $stock->available_quantity,
                    'reserved' => $stock->reserved_quantity,
                    'total' => $stock->available_quantity + $stock->reserved_quantity,
                    'cmup' => $stock->cmup,
                    'min_stock' => $stock->min_stock,
                    'max_stock' => $stock->max_stock,
                    'is_low' => $stock->isLowStock(),
                    'is_over' => $stock->isOverStock()
                ];
            }

            return response()->json([
                'success' => true,
                'stock_info' => $stockInfo
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving product stock info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve product stock information: ' . $e->getMessage()
            ], 500);
        }
    }
}
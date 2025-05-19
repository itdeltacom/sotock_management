<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductBrandController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of brands.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // User must have permission to access brands
            if (!auth()->guard('admin')->user()->can('view_brands')) {
                abort(403, 'Unauthorized action.');
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return view('admin.brands.index', [
                    'tableExists' => false,
                    'errorMessage' => 'The product_brands table does not exist in the database.'
                ]);
            }

            // Get total active brands count
            $activeBrands = 0;
            $totalBrands = 0;
            
            try {
                $totalBrands = ProductBrand::count();
                $activeBrands = ProductBrand::where('active', true)->count();
            } catch (Exception $e) {
                Log::error('Error counting brands: ' . $e->getMessage());
            }

            return view('admin.brands.index', [
                'tableExists' => true,
                'totalBrands' => $totalBrands,
                'activeBrands' => $activeBrands
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying brands: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing brands.');
        }
    }

    /**
     * Get brands data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        try {
            // User must have permission to access brands
            if (!auth()->guard('admin')->user()->can('view_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }

            // Check if the products table exists for counting relationship
            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $brands = ProductBrand::withCount('products')->get();
            } else {
                $brands = ProductBrand::all();
                // Add a dummy products_count
                $brands->each(function($brand) {
                    $brand->products_count = 0;
                });
            }
            
            return DataTables::of($brands)
                ->addColumn('logo_image', function (ProductBrand $brand) {
                    if ($brand->logo) {
                        return '<img src="' . asset('storage/' . $brand->logo) . '" alt="' . $brand->name . '" class="brand-logo-thumbnail" width="50">';
                    }
                    return '<span class="text-muted">No logo</span>';
                })
                ->addColumn('action', function (ProductBrand $brand) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_brands')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $brand->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_brands')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $brand->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }
                    
                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete_brands')) {
                        $disabledClass = $brand->products_count > 0 ? 'disabled' : '';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete ' . $disabledClass . '" data-id="' . $brand->id . '" ' . $disabledClass . '>
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['logo_image', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting brand data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve brands data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created brand.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // User must have permission to create brands
            if (!auth()->guard('admin')->user()->can('create_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_brands',
                'code' => 'nullable|string|max:50|unique:product_brands',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:500',
                'logo' => 'nullable|image|max:2048',
                'active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $request->except(['logo']);
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('brand-logos', 'public');
            }
            
            // Set default status if not provided
            if (!isset($data['active'])) {
                $data['active'] = true;
            }
            
            // Create the brand
            $brand = ProductBrand::create($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($brand)
                    ->withProperties(['brand_id' => $brand->id, 'brand_name' => $brand->name])
                    ->log('Created product brand');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully.',
                'brand' => $brand
            ]);
        } catch (Exception $e) {
            Log::error('Error creating brand: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified brand.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // User must have permission to view brands
            if (!auth()->guard('admin')->user()->can('view_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }
            
            // Check if products table exists
            $productsTableExists = Schema::hasTable('products');
            
            if ($productsTableExists) {
                $brand = ProductBrand::with(['products' => function($query) {
                    $query->where('active', true)->limit(10);
                }])->withCount('products')->findOrFail($id);
            } else {
                $brand = ProductBrand::findOrFail($id);
                $brand->products = collect([]);
                $brand->products_count = 0;
            }
            
            // Add full logo URL
            $brand->logo_url = $brand->logo ? asset('storage/' . $brand->logo) : null;
            
            return response()->json([
                'success' => true,
                'brand' => $brand
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving brand details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve brand details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified brand.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            // User must have permission to edit brands
            if (!auth()->guard('admin')->user()->can('edit_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }
            
            $brand = ProductBrand::findOrFail($id);
            
            // Add full logo URL
            $brand->logo_url = $brand->logo ? asset('storage/' . $brand->logo) : null;
            
            return response()->json([
                'success' => true,
                'brand' => $brand
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving brand for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve brand for editing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified brand.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // User must have permission to edit brands
            if (!auth()->guard('admin')->user()->can('edit_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }
            
            $brand = ProductBrand::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_brands,name,' . $id,
                'code' => 'nullable|string|max:50|unique:product_brands,code,' . $id,
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:500',
                'logo' => 'nullable|image|max:2048',
                'active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $request->except(['logo', '_method']);
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($brand->logo) {
                    Storage::disk('public')->delete($brand->logo);
                }
                
                $data['logo'] = $request->file('logo')->store('brand-logos', 'public');
            }
            
            // Store old values for logging
            $oldValues = [
                'name' => $brand->name,
                'code' => $brand->code,
                'active' => $brand->active
            ];
            
            // Update the brand
            $brand->update($data);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($brand)
                    ->withProperties([
                        'brand_id' => $brand->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $brand->name,
                            'code' => $brand->code,
                            'active' => $brand->active
                        ]
                    ])
                    ->log('Updated product brand');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully.',
                'brand' => $brand
            ]);
        } catch (Exception $e) {
            Log::error('Error updating brand: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update brand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified brand.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // User must have permission to delete brands
            if (!auth()->guard('admin')->user()->can('delete_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('product_brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product_brands table does not exist in the database.'
                ], 500);
            }
            
            // Check if products table exists
            $productsTableExists = Schema::hasTable('products');
            
            if ($productsTableExists) {
                $brand = ProductBrand::withCount('products')->findOrFail($id);
                
                // Check if brand has products
                if ($brand->products_count > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot delete brand with associated products. Please reassign products first.'
                    ], 422);
                }
            } else {
                $brand = ProductBrand::findOrFail($id);
            }
            
            // Store brand data for logging
            $brandData = [
                'id' => $brand->id,
                'name' => $brand->name,
                'code' => $brand->code
            ];
            
            // Delete logo if exists
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            
            // Delete the brand
            $brand->delete();
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($brandData)
                    ->log('Deleted product brand');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Brand deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting brand: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete brand: ' . $e->getMessage()
            ], 500);
        }
    }
}
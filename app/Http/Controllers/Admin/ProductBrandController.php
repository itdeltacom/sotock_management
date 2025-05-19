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
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Voku\Helper\ASCII;

class ProductBrandController extends Controller
{
    /**
     * Display a listing of brands.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // User must have permission to access brands
            if (!auth()->guard('admin')->user()->can('view brands')) {
                abort(403, 'Unauthorized action.');
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return view('admin.brands.index', [
                    'tableExists' => false,
                    'errorMessage' => 'The product brands table does not exist in the database.'
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
            if (!auth()->guard('admin')->user()->can('view brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product brands table does not exist in the database.'
                ], 500);
            }

            // Check if the products table exists for counting relationship
            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $brands = ProductBrand::withCount('products')->get();
            } else {
                $brands = ProductBrand::all();
                // Add a dummy products_count
                $brands->each(function ($brand) {
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
                    if (Auth::guard('admin')->user()->can('view brands')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $brand->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }

                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit brands')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $brand->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }

                    // Delete button
                    if (Auth::guard('admin')->user()->can('delete brands')) {
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
     * Generate a unique slug from the name.
     *
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    protected function generateUniqueSlug($name, $excludeId = null)
    {
        // Transliterate accented characters using voku/portable-ascii
        $cleanName = ASCII::to_ascii($name);
        // Generate base slug
        $slug = Str::slug($cleanName);

        // Ensure uniqueness
        $baseSlug = $slug;
        $counter = 1;

        while (ProductBrand::where('slug', $slug)
            ->where('id', '!=', $excludeId)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
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
            if (!auth()->guard('admin')->user()->can('create brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The brands table does not exist in the database.'
                ], 500);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:brands',
                'meta_title' => 'nullable|string|max:100',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'code' => 'nullable|string|max:50|unique:brands',
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

            // Generate slug from name
            $data['slug'] = $this->generateUniqueSlug($data['name']);

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
                    ->withProperties(['brand_id' => $brand->id, 'brand_name' => $brand->name, 'slug' => $brand->slug])
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // User must have permission to view brands
            if (!auth()->guard('admin')->user()->can('view brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The brands table does not exist in the database.'
                ], 500);
            }

            // Check if products table exists
            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $brand = ProductBrand::with(['products' => function ($query) {
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            // User must have permission to edit brands
            if (!auth()->guard('admin')->user()->can('edit brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The brands table does not exist in the database.'
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
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // User must have permission to edit brands
            if (!auth()->guard('admin')->user()->can('edit brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The brands table does not exist in the database.'
                ], 500);
            }

            $brand = ProductBrand::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:brands,name,' . $id,
                'meta_title' => 'nullable|string|max:100',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'code' => 'nullable|string|max:50|unique:brands,code,' . $id,
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

            // Generate slug from name
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);

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
                'slug' => $brand->slug,
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
                            'slug' => $brand->slug,
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // User must have permission to delete brands
            if (!auth()->guard('admin')->user()->can('delete brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete brands.'
                ], 403);
            }

            // Check if the table exists
            if (!Schema::hasTable('brands')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The brands table does not exist in the database.'
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
                'slug' => $brand->slug,
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
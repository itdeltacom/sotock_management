<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
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

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view categories')) {
                abort(403, 'Unauthorized action.');
            }

            if (!Schema::hasTable('product_categories')) {
                return view('admin.categories.index', [
                    'tableExists' => false,
                    'errorMessage' => 'The product categories table does not exist in the database.'
                ]);
            }

            $totalCategories = ProductCategory::count();
            $activeCategories = ProductCategory::where('active', true)->count();

            return view('admin.categories.index', [
                'tableExists' => true,
                'totalCategories' => $totalCategories,
                'activeCategories' => $activeCategories
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying categories: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'An error occurred while accessing categories.');
        }
    }

    /**
     * Get categories data for DataTables.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function data()
    {
        try {
            if (!auth()->guard('admin')->user()->can('view categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The product categories table does not exist in the database.'
                ], 500);
            }

            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $categories = ProductCategory::withCount('products')->with('parent')->get();
            } else {
                $categories = ProductCategory::with('parent')->get();
                $categories->each(function ($category) {
                    $category->products_count = 0;
                });
            }

            return DataTables::of($categories)
                ->addColumn('logo_image', function (ProductCategory $category) {
                    if ($category->logo) {
                        return '<img src="' . asset('storage/' . $category->logo) . '" alt="' . $category->name . '" class="category-logo-thumbnail" width="50">';
                    }
                    return '<span class="text-muted">No logo</span>';
                })
                ->addColumn('parent_name', function (ProductCategory $category) {
                    return $category->parent ? $category->parent->name : '-';
                })
                ->addColumn('action', function (ProductCategory $category) {
                    $actions = '';

                    if (Auth::guard('admin')->user()->can('view categories')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $category->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('edit categories')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $category->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }

                    if (Auth::guard('admin')->user()->can('delete categories')) {
                        $disabledClass = ($category->products_count > 0 || $category->children()->count() > 0) ? 'disabled' : '';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete ' . $disabledClass . '" data-id="' . $category->id . '" ' . $disabledClass . '>
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $actions;
                })
                ->rawColumns(['logo_image', 'action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting category data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve categories data: ' . $e->getMessage()
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
        $cleanName = ASCII::to_ascii($name);
        $slug = Str::slug($cleanName);

        $baseSlug = $slug;
        $counter = 1;

        while (ProductCategory::where('slug', $slug)
            ->where('id', '!=', $excludeId)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Store a newly created category.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->guard('admin')->user()->can('create categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to create categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The categories table does not exist in the database.'
                ], 500);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_categories',
                'code' => 'nullable|string|max:50|unique:product_categories',
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:500',
                'meta_title' => 'nullable|string|max:100',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'parent_id' => 'nullable|exists:product_categories,id',
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
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo')->store('category-logos', 'public');
            }

            if (!isset($data['active'])) {
                $data['active'] = true;
            }

            $category = ProductCategory::create($data);

            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($category)
                    ->withProperties(['category_id' => $category->id, 'category_name' => $category->name, 'slug' => $category->slug])
                    ->log('Created product category');
            }

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('view categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The categories table does not exist in the database.'
                ], 500);
            }

            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $category = ProductCategory::with(['products' => function ($query) {
                    $query->where('active', true)->limit(10);
                }, 'parent'])->withCount('products')->findOrFail($id);
            } else {
                $category = ProductCategory::with('parent')->findOrFail($id);
                $category->products = collect([]);
                $category->products_count = 0;
            }

            $category->logo_url = $category->logo ? asset('storage/' . $category->logo) : null;

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving category details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve category details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The categories table does not exist in the database.'
                ], 500);
            }

            $category = ProductCategory::with('parent')->findOrFail($id);
            $category->logo_url = $category->logo ? asset('storage/' . $category->logo) : null;

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving category for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve category for editing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('edit categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to edit categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The categories table does not exist in the database.'
                ], 500);
            }

            $category = ProductCategory::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_categories,name,' . $id,
                'code' => 'nullable|string|max:50|unique:product_categories,code,' . $id,
                'website' => 'nullable|url|max:255',
                'description' => 'nullable|string|max:500',
                'meta_title' => 'nullable|string|max:100',
                'meta_description' => 'nullable|string|max:255',
                'meta_keywords' => 'nullable|string|max:255',
                'parent_id' => 'nullable|exists:product_categories,id|not_in:' . $id,
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
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);

            if ($request->hasFile('logo')) {
                if ($category->logo) {
                    Storage::disk('public')->delete($category->logo);
                }
                $data['logo'] = $request->file('logo')->store('category-logos', 'public');
            }

            $oldValues = [
                'name' => $category->name,
                'slug' => $category->slug,
                'code' => $category->code,
                'active' => $category->active,
                'parent_id' => $category->parent_id
            ];

            $category->update($data);

            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($category)
                    ->withProperties([
                        'category_id' => $category->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $category->name,
                            'slug' => $category->slug,
                            'code' => $category->code,
                            'active' => $category->active,
                            'parent_id' => $category->parent_id
                        ]
                    ])
                    ->log('Updated product category');
            }

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update category: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            if (!auth()->guard('admin')->user()->can('delete categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to delete categories.'
                ], 403);
            }

            if (!Schema::hasTable('product_categories')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The categories table does not exist in the database.'
                ], 500);
            }

            $productsTableExists = Schema::hasTable('products');

            if ($productsTableExists) {
                $category = ProductCategory::withCount('products')->withCount('children')->findOrFail($id);

                if ($category->products_count > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot delete category with associated products. Please reassign products first.'
                    ], 422);
                }

                if ($category->children_count > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot delete category with subcategories. Please reassign subcategories first.'
                    ], 422);
                }
            } else {
                $category = ProductCategory::withCount('children')->findOrFail($id);

                if ($category->children_count > 0) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Cannot delete category with subcategories. Please reassign subcategories first.'
                    ], 422);
                }
            }

            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'code' => $category->code
            ];

            if ($category->logo) {
                Storage::disk('public')->delete($category->logo);
            }

            $category->delete();

            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->withProperties($categoryData)
                    ->log('Deleted product category');
            }

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete category: ' . $e->getMessage()
            ], 500);
        }
    }
}
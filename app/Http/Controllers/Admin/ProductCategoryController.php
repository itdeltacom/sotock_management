<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:view_categories', ['only' => ['index', 'show', 'data', 'getParentCategories']]);
        $this->middleware('permission:create_categories', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit_categories', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete_categories', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of categories.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            return view('admin.categories.index');
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
            $categories = ProductCategory::withCount('products')
                ->with('parent')
                ->get();
            
            return DataTables::of($categories)
                ->addColumn('parent_name', function (ProductCategory $category) {
                    return $category->parent ? $category->parent->name : '-';
                })
                ->addColumn('action', function (ProductCategory $category) {
                    $actions = '';
                    
                    // View button
                    if (Auth::guard('admin')->user()->can('view_categories')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-info btn-view me-1" data-id="' . $category->id . '">
                            <i class="fas fa-eye"></i>
                        </button> ';
                    }
                    
                    // Edit button
                    if (Auth::guard('admin')->user()->can('edit_categories')) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="' . $category->id . '">
                            <i class="fas fa-edit"></i>
                        </button> ';
                    }
                    
                    // Delete button - only if no products attached
                    if (Auth::guard('admin')->user()->can('delete_categories')) {
                        $disabledClass = $category->products_count > 0 ? 'disabled' : '';
                        $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete ' . $disabledClass . '" data-id="' . $category->id . '" ' . $disabledClass . '>
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (Exception $e) {
            Log::error('Error getting category data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve categories data.'
            ], 500);
        }
    }

    /**
     * Get parent categories for dropdown (excluding itself if editing).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getParentCategories(Request $request)
    {
        try {
            $query = ProductCategory::orderBy('name', 'asc');
            
            // If editing, exclude the current category and its descendants
            if ($request->has('exclude_id')) {
                $currentCategory = ProductCategory::find($request->exclude_id);
                if ($currentCategory) {
                    $descendantIds = $currentCategory->descendants()->pluck('id')->toArray();
                    $descendantIds[] = $currentCategory->id;
                    $query->whereNotIn('id', $descendantIds);
                }
            }
            
            $categories = $query->get(['id', 'name']);
            
            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Error getting parent categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve parent categories.'
            ], 500);
        }
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
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_categories',
                'code' => 'nullable|string|max:50|unique:product_categories',
                'description' => 'nullable|string|max:500',
                'parent_id' => 'nullable|exists:product_categories,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check for circular reference in parent-child relationship
            if ($request->parent_id) {
                $parent = ProductCategory::find($request->parent_id);
                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['parent_id' => ['Selected parent category does not exist.']]
                    ], 422);
                }
            }
            
            // Create the category
            $category = ProductCategory::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'parent_id' => $request->parent_id ?: null,
            ]);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($category)
                    ->withProperties(['category_id' => $category->id, 'category_name' => $category->name])
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
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $category = ProductCategory::with(['parent', 'products' => function($query) {
                $query->where('active', true)->limit(10);
            }])->withCount('products')->findOrFail($id);
            
            // Get ancestors and descendants
            $ancestors = $category->ancestors()->get();
            $descendants = $category->descendants()->get();
            
            return response()->json([
                'success' => true,
                'category' => $category,
                'ancestors' => $ancestors,
                'descendants' => $descendants
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving category details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve category details.'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving category for edit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve category for editing.'
            ], 500);
        }
    }

    /**
     * Update the specified category.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100|unique:product_categories,name,' . $id,
                'code' => 'nullable|string|max:50|unique:product_categories,code,' . $id,
                'description' => 'nullable|string|max:500',
                'parent_id' => 'nullable|exists:product_categories,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Prevent setting itself as parent
            if ($request->parent_id == $id) {
                return response()->json([
                    'success' => false,
                    'errors' => ['parent_id' => ['A category cannot be its own parent.']]
                ], 422);
            }
            
            // Prevent setting a descendant as parent (circular reference)
            if ($request->parent_id) {
                $descendants = $category->descendants()->pluck('id')->toArray();
                if (in_array($request->parent_id, $descendants)) {
                    return response()->json([
                        'success' => false,
                        'errors' => ['parent_id' => ['Cannot set a descendant category as parent.']]
                    ], 422);
                }
            }
            
            // Store old values for logging
            $oldValues = [
                'name' => $category->name,
                'code' => $category->code,
                'parent_id' => $category->parent_id
            ];
            
            // Update the category
            $category->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'parent_id' => $request->parent_id ?: null,
            ]);
            
            // Log activity
            if (method_exists(app(), 'activity')) {
                activity()
                    ->causedBy(Auth::guard('admin')->user())
                    ->performedOn($category)
                    ->withProperties([
                        'category_id' => $category->id,
                        'old_values' => $oldValues,
                        'new_values' => [
                            'name' => $category->name,
                            'code' => $category->code,
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
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $category = ProductCategory::withCount('products')->findOrFail($id);
            
            // Check if category has products
            if ($category->products_count > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete category with associated products. Please remove products first.'
                ], 422);
            }
            
            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete category with sub-categories. Please remove child categories first.'
                ], 422);
            }
            
            // Store category data for logging
            $categoryData = [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code
            ];
            
            // Delete the category
            $category->delete();
            
            // Log activity
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
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the blog categories.
     */
    public function index()
    {
        return view('admin.blogs.categories');
    }

   /**
 * Get blog categories data for DataTables.
 */
public function data()
{
    // Load parent relationship and count posts
    $categories = BlogCategory::with('parent')
        ->withCount('posts')
        ->get();
    
    return DataTables::of($categories)
        ->addColumn('action', function (BlogCategory $category) {
            $actions = '';
            
            // Only show edit button if user has permission
            if (Auth::guard('admin')->user()->can('edit blog categories')) {
                $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$category->id.'">
                    <i class="fas fa-edit"></i>
                </button> ';
            }
            
            // Only show delete button if user has permission
            if (Auth::guard('admin')->user()->can('delete blog categories')) {
                $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$category->id.'" data-name="'.$category->name.'">
                    <i class="fas fa-trash"></i>
                </button>';
            }
            
            return $actions;
        })
        ->addColumn('status', function (BlogCategory $category) {
            return $category->is_active 
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>';
        })
        ->rawColumns(['action', 'status'])
        ->make(true);
}

    /**
     * Show the specified blog category.
     */
    public function show(BlogCategory $category)
    {
        return response()->json([
            'success' => true,
            'category' => $category->load('parent', 'children')
        ]);
    }

    /**
     * Store a newly created blog category in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        // Generate slug from name
        $data['slug'] = Str::slug($request->name);
        
        // Create the category
        $category = BlogCategory::create($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($category)
                ->withProperties(['category_name' => $category->name])
                ->log('Created blog category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog category created successfully.',
            'category' => $category
        ]);
    }

    /**
     * Show the form for editing the specified blog category.
     */
    public function edit(BlogCategory $category)
    {
        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    /**
     * Update the specified blog category in storage.
     */
    public function update(Request $request, BlogCategory $category)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $category->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|exists:blog_categories,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if parent ID is not the category itself or one of its children
        if ($request->has('parent_id') && $request->parent_id) {
            if ($request->parent_id == $category->id) {
                return response()->json([
                    'success' => false,
                    'errors' => ['parent_id' => ['A category cannot be its own parent.']]
                ], 422);
            }
            
            // Check if the parent is not a child of the category
            $childrenIds = $this->getAllChildrenIds($category);
            if (in_array($request->parent_id, $childrenIds)) {
                return response()->json([
                    'success' => false,
                    'errors' => ['parent_id' => ['Cannot set a child category as parent.']]
                ], 422);
            }
        }

        $data = $request->all();
        
        // Update slug if name has changed
        if ($request->name !== $category->name) {
            $data['slug'] = Str::slug($request->name);
        }
        
        // Update the category
        $category->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($category)
                ->withProperties(['category_name' => $category->name])
                ->log('Updated blog category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog category updated successfully.',
            'category' => $category
        ]);
    }
    
    /**
     * Remove the specified blog category from storage.
     */
    public function destroy(BlogCategory $category)
    {
        // Check if category has posts
        if ($category->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated posts. Reassign posts to another category first.'
            ], 400);
        }
        
        // Check if category has children
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with sub-categories. Delete or reassign sub-categories first.'
            ], 400);
        }
        
        // Store data for activity log
        $categoryData = [
            'id' => $category->id,
            'name' => $category->name
        ];
        
        $category->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($categoryData)
                ->log('Deleted blog category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog category deleted successfully.'
        ]);
    }
    
    /**
     * Get categories for dropdown.
     */
    public function getCategories()
    {
        $categories = BlogCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);
        
        return response()->json([
            'success' => true,
            'categories' => $categories
        ]);
    }

    /**
     * Validate a single field.
     */
    public function validateField(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $categoryId = $request->category_id;
        
        $rules = [];
        
        switch ($field) {
            case 'name':
                $rules[$field] = 'required|string|max:255';
                if (!$categoryId) {
                    $rules[$field] .= '|unique:blog_categories,name';
                } else {
                    $rules[$field] .= '|unique:blog_categories,name,' . $categoryId;
                }
                break;
            case 'parent_id':
                $rules[$field] = 'nullable|exists:blog_categories,id';
                
                // Check for circular reference
                if ($categoryId && $value && $value == $categoryId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A category cannot be its own parent.'
                    ]);
                }
                
                // Check for child as parent
                if ($categoryId && $value) {
                    $category = BlogCategory::find($categoryId);
                    if ($category) {
                        $childrenIds = $this->getAllChildrenIds($category);
                        if (in_array($value, $childrenIds)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Cannot set a child category as parent.'
                            ]);
                        }
                    }
                }
                break;
            case 'description':
                $rules[$field] = 'nullable|string';
                break;
            case 'meta_title':
                $rules[$field] = 'nullable|string|max:255';
                break;
            case 'meta_description':
                $rules[$field] = 'nullable|string';
                break;
            case 'meta_keywords':
                $rules[$field] = 'nullable|string|max:255';
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Unknown field: ' . $field
                ]);
        }
        
        $validator = Validator::make([$field => $value], $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first($field)
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Field validation passed'
        ]);
    }
    
    /**
     * Get all children IDs for a category
     */
    private function getAllChildrenIds(BlogCategory $category)
    {
        $ids = [];
        $children = $category->children()->get();
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllChildrenIds($child));
        }
        
        return $ids;
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        return view('admin.cars.categories');
    }

    /**
     * Get category data for DataTables.
     */
    public function data()
    {
        $categories = Category::withCount('cars')->get();
        
        return DataTables::of($categories)
            ->addColumn('action', function (Category $category) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit categories')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$category->id.'">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete categories')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$category->id.'" data-name="'.$category->name.'">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (Category $category) {
                return $category->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('image', function (Category $category) {
                if ($category->image) {
                    return '<img src="'.Storage::url($category->image).'" alt="'.$category->name.'" width="50" class="img-thumbnail">';
                }
                
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->rawColumns(['action', 'status', 'image'])
            ->make(true);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('create categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create categories.'
            ], 403);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['image']);
        $data['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }
        
        Category::create($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties(['category_name' => $data['name']])
                ->log('Created category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.'
        ]);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category)
    {
        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('edit categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit categories.'
            ], 403);
        }
        
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->except(['image', '_method']);
        $data['slug'] = Str::slug($request->name);
        
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            
            $path = $request->file('image')->store('categories', 'public');
            $data['image'] = $path;
        }
        
        $category->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($category)
                ->withProperties(['category_name' => $category->name])
                ->log('Updated category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.'
        ]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check permission
        if (!Auth::guard('admin')->user()->can('delete categories')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete categories.'
            ], 403);
        }
        
        // Check if category has associated cars
        if ($category->cars()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated cars.'
            ], 400);
        }
        
        // Store data for activity log
        $categoryData = [
            'id' => $category->id,
            'name' => $category->name
        ];
        
        // Delete image if exists
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        
        $category->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($categoryData)
                ->log('Deleted category');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.'
        ]);
    }
}
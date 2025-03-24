<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BlogTagController extends Controller
{
    /**
     * Display a listing of the blog tags.
     */
    public function index()
    {
        return view('admin.blogs.tags');
    }

    /**
     * Get blog tags data for DataTables.
     */
    public function data()
    {
        $tags = BlogTag::withCount('posts')->get();
        
        return DataTables::of($tags)
            ->addColumn('action', function (BlogTag $tag) {
                $actions = '';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit blog tags')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary btn-edit me-1" data-id="'.$tag->id.'">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete blog tags')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$tag->id.'" data-name="'.$tag->name.'">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (BlogTag $tag) {
                return $tag->is_active 
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('post_count', function (BlogTag $tag) {
                return $tag->posts_count;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    /**
     * Show the specified blog tag.
     */
    public function show(BlogTag $tag)
    {
        return response()->json([
            'success' => true,
            'tag' => $tag
        ]);
    }

    /**
     * Store a newly created blog tag in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:blog_tags,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
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
        
        // Create the tag
        $tag = BlogTag::create($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($tag)
                ->withProperties(['tag_name' => $tag->name])
                ->log('Created blog tag');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog tag created successfully.',
            'tag' => $tag
        ]);
    }

    /**
     * Show the form for editing the specified blog tag.
     */
    public function edit(BlogTag $tag)
    {
        return response()->json([
            'success' => true,
            'tag' => $tag
        ]);
    }

    /**
     * Update the specified blog tag in storage.
     */
    public function update(Request $request, BlogTag $tag)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:blog_tags,name,' . $tag->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
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
        
        // Update slug if name has changed
        if ($request->name !== $tag->name) {
            $data['slug'] = Str::slug($request->name);
        }
        
        // Update the tag
        $tag->update($data);
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($tag)
                ->withProperties(['tag_name' => $tag->name])
                ->log('Updated blog tag');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog tag updated successfully.',
            'tag' => $tag
        ]);
    }
    
    /**
     * Remove the specified blog tag from storage.
     */
    public function destroy(BlogTag $tag)
    {
        // Check if tag has posts
        if ($tag->posts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tag with associated posts. Remove the tag from all posts first.'
            ], 400);
        }
        
        // Store data for activity log
        $tagData = [
            'id' => $tag->id,
            'name' => $tag->name
        ];
        
        $tag->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($tagData)
                ->log('Deleted blog tag');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog tag deleted successfully.'
        ]);
    }
    
    /**
     * Get active tags for dropdown.
     */
    public function getTags()
    {
        $tags = BlogTag::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'tags' => $tags
        ]);
    }

    /**
     * Validate a single field.
     */
    public function validateField(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $tagId = $request->tag_id;
        
        $rules = [];
        
        switch ($field) {
            case 'name':
                $rules[$field] = 'required|string|max:255';
                if (!$tagId) {
                    $rules[$field] .= '|unique:blog_tags,name';
                } else {
                    $rules[$field] .= '|unique:blog_tags,name,' . $tagId;
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
}
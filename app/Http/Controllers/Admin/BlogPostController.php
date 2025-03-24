<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BlogPostController extends Controller
{
    /**
     * Display a listing of the blog posts.
     */
    public function index()
    {
        // Load categories and tags for the create/edit modal
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        $tags = BlogTag::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.blogs.index', compact('categories', 'tags'));
    }

    /**
     * Get blog posts data for DataTables.
     */
    public function data(Request $request)
    {
        $query = BlogPost::with(['category', 'author', 'tags']);
        
        // Apply filters if provided
        if ($request->has('status') && !empty($request->status)) {
            if ($request->status === 'published') {
                $query->where('is_published', true)->where('published_at', '<=', now());
            } elseif ($request->status === 'scheduled') {
                $query->where('is_published', true)->where('published_at', '>', now());
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }
        
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('is_featured') && $request->is_featured === '1') {
            $query->where('is_featured', true);
        }
        
        $posts = $query->get();
        
        return DataTables::of($posts)
            ->addColumn('action', function (BlogPost $post) {
                $actions = '';
                
                // Preview button
                $actions .= '<a href="' . route('blog.show', $post->slug) . '" target="_blank" class="btn btn-sm btn-info me-1" title="Preview">
                    <i class="fas fa-eye"></i>
                </a> ';
                
                // Only show edit button if user has permission
                if (Auth::guard('admin')->user()->can('edit blog posts')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary me-1 btn-edit" data-id="'.$post->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button> ';
                }
                
                // Feature/unfeature button if user has permission
                if (Auth::guard('admin')->user()->can('edit blog posts')) {
                    $featured = $post->is_featured ? '1' : '0';
                    $featureIcon = $post->is_featured ? 
                        '<i class="fas fa-star text-warning"></i>' : 
                        '<i class="far fa-star"></i>';
                    $featureTitle = $post->is_featured ? 'Remove from featured' : 'Add to featured';
                    
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-warning me-1 btn-feature" data-id="'.$post->id.'" data-featured="'.$featured.'" title="'.$featureTitle.'">
                        '.$featureIcon.'
                    </button> ';
                }
                
                // Publish/unpublish button if user has permission
                if (Auth::guard('admin')->user()->can('edit blog posts')) {
                    $published = $post->is_published ? '1' : '0';
                    $publishIcon = $post->is_published ? 
                        '<i class="fas fa-eye"></i>' : 
                        '<i class="fas fa-eye-slash"></i>';
                    $publishTitle = $post->is_published ? 'Unpublish' : 'Publish';
                    
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-success me-1 btn-publish" data-id="'.$post->id.'" data-published="'.$published.'" title="'.$publishTitle.'">
                        '.$publishIcon.'
                    </button> ';
                }
                
                // Only show delete button if user has permission
                if (Auth::guard('admin')->user()->can('delete blog posts')) {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$post->id.'" data-title="'.$post->title.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }
                
                return $actions;
            })
            ->addColumn('status', function (BlogPost $post) {
                if (!$post->is_published) {
                    return '<span class="badge bg-secondary">Draft</span>';
                } elseif ($post->published_at > now()) {
                    return '<span class="badge bg-info">Scheduled</span>';
                } else {
                    return '<span class="badge bg-success">Published</span>';
                }
            })
            ->addColumn('category_name', function (BlogPost $post) {
                return $post->category ? $post->category->name : '-';
            })
            ->addColumn('author_name', function (BlogPost $post) {
                return $post->author ? $post->author->name : '-';
            })
            ->addColumn('tags_list', function (BlogPost $post) {
                $tags = $post->tags->map(function ($tag) {
                    return '<span class="badge bg-primary me-1">' . $tag->name . '</span>';
                })->join(' ');
                
                return $tags ?: '-';
            })
            ->addColumn('published_date', function (BlogPost $post) {
                if (!$post->is_published) {
                    return '-';
                } elseif ($post->published_at > now()) {
                    return '<span class="text-info">' . $post->published_at->format('M d, Y H:i') . '</span>';
                } else {
                    return $post->published_at->format('M d, Y H:i');
                }
            })
            ->addColumn('image', function (BlogPost $post) {
                if ($post->featured_image) {
                    return '<img src="' . Storage::url($post->featured_image) . '" alt="' . $post->title . '" width="80" class="img-thumbnail">';
                }
                
                return '<span class="badge bg-secondary">No Image</span>';
            })
            ->addColumn('comment_count', function (BlogPost $post) {
                return $post->comments()->count();
            })
            ->rawColumns(['action', 'status', 'tags_list', 'published_date', 'image'])
            ->make(true);
    }

    /**
     * Store a newly created blog post in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'category_id' => 'required|exists:blog_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_published' => 'required|in:true,false',
            'is_featured' => 'required|in:true,false',
            'allow_comments' => 'required|in:true,false',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'facebook_description' => 'nullable|string|max:500',
            'twitter_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);
        
        if ($validator->fails()) {
            // For AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For traditional form submissions
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['featured_image', 'social_image', 'tags', '_token']);
        
        // Convert string boolean values to actual booleans
        $data['is_published'] = $request->is_published === 'true' ? true : false;
        $data['is_featured'] = $request->is_featured === 'true' ? true : false;
        $data['allow_comments'] = $request->allow_comments === 'true' ? true : false;
        
        // Set author ID
        $data['author_id'] = Auth::guard('admin')->id();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
            
            // Ensure the slug is unique
            $originalSlug = $data['slug'];
            $count = 1;
            
            while (BlogPost::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }
        
        // Set published_at if not provided but is_published is true
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blogs/featured', 'public');
            $data['featured_image'] = $path;
        }
        
        // Handle social image upload
        if ($request->hasFile('social_image')) {
            $path = $request->file('social_image')->store('blogs/social', 'public');
            $data['social_image'] = $path;
        }
        
        // Create the post
        $post = BlogPost::create($data);
        
        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($post)
                ->withProperties(['post_title' => $post->title])
                ->log('Created blog post');
        }
        
        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog post created successfully.',
                'post' => $post
            ]);
        }
        
        // For traditional form submissions
        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    // ... (keep existing methods)

    /**
     * Update the specified blog post in storage.
     */
    public function update(Request $request, BlogPost $post)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'category_id' => 'required|exists:blog_categories,id',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'social_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_published' => 'required|in:true,false',
            'is_featured' => 'required|in:true,false',
            'allow_comments' => 'required|in:true,false',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:255',
            'facebook_description' => 'nullable|string|max:500',
            'twitter_description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
        ]);
        
        if ($validator->fails()) {
            // For AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // For traditional form submissions
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->except(['featured_image', 'social_image', 'tags', '_token', '_method']);
        
        // Convert string boolean values to actual booleans
        $data['is_published'] = $request->is_published === 'true' ? true : false;
        $data['is_featured'] = $request->is_featured === 'true' ? true : false;
        $data['allow_comments'] = $request->allow_comments === 'true' ? true : false;
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
            
            // Ensure the slug is unique
            $originalSlug = $data['slug'];
            $count = 1;
            
            while (BlogPost::where('slug', $data['slug'])->where('id', '!=', $post->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }
        
        // Set published_at if status changed to published
        if ($data['is_published'] && !$post->is_published && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            
            $path = $request->file('featured_image')->store('blogs/featured', 'public');
            $data['featured_image'] = $path;
        }
        
        // Handle social image upload
        if ($request->hasFile('social_image')) {
            // Delete old image if exists
            if ($post->social_image) {
                Storage::disk('public')->delete($post->social_image);
            }
            
            $path = $request->file('social_image')->store('blogs/social', 'public');
            $data['social_image'] = $path;
        }
        
        // Update the post
        $post->update($data);
        
        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        } else {
            $post->tags()->detach();
        }
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->performedOn($post)
                ->withProperties(['post_title' => $post->title])
                ->log('Updated blog post');
        }
        
        // For AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blog post updated successfully.',
                'post' => $post
            ]);
        }
        
        // For traditional form submissions
        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Display the specified blog post.
     */
    public function show(BlogPost $post)
    {
        $post->load(['category', 'author', 'tags']);
        
        // Add image URLs for frontend display
        if ($post->featured_image) {
            $post->featured_image_url = Storage::url($post->featured_image);
        }
        
        if ($post->social_image) {
            $post->social_image_url = Storage::url($post->social_image);
        }
        
        return response()->json([
            'success' => true,
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(BlogPost $post)
    {
        $post->load(['category', 'author', 'tags']);
        
        // For AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            // Add image URLs for frontend display
            if ($post->featured_image) {
                $post->featured_image_url = Storage::url($post->featured_image);
            }
            
            if ($post->social_image) {
                $post->social_image_url = Storage::url($post->social_image);
            }
            
            return response()->json([
                'success' => true,
                'post' => $post
            ]);
        }
        
        // For traditional form submissions
        $categories = BlogCategory::where('is_active', true)->orderBy('name')->get();
        $tags = BlogTag::where('is_active', true)->orderBy('name')->get();
        $selectedTags = $post->tags->pluck('id')->toArray();
        
        return view('admin.blogs.posts.edit', compact('post', 'categories', 'tags', 'selectedTags'));
    }

        
    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(BlogPost $post)
    {
        // Store data for activity log
        $postData = [
            'id' => $post->id,
            'title' => $post->title
        ];
        
        // Delete featured image if exists
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        
        // Delete social image if exists
        if ($post->social_image) {
            Storage::disk('public')->delete($post->social_image);
        }
        
        // Delete all associated comments
        $post->comments()->delete();
        
        // Delete tags association
        $post->tags()->detach();
        
        // Delete the post
        $post->delete();
        
        // Log activity if spatie activity-log package is installed
        if (method_exists(app(), 'activity')) {
            activity()
                ->causedBy(Auth::guard('admin')->user())
                ->withProperties($postData)
                ->log('Deleted blog post');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully.'
        ]);
    }
    
    /**
     * Toggle the featured status of a post.
     */
    public function toggleFeatured(BlogPost $post)
    {
        $post->is_featured = !$post->is_featured;
        $post->save();
        
        return response()->json([
            'success' => true,
            'message' => $post->is_featured ? 'Post set as featured.' : 'Post removed from featured.',
            'is_featured' => $post->is_featured
        ]);
    }
    
    /**
     * Toggle the published status of a post.
     */
    public function togglePublished(BlogPost $post)
    {
        $post->is_published = !$post->is_published;
        
        if ($post->is_published && !$post->published_at) {
            $post->published_at = now();
        }
        
        $post->save();
        
        return response()->json([
            'success' => true,
            'message' => $post->is_published ? 'Post published successfully.' : 'Post unpublished successfully.',
            'is_published' => $post->is_published,
            'published_at' => $post->published_at ? $post->published_at->format('M d, Y H:i') : null
        ]);
    }
    
    /**
     * Upload image for the post content editor.
     * This works for CKEditor 5's simpleUpload adapter.
     */
    public function uploadImage(Request $request)
    {
        // Validate the image
        $validator = Validator::make($request->all(), [
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message' => $validator->errors()->first('upload')
                ]
            ], 422);
        }
        
        try {
            $image = $request->file('upload');
            $path = $image->store('blogs/content', 'public');
            $url = Storage::url($path);
            
            // Format for CKEditor 5
            return response()->json([
                'url' => $url,
                'uploaded' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => 'Image upload failed: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
    
    /**
     * Validate the slug for uniqueness.
     */
    public function validateSlug(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|max:255',
            'post_id' => 'nullable|integer|exists:blog_posts,id',
        ]);
        
        $postId = $request->post_id;
        $slug = $request->slug;
        
        $query = BlogPost::where('slug', $slug);
        
        if ($postId) {
            $query->where('id', '!=', $postId);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? 'This slug is already in use. Please choose another.' : 'Slug is available.'
        ]);
    }
    
    /**
     * Generate a slug from the title.
     */
    public function generateSlug(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);
        
        $title = $request->title;
        $slug = Str::slug($title);
        
        // Ensure the slug is unique
        $originalSlug = $slug;
        $count = 1;
        
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return response()->json([
            'slug' => $slug
        ]);
    }
    
    /**
     * Validate a specific field.
     */
    public function validateField(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $postId = $request->post_id;
        
        $rules = [];
        $messages = [];
        
        switch ($field) {
            case 'title':
                $rules[$field] = 'required|string|max:255';
                break;
            case 'slug':
                $rules[$field] = 'nullable|string|max:255|unique:blog_posts,slug' . ($postId ? ','.$postId : '');
                $messages = [
                    'slug.unique' => 'This slug is already in use. Please choose another.'
                ];
                break;
            case 'category_id':
                $rules[$field] = 'required|exists:blog_categories,id';
                break;
            case 'content':
                $rules[$field] = 'required|string';
                break;
            case 'excerpt':
                $rules[$field] = 'nullable|string|max:500';
                break;
            default:
                return response()->json([
                    'valid' => true
                ]);
        }
        
        $validator = Validator::make([$field => $value], $rules, $messages);
        
        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first($field)
            ]);
        }
        
        return response()->json([
            'valid' => true
        ]);
    }
}
<?php

namespace App\Http\Controllers\Front;

use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * Display the blog index page.
     */
    public function index()
    {
        $posts = BlogPost::published()->recent()->paginate(3);
        $categories = BlogCategory::where('is_active', true)->get();
        $recentPosts = BlogPost::published()->recent()->limit(5)->get();
        
        return view('site.blogs.index', compact('posts', 'categories', 'recentPosts'));
    }
    
    /**
     * Load more posts via AJAX.
     */
    public function loadMorePosts(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            
            // Make sure we have the correct model and method
            $posts = BlogPost::with(['author', 'category'])
                ->where('is_published', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->paginate(3, ['*'], 'page', $page);
            
            // Add comment counts if needed
            foreach ($posts as $post) {
                // Check if comments method exists before counting
                if (method_exists($post, 'comments')) {
                    $post->comments_count = $post->comments()->count();
                } else {
                    $post->comments_count = 0;
                }
            }
            
            // Check if the partial view exists
            if (!View::exists('site.blogs.partials.post-items')) {
                // Create a temporary view file if it doesn't exist
                $viewContent = view('site.blogs.post-items-temp', compact('posts'))->render();
                return response()->json([
                    'html' => $viewContent,
                    'hasMorePages' => $posts->hasMorePages(),
                    'count' => $posts->count()
                ]);
            }
            
            return response()->json([
                'html' => view('site.blogs.post-items-temp', compact('posts'))->render(),
                'hasMorePages' => $posts->hasMorePages(),
                'count' => $posts->count()
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error in loadMorePosts: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Return a proper error response
            return response()->json([
                'error' => 'Failed to load posts: ' . $e->getMessage()
            ], 500);
        }
    }
    
   /**
 * Display a single blog post.
 */
public function show($slug)
{
    $post = BlogPost::where('slug', $slug)
        ->where(function($query) {
            // Published posts are public
            $query->where('is_published', true)
                ->where('published_at', '<=', now());
            
            // Admin users can preview posts
            if (auth()->guard('admin')->check()) {
                $query->orWhere('id', '>', 0); 
            }
        })
        ->firstOrFail();
    
    // Increment view count
    if (method_exists($post, 'incrementViewCount')) {
        $post->incrementViewCount();
    }

    // Get comments for this post
    $comments = $post->comments()
        ->with(['user'])
        ->approved()
        ->orderBy('created_at', 'desc')
        ->get();
    
    // Count total comments (for pagination if needed)
    $totalComments = $comments->count();
    
    // Get related posts    
    $relatedPosts = method_exists($post, 'getRelatedPostsAttribute') 
        ? $post->relatedPosts 
        : BlogPost::where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();
            
    // Get categories for sidebar
    $categories = BlogCategory::where('is_active', true)->get();
    
    // Get recent posts for sidebar
    $recentPosts = BlogPost::published()->recent()->limit(5)->get();
    
    // Get popular tags for sidebar
    $popularTags = BlogTag::where('is_active', true)
        ->withCount(['posts' => function($query) {
            $query->where('is_published', true)
                ->where('published_at', '<=', now());
        }])
        ->orderBy('posts_count', 'desc')
        ->limit(10)
        ->get();
    
    return view('site.blogs.post', compact(
        'post', 
        'comments',
        'totalComments',
        'relatedPosts', 
        'categories', 
        'recentPosts',
        'popularTags'
    ));
}
    
    /**
     * Display posts by category.
     */
    public function category($slug)
    {
        $category = BlogCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $posts = BlogPost::where('category_id', $category->id)
            ->published()
            ->recent()
            ->paginate(12);
            
        $categories = BlogCategory::where('is_active', true)->get();
        $recentPosts = BlogPost::published()->recent()->limit(5)->get();
        
        return view('site.blogs.category', compact('category', 'posts', 'categories', 'recentPosts'));
    }
    
    /**
     * Display posts by tag.
     */
    public function tag($slug)
    {
        $tag = BlogTag::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $posts = $tag->posts()
            ->published()
            ->recent()
            ->paginate(12);
            
        $categories = BlogCategory::where('is_active', true)->get();
        $recentPosts = BlogPost::published()->recent()->limit(5)->get();
        
        return view('site.blogs.tag', compact('tag', 'posts', 'categories', 'recentPosts'));
    }
}
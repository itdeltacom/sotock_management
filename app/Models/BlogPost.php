<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BlogPost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'author_id',
        'featured_image',
        'is_published',
        'published_at',
        'is_featured',
        'allow_comments',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'read_time',
        'view_count',
        'canonical_url',
        'structured_data',
        'social_image',
        'facebook_description',
        'twitter_description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'published_at' => 'datetime',
        'read_time' => 'integer',
        'view_count' => 'integer',
        'structured_data' => 'array',
    ];

    /**
     * Get the category that the post belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /**
     * Get all tags associated with the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags', 'post_id', 'tag_id')
            ->withTimestamps();
    }

    /**
     * Get all comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'post_id')->whereNull('parent_id');
    }

    /**
     * Get all replies to comments for the post.
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'post_id');
    }

    /**
     * Get related posts based on category and tags.
     */
    public function getRelatedPostsAttribute()
    {
        // Get posts in the same category
        $categoryPosts = self::where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->limit(3)
            ->get();
        
        // If we have 3 posts from the same category, return those
        if ($categoryPosts->count() >= 3) {
            return $categoryPosts;
        }
        
        // Get the IDs of posts we already have
        $existingIds = $categoryPosts->pluck('id')->toArray();
        $existingIds[] = $this->id;
        
        // Get tag IDs for this post
        $tagIds = $this->tags->pluck('id')->toArray();
        
        // Get posts with the same tags
        $tagPosts = self::whereHas('tags', function($query) use ($tagIds) {
                $query->whereIn('blog_tags.id', $tagIds);
            })
            ->whereNotIn('id', $existingIds)
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->limit(3 - $categoryPosts->count())
            ->get();
        
        // Combine category and tag posts
        $relatedPosts = $categoryPosts->merge($tagPosts);
        
        // If we still don't have 3 posts, get recent posts
        if ($relatedPosts->count() < 3) {
            $existingIds = $relatedPosts->pluck('id')->toArray();
            $existingIds[] = $this->id;
            
            $recentPosts = self::whereNotIn('id', $existingIds)
                ->where('is_published', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->limit(3 - $relatedPosts->count())
                ->get();
            
            $relatedPosts = $relatedPosts->merge($recentPosts);
        }
        
        return $relatedPosts;
    }
    
    /**
     * Get the URL for the post.
     */
    public function getUrlAttribute()
    {
        return route('blog.show', $this->slug);
    }
    
    /**
     * Get the full URL for the featured image.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if (!$this->featured_image) {
            return asset('images/blog/default.jpg');
        }
        
        return Storage::url($this->featured_image);
    }
    
    /**
     * Get the social sharing image URL.
     */
    public function getSocialImageUrlAttribute()
    {
        if ($this->social_image) {
            return Storage::url($this->social_image);
        }
        
        return $this->featured_image_url;
    }
    
    /**
     * Get the estimated reading time for the post.
     */
    public function calculateReadTime()
    {
        // Average reading speed is about 200-250 words per minute
        $wordsPerMinute = 225;
        $wordCount = str_word_count(strip_tags($this->content));
        $readTime = ceil($wordCount / $wordsPerMinute);
        
        // Minimum read time is 1 minute
        return max(1, $readTime);
    }
    
    /**
     * Increment view count.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
    
    /**
     * Generate JSON-LD structured data.
     */
   /**
 * Generate JSON-LD structured data.
 */
public function generateStructuredData()
{
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $this->title,
        'image' => [$this->featured_image_url],
        'dateModified' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        'author' => [
            '@type' => 'Person',
            'name' => $this->author->name ?? 'Cental Staff'
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Cental',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('site/img/logo.png')
            ]
        ],
        'description' => $this->meta_description ?? $this->excerpt,
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $this->url
        ]
    ];
    
    // Only add datePublished if it exists
    if ($this->published_at) {
        $structuredData['datePublished'] = $this->published_at->toIso8601String();
    }
    
    return $structuredData;
}
    
    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }
    
    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    /**
     * Scope a query to order by most recent.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }
    
    /**
     * Scope a query to order by popularity (view count).
     */
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }
    
    /**
     * Generate excerpt from content if not provided.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($post) {
            // Generate slug from title if not set
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
            
            // Generate excerpt from content if not set
            if (!$post->excerpt) {
                $post->excerpt = Str::limit(strip_tags($post->content), 160);
            }
            
            // Calculate read time if not set
            if (!$post->read_time) {
                $post->read_time = $post->calculateReadTime();
            }
            
            // Set meta title if not set
            if (!$post->meta_title) {
                $post->meta_title = $post->title . ' | Cental Blog';
            }
            
            // Set meta description if not set
            if (!$post->meta_description) {
                $post->meta_description = $post->excerpt;
            }
            
            // Generate structured data
            if (!$post->structured_data) {
                $post->structured_data = $post->generateStructuredData();
            }
        });
        
        static::updating(function ($post) {
            // Update slug if title changed and slug matches old title
            if ($post->isDirty('title') && $post->slug == Str::slug($post->getOriginal('title'))) {
                $post->slug = Str::slug($post->title);
            }
            
            // Recalculate read time if content changed
            if ($post->isDirty('content')) {
                $post->read_time = $post->calculateReadTime();
                
                // Update excerpt if it was auto-generated
                if (!$post->excerpt || $post->excerpt == Str::limit(strip_tags($post->getOriginal('content')), 160)) {
                    $post->excerpt = Str::limit(strip_tags($post->content), 160);
                }
            }
            
            // Update structured data
            $post->structured_data = $post->generateStructuredData();
        });
    }
}
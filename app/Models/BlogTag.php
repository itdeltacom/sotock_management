<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all posts associated with the tag.
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_tags', 'tag_id', 'post_id')
            ->withTimestamps();
    }

    /**
     * Get all published posts associated with the tag.
     */
    public function publishedPosts(): BelongsToMany
    {
        return $this->posts()
            ->where('is_published', true)
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    /**
     * Get the URL for the tag page.
     */
    public function getUrlAttribute()
    {
        return route('blog.tag', $this->slug);
    }

    /**
     * Get the number of published posts for this tag.
     */
    public function getPostCountAttribute()
    {
        return $this->publishedPosts()->count();
    }

    /**
     * Scope a query to only include active tags.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include tags with published posts.
     */
    public function scopeWithPublishedPosts($query)
    {
        return $query->whereHas('posts', function ($query) {
            $query->where('is_published', true)
                ->where('published_at', '<=', now());
        });
    }

    /**
     * Scope a query to order by name.
     */
    public function scopeAlphabetical($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Scope a query to order by popularity (post count).
     */
    public function scopePopular($query)
    {
        return $query->withCount(['posts' => function ($query) {
            $query->where('is_published', true)
                ->where('published_at', '<=', now());
        }])->orderBy('posts_count', 'desc');
    }

    /**
     * Generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($tag) {
            // Generate slug from name if not set
            if (!$tag->slug) {
                $tag->slug = Str::slug($tag->name);
            }
            
            // Set meta title if not set
            if (!$tag->meta_title) {
                $tag->meta_title = $tag->name . ' | Cental Blog';
            }
            
            // Set meta description if not set
            if (!$tag->meta_description && $tag->description) {
                $tag->meta_description = Str::limit(strip_tags($tag->description), 160);
            }
        });
        
        static::updating(function ($tag) {
            // Update slug if name changed and slug matches old name
            if ($tag->isDirty('name') && $tag->slug == Str::slug($tag->getOriginal('name'))) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }
}
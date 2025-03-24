@extends('site.layouts.app')

@section('title', $post->meta_title ?? $post->title . ' | Cental Car Rental Blog')
@section('meta_description', $post->meta_description ?? Str::limit(strip_tags($post->content), 160))
@section('meta_keywords', $post->meta_keywords ?? 'car rental, blog, ' . $post->category->name)

@section('og_title', $post->meta_title ?? $post->title)
@section('og_description', $post->meta_description ?? Str::limit(strip_tags($post->content), 200))
@section('og_image', $post->social_image ? Storage::url($post->social_image) : ($post->featured_image ? Storage::url($post->featured_image) : asset('site/img/blog-header.jpg')))

@section('canonical_url', $post->canonical_url ?? route('blog.show', $post->slug))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    <style>
        /* Blog Post Styling */
        .blog-detail-img {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .blog-meta {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e9e9e9;
        }

        .blog-meta span {
            margin-right: 1.5rem;
            color: #777;
            font-size: 0.9rem;
        }

        .blog-meta span i {
            color: var(--primary);
            margin-right: 5px;
        }

        .blog-content {
            line-height: 1.8;
            font-size: 1.1rem;
            color: #444;
        }

        .blog-content p {
            margin-bottom: 1.5rem;
        }

        .blog-content h2,
        .blog-content h3 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1.5rem 0;
        }

        .blog-content ul,
        .blog-content ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .blog-content blockquote {
            border-left: 4px solid var(--primary);
            padding-left: 1.5rem;
            font-style: italic;
            margin: 1.5rem 0;
            color: #555;
        }

        .blog-tags {
            margin: 2rem 0;
        }

        .blog-tags a {
            display: inline-block;
            background-color: #f0f0f0;
            color: #555;
            padding: 5px 15px;
            border-radius: 50px;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .blog-tags a:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Author Box */
        .author-box {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            display: flex;
            align-items: center;
        }

        .author-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 1.5rem;
        }

        .author-bio h4 {
            margin-bottom: 0.5rem;
        }

        .author-bio p {
            margin-bottom: 0;
            color: #555;
        }

        /* Comments Section */
        .comments-section {
            margin-top: 3rem;
        }

        .comments-title {
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 10px;
        }

        .comments-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        .comment {
            margin-bottom: 2rem;
            position: relative;
        }

        .comment-body {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 1.5rem;
            margin-left: 80px;
            position: relative;
        }

        .comment-body:before {
            content: '';
            position: absolute;
            left: -10px;
            top: 20px;
            width: 0;
            height: 0;
            border-top: 10px solid transparent;
            border-bottom: 10px solid transparent;
            border-right: 10px solid #f9f9f9;
        }

        .comment-author-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
            position: absolute;
            left: 0;
            top: 0;
        }

        .comment-author {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .comment-date {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 10px;
        }

        .comment-content {
            margin-bottom: 10px;
        }

        .comment-reply {
            display: inline-block;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .comment-reply:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        .reply-form {
            display: none;
            margin-top: 1rem;
        }

        .replies {
            margin-left: 80px;
            margin-top: 1.5rem;
        }

        /* Comment Form */
        .comment-form {
            margin-top: 3rem;
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 2rem;
        }

        .comment-form h3 {
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .comment-form h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        .comment-form .form-control {
            border-radius: 8px;
            padding: 0.8rem 1.2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
        }

        .comment-form textarea.form-control {
            height: 150px;
        }

        .comment-form .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
        }

        /* Error styling */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: -1rem;
            margin-bottom: 1rem;
        }

        /* Share Buttons */
        .share-buttons {
            display: flex;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .share-buttons .btn {
            margin-right: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            padding: 8px 15px;
            border-radius: 50px;
            color: white;
            transition: all 0.3s ease;
        }

        .share-buttons .btn i {
            margin-right: 5px;
        }

        .btn-facebook {
            background-color: #3b5998;
        }

        .btn-twitter {
            background-color: #1da1f2;
        }

        .btn-linkedin {
            background-color: #0077b5;
        }

        .btn-pinterest {
            background-color: #bd081c;
        }

        .btn-whatsapp {
            background-color: #25d366;
        }

        .share-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* Related Posts */
        .related-posts {
            margin-top: 3rem;
        }

        .related-posts h3 {
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .related-posts h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        .related-post-card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 100%
        }

        .related-post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .related-post-img {
            height: 200px;
            overflow: hidden;
        }

        .related-post-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .related-post-card:hover .related-post-img img {
            transform: scale(1.05);
        }

        .related-post-content {
            padding: 1.5rem;
        }

        .related-post-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .related-post-date {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 0.5rem;
        }

        /* Sidebar */
        .sidebar-widget {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar-widget h3 {
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }

        .sidebar-widget h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--primary);
        }

        .sidebar-widget ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-widget ul li {
            padding: 10px 0;
            border-bottom: 1px solid #e9e9e9;
        }

        .sidebar-widget ul li:last-child {
            border-bottom: none;
        }

        .sidebar-widget ul li a {
            color: #444;
            transition: all 0.3s ease;
            display: block;
        }

        .sidebar-widget ul li a:hover {
            color: var(--primary);
            padding-left: 5px;
        }

        .sidebar-widget .cat-count {
            float: right;
            background-color: #eee;
            color: #777;
            border-radius: 50px;
            padding: 2px 10px;
            font-size: 0.8rem;
        }

        .recent-post {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9e9e9;
        }

        .recent-post:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .recent-post-img {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 1rem;
        }

        .recent-post-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .recent-post-content h4 {
            font-size: 0.9rem;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .recent-post-date {
            font-size: 0.8rem;
            color: #777;
        }

        /* Tag Cloud */
        .tag-cloud {
            margin-top: 1rem;
        }

        .tag-cloud a {
            display: inline-block;
            background-color: #eee;
            color: #555;
            padding: 5px 12px;
            border-radius: 50px;
            margin-right: 5px;
            margin-bottom: 10px;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .tag-cloud a:hover {
            background-color: var(--primary);
            color: white;
        }

        /* Table of Contents */
        .toc-container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .toc-container h4 {
            margin-bottom: 1rem;
        }

        .toc-container ul {
            padding-left: 1.5rem;
        }

        .toc-container ul li {
            margin-bottom: 0.5rem;
        }

        .toc-container ul li a {
            color: #444;
            transition: all 0.3s ease;
        }

        .toc-container ul li a:hover {
            color: var(--primary);
        }

        /* Reading Progress Bar */
        .progress-container {
            position: fixed;
            top: 0;
            z-index: 1;
            width: 100%;
            height: 4px;
            background: transparent;
        }

        .progress-bar {
            height: 4px;
            background: var(--primary);
            width: 0%;
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .back-to-top.show {
            opacity: 1;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .author-box {
                flex-direction: column;
                text-align: center;
            }

            .author-img {
                margin-right: 0;
                margin-bottom: 1rem;
            }

            .comment-body {
                margin-left: 0;
            }

            .comment-body:before {
                display: none;
            }

            .comment-author-img {
                position: relative;
                margin-bottom: 1rem;
            }

            .replies {
                margin-left: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    @include('site.includes.head')

    <div class="progress-container">
        <div class="progress-bar" id="readingProgress"></div>
    </div>

    <!-- Blog Post Detail Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="blog-detail-img wow fadeInUp" data-wow-delay="0.1s">
                        @if($post->featured_image)
                            <img src="{{ Storage::url($post->featured_image) }}" class="img-fluid w-100"
                                alt="{{ $post->title }}">
                        @else
                            <img src="{{ asset('site/img/blog-header.jpg') }}" class="img-fluid w-100" alt="{{ $post->title }}">
                        @endif
                    </div>

                    <div class="blog-detail-content">
                        <h1 class="display-5 mb-4 wow fadeInUp" data-wow-delay="0.1s">{{ $post->title }}</h1>

                        <div class="blog-meta wow fadeInUp" data-wow-delay="0.1s">
                            <span><i class="far fa-calendar-alt"></i> {{ $post->published_at->format('M d, Y') }}</span>
                            <span><i class="far fa-user"></i> {{ $post->author ? $post->author->name : 'Admin' }}</span>
                            <span><i class="far fa-comments"></i> {{ $comments->count() }} Comments</span>
                            @if($post->category)
                                <span><i class="far fa-folder"></i> <a
                                        href="{{ route('blog.category', $post->category->slug) }}">{{ $post->category->name }}</a></span>
                            @endif
                        </div>

                        <!-- Table of Contents -->
                        @if(strlen($post->content) > 1000)
                            <div class="toc-container wow fadeInUp" data-wow-delay="0.1s">
                                <h4>Table of Contents</h4>
                                <div id="toc"></div>
                            </div>
                        @endif

                        <div class="blog-content wow fadeInUp" data-wow-delay="0.1s">
                            {!! $post->content !!}
                        </div>

                        <!-- Tags -->
                        @if($post->tags && $post->tags->count() > 0)
                            <div class="blog-tags wow fadeInUp" data-wow-delay="0.1s">
                                <strong>Tags:</strong>
                                @foreach($post->tags as $tag)
                                    <a href="{{ route('blog.tag', $tag->slug) }}">{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        @endif

                        <!-- Social Share Buttons -->
                        <div class="share-buttons wow fadeInUp" data-wow-delay="0.1s">
                            <h4 class="mb-3">Share This Post:</h4>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post->slug)) }}"
                                class="btn btn-facebook" target="_blank">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blog.show', $post->slug)) }}&text={{ urlencode($post->title) }}"
                                class="btn btn-twitter" target="_blank">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('blog.show', $post->slug)) }}&title={{ urlencode($post->title) }}"
                                class="btn btn-linkedin" target="_blank">
                                <i class="fab fa-linkedin-in"></i> LinkedIn
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url={{ urlencode(route('blog.show', $post->slug)) }}&media={{ urlencode($post->featured_image ? Storage::url($post->featured_image) : asset('site/img/blog-header.jpg')) }}&description={{ urlencode($post->title) }}"
                                class="btn btn-pinterest" target="_blank">
                                <i class="fab fa-pinterest-p"></i> Pinterest
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . route('blog.show', $post->slug)) }}"
                                class="btn btn-whatsapp" target="_blank">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>

                        <!-- Author Box -->
                        @if($post->author)
                            <div class="author-box wow fadeInUp" data-wow-delay="0.1s">
                                <div class="author-img">
                                    @if($post->author->profile_image)
                                        <img src="{{ Storage::url($post->author->profile_image) }}" class="img-fluid"
                                            alt="{{ $post->author->name }}">
                                    @else
                                        <img src="{{ asset('site/img/user.jpg') }}" class="img-fluid"
                                            alt="{{ $post->author->name }}">
                                    @endif
                                </div>
                                <div class="author-bio">
                                    <h4>{{ $post->author->name }}</h4>
                                    <p>{{ $post->author->bio ?? 'Author at Cental Car Rental Blog' }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Related Posts -->
                        @if($relatedPosts && $relatedPosts->count() > 0)
                            <div class="related-posts wow fadeInUp" data-wow-delay="0.1s">
                                <h3>Related Posts</h3>
                                <div class="row">
                                    @foreach($relatedPosts->take(3) as $relatedPost)
                                        <div class="col-md-4">
                                            <div class="related-post-card">
                                                <div class="related-post-img">
                                                    @if($relatedPost->featured_image)
                                                        <img src="{{ Storage::url($relatedPost->featured_image) }}" class="img-fluid"
                                                            alt="{{ $relatedPost->title }}">
                                                    @else
                                                        <img src="{{ asset('site/img/blog-' . rand(1, 3) . '.jpg') }}" class="img-fluid"
                                                            alt="{{ $relatedPost->title }}">
                                                    @endif
                                                </div>
                                                <div class="related-post-content">
                                                    <div class="related-post-date">
                                                        {{ $relatedPost->published_at->format('M d, Y') }}
                                                    </div>
                                                    <h5 class="related-post-title">
                                                        <a
                                                            href="{{ route('blog.show', $relatedPost->slug) }}">{{ $relatedPost->title }}</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Comments Section -->
                        <div class="comments-section wow fadeInUp" data-wow-delay="0.1s">
                            <h3 class="comments-title">{{ $comments->count() }} Comments</h3>

                            <div class="comments-list">
                                @foreach($comments->where('parent_id', null) as $comment)
                                    <div class="comment" id="comment-{{ $comment->id }}">
                                        <div class="comment-author-img">
                                            <img src="{{ $comment->avatar }}" class="img-fluid" alt="{{ $comment->name }}">
                                        </div>
                                        <div class="comment-body">
                                            <h4 class="comment-author">{{ $comment->name }}</h4>
                                            <div class="comment-date">{{ $comment->formatted_date }}</div>
                                            <div class="comment-content">
                                                <p>{{ $comment->content }}</p>
                                            </div>
                                            <div class="comment-reply" data-comment-id="{{ $comment->id }}">Reply</div>

                                            <!-- Reply Form -->
                                            <div class="reply-form" id="reply-form-{{ $comment->id }}" style="display: none;">
                                                <form action="javascript:void(0)" method="POST"
                                                    data-post-slug="{{ $post->slug }}" data-comment-id="{{ $comment->id }}">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="text" name="name" class="form-control"
                                                                    placeholder="Your Name *" required
                                                                    value="{{ $commenterName ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <input type="email" name="email" class="form-control"
                                                                    placeholder="Your Email *" required
                                                                    value="{{ $commenterEmail ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <input type="url" name="website" class="form-control"
                                                                    placeholder="Your Website"
                                                                    value="{{ $commenterWebsite ?? '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-group">
                                                                <textarea name="content" class="form-control" rows="3"
                                                                    placeholder="Your Reply *" required></textarea>
                                                            </div>
                                                            <div class="form-check mb-3">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="saveInfoReply{{ $comment->id }}" name="save_info"
                                                                    checked>
                                                                <label class="form-check-label"
                                                                    for="saveInfoReply{{ $comment->id }}">Save my information
                                                                    for future comments</label>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary">Submit Reply</button>
                                                            <button type="button" class="btn btn-secondary cancel-reply"
                                                                data-comment-id="{{ $comment->id }}">Cancel</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Comment Replies -->
                                        @if($comment->replies->count() > 0)
                                            <div class="replies" id="replies-{{ $comment->id }}">
                                                @foreach($comment->replies as $reply)
                                                    <div class="comment reply" id="comment-{{ $reply->id }}">
                                                        <div class="comment-author-img">
                                                            <img src="{{ $reply->avatar }}" class="img-fluid" alt="{{ $reply->name }}">
                                                        </div>
                                                        <div class="comment-body">
                                                            <h4 class="comment-author">{{ $reply->name }}</h4>
                                                            <div class="comment-date">{{ $reply->formatted_date }}</div>
                                                            <div class="comment-content">
                                                                <p>{{ $reply->content }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="replies" id="replies-{{ $comment->id }}"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($comments->count() == 0)
                                <p>No comments yet. Be the first to comment!</p>
                            @endif

                            @if($totalComments > $comments->count())
                                <div class="text-center mt-4">
                                    <button id="load-more-comments" class="btn btn-outline-primary"
                                        data-post-slug="{{ $post->slug }}" data-current-page="1">
                                        Load More Comments
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Comment Form -->
                        <div class="comment-form wow fadeInUp" data-wow-delay="0.1s">
                            <h3>Leave a Comment</h3>
                            <form id="comment-form" action="javascript:void(0)" method="POST"
                                data-post-slug="{{ $post->slug }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="text" name="name" class="form-control" placeholder="Your Name *"
                                                required value="{{ $commenterName ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control" placeholder="Your Email *"
                                                required value="{{ $commenterEmail ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="url" name="website" class="form-control" placeholder="Your Website"
                                                value="{{ $commenterWebsite ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <textarea name="content" class="form-control" rows="5"
                                                placeholder="Your Comment *" required></textarea>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input type="checkbox" class="form-check-input" id="saveInfo" name="save_info"
                                                checked>
                                            <label class="form-check-label" for="saveInfo">Save my information for future
                                                comments</label>
                                        </div>
                                        <button type="submit" class="btn btn-primary py-3 px-5">Post Comment</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Search Widget -->
                    <div class="sidebar-widget wow fadeInUp" data-wow-delay="0.1s">
                        <h3>Search</h3>
                        <form action="" method="GET">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Search Posts..."
                                    value="{{ request()->get('q') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div class="sidebar-widget wow fadeInUp" data-wow-delay="0.1s">
                        <h3>Categories</h3>
                        <ul>
                            @foreach($categories as $category)
                                <li>
                                    <a href="{{ route('blog.category', $category->slug) }}">
                                        {{ $category->name }}
                                        <span class="cat-count">{{ $category->posts_count ?? 0 }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div class="sidebar-widget wow fadeInUp" data-wow-delay="0.1s">
                        <h3>Recent Posts</h3>
                        @foreach($recentPosts as $recentPost)
                            <div class="recent-post">
                                <div class="recent-post-img">
                                    @if($recentPost->featured_image)
                                        <img src="{{ Storage::url($recentPost->featured_image) }}" class="img-fluid"
                                            alt="{{ $recentPost->title }}">
                                    @else
                                        <img src="{{ asset('site/img/blog-' . rand(1, 3) . '.jpg') }}" class="img-fluid"
                                            alt="{{ $recentPost->title }}">
                                    @endif
                                </div>
                                <div class="recent-post-content">
                                    <h4><a href="{{ route('blog.show', $recentPost->slug) }}">{{ $recentPost->title }}</a></h4>
                                    <div class="recent-post-date">{{ $recentPost->published_at->format('M d, Y') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Tags Widget -->
                    <div class="sidebar-widget wow fadeInUp" data-wow-delay="0.1s">
                        <h3>Popular Tags</h3>
                        <div class="tag-cloud">
                            @foreach($popularTags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}">{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="sidebar-widget wow fadeInUp" data-wow-delay="0.1s">
                        <h3>Newsletter</h3>
                        <p>Subscribe to our newsletter to get the latest updates directly to your inbox.</p>
                        <form method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="email" name="email" class="form-control" placeholder="Your Email *" required>
                                <button class="btn btn-primary" type="submit">
                                    Subscribe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Blog Post Detail End -->

    <!-- Back to Top -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Structured Data for Article -->
    <script type="application/ld+json">
                {
                  "@context": "https://schema.org",
                  "@type": "BlogPosting",
                  "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": "{{ route('blog.show', $post->slug) }}"
                  },
                  "headline": "{{ $post->title }}",
                  "description": "{{ $post->meta_description ?? Str::limit(strip_tags($post->content), 160) }}",
                  "image": "{{ $post->featured_image ? Storage::url($post->featured_image) : asset('site/img/blog-header.jpg') }}",
                  "author": {
                    "@type": "Person",
                    "name": "{{ $post->author ? $post->author->name : 'Admin' }}"
                  },
                  "publisher": {
                    "@type": "Organization",
                    "name": "Cental Car Rental",
                    "logo": {
                      "@type": "ImageObject",
                      "url": "{{ asset('site/img/logo.png') }}"
                    }
                  },
                  "datePublished": "{{ $post->published_at->toIso8601String() }}",
                  "dateModified": "{{ $post->updated_at->toIso8601String() }}"
                }
                </script>

    <!-- Structured Data for BreadcrumbList -->
    <script type="application/ld+json">
                {
                  "@context": "https://schema.org",
                  "@type": "BreadcrumbList",
                  "itemListElement": [
                    {
                      "@type": "ListItem",
                      "position": 1,
                      "name": "Home",
                      "item": "{{ route('home') }}"
                    },
                    {
                      "@type": "ListItem",
                      "position": 2,
                      "name": "Blog",
                      "item": "{{ route('blog.index') }}"
                    },
                    @if($post->category)
                        {
                          "@type": "ListItem",
                          "position": 3,
                          "name": "{{ $post->category->name }}",
                          "item": "{{ route('blog.category', $post->category->slug) }}"
                        },
                        {
                          "@type": "ListItem",
                          "position": 4,
                          "name": "{{ $post->title }}",
                          "item": "{{ route('blog.show', $post->slug) }}"
                        }
                    @else
                        {
                          "@type": "ListItem",
                          "position": 3,
                          "name": "{{ $post->title }}",
                          "item": "{{ route('blog.show', $post->slug) }}"
                        }
                    @endif
                  ]
                }
                </script>
@endsection

@push('scripts')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function () {
            // Reading Progress Bar
            window.onscroll = function () {
                updateReadingProgress();
            };

            function updateReadingProgress() {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                document.getElementById("readingProgress").style.width = scrolled + "%";

                // Back to Top Button
                if (document.body.scrollTop > 500 || document.documentElement.scrollTop > 500) {
                    document.getElementById("backToTop").classList.add("show");
                } else {
                    document.getElementById("backToTop").classList.remove("show");
                }
            }

            // Back to Top Button Click
            $("#backToTop").on("click", function (e) {
                e.preventDefault();
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

            // Table of Contents Generation
            const tocContainer = document.getElementById("toc");
            if (tocContainer) {
                const headings = document.querySelectorAll(".blog-content h2, .blog-content h3");
                if (headings.length > 0) {
                    const toc = document.createElement("ul");

                    headings.forEach((heading, index) => {
                        // Create an ID for the heading if it doesn't have one
                        if (!heading.id) {
                            heading.id = "heading-" + index;
                        }

                        const listItem = document.createElement("li");
                        const link = document.createElement("a");
                        link.href = "#" + heading.id;
                        link.textContent = heading.textContent;

                        // Add indentation for h3
                        if (heading.tagName.toLowerCase() === 'h3') {
                            listItem.style.marginLeft = "20px";
                        }

                        listItem.appendChild(link);
                        toc.appendChild(listItem);
                    });

                    tocContainer.appendChild(toc);
                } else {
                    // If no headings found, hide the ToC container
                    document.querySelector(".toc-container").style.display = "none";
                }
            }

            // Initialize SweetAlert2
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Comment Form AJAX Submission
            $('#comment-form').on('submit', function (e) {
                e.preventDefault();

                // Clear previous error messages
                $('.error-message').remove();
                $('.is-invalid').removeClass('is-invalid');

                // Get form data
                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const postSlug = $(this).data('post-slug');

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

                // Submit comment via AJAX
                $.ajax({
                    url: `/blogs/${postSlug}/comment`,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // Reset form
                        $('#comment-form')[0].reset();

                        // Show success message
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        // If comment is approved, append it to comments list
                        if (response.isApproved) {
                            appendNewComment(response.comment);

                            // Update comment count
                            updateCommentCount(1);
                        }

                        // Enable submit button
                        submitBtn.prop('disabled', false).html('Post Comment');
                    },
                    error: function (xhr) {
                        // Parse error response
                        const response = xhr.responseJSON;

                        // Show error message
                        Toast.fire({
                            icon: 'error',
                            title: 'Error submitting comment'
                        });

                        // Display validation errors
                        if (response && response.errors) {
                            displayValidationErrors(response.errors, '#comment-form');
                        }

                        // Enable submit button
                        submitBtn.prop('disabled', false).html('Post Comment');
                    }
                });
            });

            // Reply Form AJAX Submission
            $(document).on('submit', '.reply-form form', function (e) {
                e.preventDefault();

                // Clear previous error messages
                $('.error-message').remove();
                $('.is-invalid').removeClass('is-invalid');

                // Get form data
                const formData = $(this).serialize();
                const submitBtn = $(this).find('button[type="submit"]');
                const cancelBtn = $(this).find('button.cancel-reply');
                const postSlug = $(this).data('post-slug');
                const commentId = $(this).data('comment-id');

                // Disable submit button and show loading
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                cancelBtn.prop('disabled', true);

                // Submit reply via AJAX
                $.ajax({
                    url: `/blogs/${postSlug}/comment/${commentId}/reply`,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        // Reset form and hide it
                        $(`.reply-form#reply-form-${commentId}`).slideUp();
                        $(`.reply-form#reply-form-${commentId} form`)[0].reset();

                        // Show success message
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        // If reply is approved, append it to replies list
                        if (response.isApproved) {
                            appendNewReply(response.reply);

                            // Update comment count
                            updateCommentCount(1);
                        }

                        // Enable buttons
                        submitBtn.prop('disabled', false).html('Submit Reply');
                        cancelBtn.prop('disabled', false);
                    },
                    error: function (xhr) {
                        // Parse error response
                        const response = xhr.responseJSON;

                        // Show error message
                        Toast.fire({
                            icon: 'error',
                            title: 'Error submitting reply'
                        });

                        // Display validation errors
                        if (response && response.errors) {
                            displayValidationErrors(response.errors, `#reply-form-${commentId} form`);
                        }

                        // Enable buttons
                        submitBtn.prop('disabled', false).html('Submit Reply');
                        cancelBtn.prop('disabled', false);
                    }
                });
            });

            // Reply Button Click
            $(document).on('click', '.comment-reply', function () {
                const commentId = $(this).data('comment-id');
                $("#reply-form-" + commentId).slideToggle();
            });

            // Cancel Reply Button Click
            $(document).on('click', '.cancel-reply', function () {
                const commentId = $(this).data('comment-id');
                $("#reply-form-" + commentId).slideUp();
            });

            // Function to display validation errors
            function displayValidationErrors(errors, formSelector) {
                // Loop through each error
                for (const field in errors) {
                    const errorMessage = errors[field][0];
                    const inputField = $(`${formSelector} [name="${field}"]`);

                    // Add error class to input
                    inputField.addClass('is-invalid');

                    // Add error message after input
                    inputField.after(`<div class="error-message text-danger mt-1"><small>${errorMessage}</small></div>`);
                }
            }

            // Function to append new comment to the comments list
            function appendNewComment(comment) {
                const commentHtml = `
                            <div class="comment wow fadeInUp" id="comment-${comment.id}" data-wow-delay="0.1s">
                                <div class="comment-author-img">
                                    <img src="${comment.avatar}" class="img-fluid" alt="${comment.name}">
                                </div>
                                <div class="comment-body">
                                    <h4 class="comment-author">${comment.name}</h4>
                                    <div class="comment-date">${comment.formattedDate}</div>
                                    <div class="comment-content">
                                        <p>${comment.content}</p>
                                    </div>
                                    <div class="comment-reply" data-comment-id="${comment.id}">Reply</div>

                                    <!-- Reply Form -->
                                    <div class="reply-form" id="reply-form-${comment.id}" style="display: none;">
                                        <form action="javascript:void(0)" method="POST" data-post-slug="${$('#comment-form').data('post-slug')}" data-comment-id="${comment.id}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" name="name" class="form-control" placeholder="Your Name *" required value="${$('#comment-form [name="name"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="email" name="email" class="form-control" placeholder="Your Email *" required value="${$('#comment-form [name="email"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="url" name="website" class="form-control" placeholder="Your Website" value="${$('#comment-form [name="website"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <textarea name="content" class="form-control" rows="3" placeholder="Your Reply *" required></textarea>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="checkbox" class="form-check-input" id="saveInfoReply${comment.id}" name="save_info" checked>
                                                        <label class="form-check-label" for="saveInfoReply${comment.id}">Save my information for future comments</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Submit Reply</button>
                                                    <button type="button" class="btn btn-secondary cancel-reply" data-comment-id="${comment.id}">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Comment Replies -->
                                <div class="replies" id="replies-${comment.id}">
                                    <!-- Replies will be added here -->
                                </div>
                            </div>
                        `;

                // If no comments yet, remove the no comments message
                if ($('.comments-list p').length > 0 && $('.comments-list p').text().includes('No comments yet')) {
                    $('.comments-list p').remove();
                }

                // Append the new comment to the comments list
                $('.comments-list').prepend(commentHtml);

                // Initialize WOW animations
                new WOW().init();
            }

            // Function to append new reply to the replies list
            function appendNewReply(reply) {
                const replyHtml = `
                            <div class="comment reply wow fadeInUp" id="comment-${reply.id}" data-wow-delay="0.1s">
                                <div class="comment-author-img">
                                    <img src="${reply.avatar}" class="img-fluid" alt="${reply.name}">
                                </div>
                                <div class="comment-body">
                                    <h4 class="comment-author">${reply.name}</h4>
                                    <div class="comment-date">${reply.formattedDate}</div>
                                    <div class="comment-content">
                                        <p>${reply.content}</p>
                                    </div>
                                </div>
                            </div>
                        `;

                // Append the new reply to the replies list
                $(`#replies-${reply.parentId}`).append(replyHtml);

                // Initialize WOW animations
                new WOW().init();
            }

            // Function to update comment count
            function updateCommentCount(increment) {
                const countElement = $('.comments-title');
                let currentCount = parseInt(countElement.text().split(' ')[0]);
                currentCount += increment;
                countElement.text(`${currentCount} Comments`);

                // Also update the comment count in meta
                const metaCountElement = $('.blog-meta span:contains("Comments")');
                if (metaCountElement.length) {
                    metaCountElement.html(`<i class="far fa-comments"></i> ${currentCount} Comments`);
                }
            }

            // Load More Comments Button Click
            $('#load-more-comments').on('click', function () {
                const button = $(this);
                const postSlug = button.data('post-slug');
                const currentPage = button.data('current-page');
                const nextPage = currentPage + 1;

                // Disable button and show loading
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

                // Load more comments via AJAX
                $.ajax({
                    url: `/blogs/${postSlug}/comments`,
                    type: 'GET',
                    data: {
                        page: nextPage,
                        per_page: 10
                    },
                    success: function (response) {
                        if (response.success) {
                            // Append comments to the list
                            response.comments.forEach(function (comment) {
                                appendLoadedComment(comment);
                            });

                            // Update pagination
                            button.data('current-page', nextPage);

                            // Check if there are more pages
                            if (!response.pagination.has_more_pages) {
                                button.remove();
                            }
                        }

                        // Enable button
                        button.prop('disabled', false).html('Load More Comments');

                        // Initialize WOW animations
                        new WOW().init();
                    },
                    error: function () {
                        // Show error message
                        Toast.fire({
                            icon: 'error',
                            title: 'Error loading comments'
                        });

                        // Enable button
                        button.prop('disabled', false).html('Load More Comments');
                    }
                });
            });

            // Function to append loaded comment (with replies)
            function appendLoadedComment(comment) {
                let repliesHtml = '';

                // Generate HTML for replies if any
                if (comment.hasReplies) {
                    comment.replies.forEach(function (reply) {
                        repliesHtml += `
                                    <div class="comment reply" id="comment-${reply.id}">
                                        <div class="comment-author-img">
                                            <img src="${reply.avatar}" class="img-fluid" alt="${reply.name}">
                                        </div>
                                        <div class="comment-body">
                                            <h4 class="comment-author">${reply.name}</h4>
                                            <div class="comment-date">${reply.formattedDate}</div>
                                            <div class="comment-content">
                                                <p>${reply.content}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;
                    });
                }

                const commentHtml = `
                            <div class="comment" id="comment-${comment.id}">
                                <div class="comment-author-img">
                                    <img src="${comment.avatar}" class="img-fluid" alt="${comment.name}">
                                </div>
                                <div class="comment-body">
                                    <h4 class="comment-author">${comment.name}</h4>
                                    <div class="comment-date">${comment.formattedDate}</div>
                                    <div class="comment-content">
                                        <p>${comment.content}</p>
                                    </div>
                                    <div class="comment-reply" data-comment-id="${comment.id}">Reply</div>

                                    <!-- Reply Form -->
                                    <div class="reply-form" id="reply-form-${comment.id}" style="display: none;">
                                        <form action="javascript:void(0)" method="POST" data-post-slug="${$('#comment-form').data('post-slug')}" data-comment-id="${comment.id}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="text" name="name" class="form-control" placeholder="Your Name *" required value="${$('#comment-form [name="name"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <input type="email" name="email" class="form-control" placeholder="Your Email *" required value="${$('#comment-form [name="email"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <input type="url" name="website" class="form-control" placeholder="Your Website" value="${$('#comment-form [name="website"]').val()}">
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <textarea name="content" class="form-control" rows="3" placeholder="Your Reply *" required></textarea>
                                                    </div>
                                                    <div class="form-check mb-3">
                                                        <input type="checkbox" class="form-check-input" id="saveInfoReply${comment.id}" name="save_info" checked>
                                                        <label class="form-check-label" for="saveInfoReply${comment.id}">Save my information for future comments</label>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Submit Reply</button>
                                                    <button type="button" class="btn btn-secondary cancel-reply" data-comment-id="${comment.id}">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Comment Replies -->
                                <div class="replies" id="replies-${comment.id}">
                                    ${repliesHtml}
                                </div>
                            </div>
                        `;

                // Append the comment to the comments list
                $('.comments-list').append(commentHtml);
            }

            // Image Lightbox
            $(".blog-content img").on("click", function () {
                const img = $(this);
                const src = img.attr("src");

                Swal.fire({
                    imageUrl: src,
                    imageAlt: 'Image',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        container: 'lightbox-container',
                        image: 'lightbox-image'
                    }
                });
            });

            // Make blog content images clickable
            $(".blog-content img").css("cursor", "pointer");
        });
    </script>
@endpush
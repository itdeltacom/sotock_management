@extends('site.layouts.app')

@section('title', 'Cental - Premium Car Rental Services | Blog & News')
@section('meta_description', 'Explore Cental\'s blog for expert car rental tips, driving guides, and automotive news. Stay updated with the latest in the car rental industry.')
@section('meta_keywords', 'car rental, luxury car rental, premium cars, car rental blog, driving tips, automotive news, travel guides')

@section('og_title', 'Cental Blog - Car Rental News & Tips')
@section('og_description', 'Read our latest articles on car rentals, driving guides, and travel tips. Get expert advice on making the most of your rental experience.')
@section('og_image', asset('site/img/blog-header.jpg'))

@push('styles')
    <style>
        /* Custom CSS for Blog AJAX Loading Animations */

        /* Blog item hover effect */
        .blog-item {
            transition: all 0.4s ease;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .blog-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Image zoom effect on hover */
        .blog-img {
            overflow: hidden;
        }

        .blog-img img {
            transition: transform 0.5s ease;
        }

        .blog-item:hover .blog-img img {
            transform: scale(1.05);
        }

        /* Read more link animation */
        .blog-content a:not(.h4) {
            position: relative;
            transition: all 0.3s ease;
        }

        .blog-content a:not(.h4):after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }

        .blog-content a:not(.h4):hover:after {
            width: 100%;
        }

        .blog-content a:not(.h4) i {
            transition: transform 0.3s ease;
        }

        .blog-content a:not(.h4):hover i {
            transform: translateX(5px);
        }

        /* Load More Button Animation */
        #load-more-btn {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        #load-more-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        #load-more-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 1s;
        }

        #load-more-btn:hover:before {
            left: 100%;
        }

        /* Loading Spinner Animation */
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loading-spinner {
            animation: spin 1s linear infinite;
        }

        /* Blog date styling */
        .blog-date {
            background: var(--primary);
            color: white;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: 500;
            position: relative;
            top: -20px;
            margin-bottom: -10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        /* Newly loaded posts animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-post-animation {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
@endpush

@section('content')
    @include('site.includes.head')

    <!-- Blog Start -->
    <div class="container-fluid blog py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Cental<span class="text-primary"> Blog & News</span></h1>
                <p class="mb-0">Stay updated with the latest news, tips, and guides for car rentals. From vehicle
                    maintenance to road trip ideas, we've got everything you need to make the most of your rental
                    experience.</p>
            </div>

            <div class="row g-4" id="blog-posts-container">
                @foreach($posts as $post)
                    <div class="col-lg-4 wow fadeInUp" data-wow-delay="{{ ($loop->iteration % 3) * 0.2 + 0.1 }}s">
                        <div class="blog-item">
                            <div class="blog-img">
                                @if($post->featured_image)
                                    <img src="{{ Storage::url($post->featured_image) }}" class="img-fluid rounded-top w-100"
                                        alt="{{ $post->title }}">
                                @else
                                    <img src="{{ asset('site/img/blog-' . rand(1, 3) . '.jpg') }}"
                                        class="img-fluid rounded-top w-100" alt="{{ $post->title }}">
                                @endif
                            </div>
                            <div class="blog-content rounded-bottom p-4">
                                <div class="blog-date">{{ $post->published_at->format('d M Y') }}</div>
                                <div class="blog-comment my-3">
                                    <div class="small"><span class="fa fa-user text-primary"></span><span
                                            class="ms-2">{{ $post->author ? $post->author->name : 'Admin' }}</span></div>
                                    <div class="small"><span class="fa fa-comment-alt text-primary"></span><span
                                            class="ms-2">{{ $post->comments_count ?? 0 }} Comments</span></div>
                                </div>
                                <a href="{{ route('blog.show', $post->slug) }}" class="h4 d-block mb-3">{{ $post->title }}</a>
                                <p class="mb-3">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 120) }}</p>
                                <a href="{{ route('blog.show', $post->slug) }}" class="">Read More <i
                                        class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Load More Button -->
            <div class="text-center mt-5">
                @if($posts->hasMorePages())
                    <button id="load-more-btn" class="btn btn-primary rounded-pill py-3 px-4 px-md-5"
                        data-current-page="{{ $posts->currentPage() }}">
                        Load More Articles
                    </button>
                    <div id="loading-spinner" class="spinner-border text-primary mt-3 d-none" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Blog End -->

    <!-- Fact Counter -->
    <div class="container-fluid counter py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-thumbs-up fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">829</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Happy Clients</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-car-alt fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">56</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Number of Cars</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">127</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Car Center</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="counter-item text-center">
                        <div class="counter-item-icon mx-auto">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="counter-counting my-3">
                            <span class="text-white fs-2 fw-bold" data-toggle="counter-up">589</span>
                            <span class="h1 fw-bold text-white">+</span>
                        </div>
                        <h4 class="text-white mb-0">Total kilometers</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fact Counter -->

    <div class="container-fluid banner py-5 wow zoomInDown" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="banner-item rounded">
                <img src="{{ asset('site/img/banner-1.jpg') }}" class="img-fluid rounded w-100" alt="">
                <div class="banner-content">
                    <h2 class="text-primary">Rent Your Car</h2>
                    <h1 class="text-white">Interested in Renting?</h1>
                    <p class="text-white">Don't hesitate and send us a message.</p>
                    <div class="banner-btn">
                        <a href="#" class="btn btn-secondary rounded-pill py-3 px-4 px-md-5 me-2">WhatsApp</a>
                        <a href="{{ route('contact') }}" class="btn btn-primary rounded-pill py-3 px-4 px-md-5 ms-2">Contact
                            Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Structured Data for Blog -->
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "Blog",
          "name": "Cental Car Rental Blog",
          "url": "{{ route('blog.index') }}",
          "description": "The latest news, tips and guides about car rentals, vehicle maintenance, and road trip ideas.",
          "publisher": {
            "@type": "Organization",
            "name": "Cental Car Rental",
            "logo": {
              "@type": "ImageObject",
              "url": "{{ asset('site/img/logo.png') }}"
            }
          },
          "blogPost": [
            @foreach($posts as $post)
                {
                  "@type": "BlogPosting",
                  "headline": "{{ $post->title }}",
                  "description": "{{ Str::limit($post->excerpt ?? strip_tags($post->content), 150) }}",
                  "url": "{{ route('blog.show', $post->slug) }}",
                  "datePublished": "{{ $post->published_at->toIso8601String() }}",
                  "dateModified": "{{ $post->updated_at->toIso8601String() }}",
                  "author": {
                    "@type": "Person",
                    "name": "{{ $post->author ? $post->author->name : 'Admin' }}"
                  },
                  "image": "{{ $post->featured_image ? Storage::url($post->featured_image) : asset('site/img/blog-' . rand(1, 3) . '.jpg') }}"
                }{{ !$loop->last ? ',' : '' }}
            @endforeach
          ]
        }
        </script>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#load-more-btn').on('click', function () {
                let button = $(this);
                let currentPage = button.data('current-page');
                let nextPage = currentPage + 1;
                let spinner = $('#loading-spinner');

                // Show loading spinner, hide button
                button.addClass('d-none');
                spinner.removeClass('d-none');

                $.ajax({
                    url: "{{ route('blogs.load-more') }}",
                    type: "GET",
                    data: {
                        page: nextPage,
                    },
                    success: function (response) {
                        // Hide spinner
                        spinner.addClass('d-none');

                        if (response.error) {
                            console.error('Server returned an error:', response.error);
                            alert('Error: ' + response.error);
                            button.removeClass('d-none');
                            return;
                        }

                        // Append new posts with animation
                        $('#blog-posts-container').append(response.html);

                        // Add animation to newly loaded posts
                        const newItems = $('#blog-posts-container .col-lg-4').slice(-response.count);
                        newItems.css('opacity', 0);

                        // Ensure images are loaded before animating
                        const imgPromises = [];
                        newItems.find('img').each(function () {
                            const img = $(this)[0];
                            if (img.complete) {
                                return;
                            }

                            const promise = new Promise(resolve => {
                                img.onload = resolve;
                                img.onerror = resolve; // Handle error case as well
                            });
                            imgPromises.push(promise);
                        });

                        // After all images load, animate in the cards
                        Promise.all(imgPromises).then(() => {
                            newItems.each(function (index) {
                                $(this).delay(index * 100).animate({ opacity: 1 }, 500);
                            });

                            // Initialize wow.js animations
                            new WOW().init();
                        });

                        // Update button data attribute to the next page
                        button.data('current-page', nextPage);

                        // Show or hide load more button based on whether there are more pages
                        if (response.hasMorePages) {
                            button.removeClass('d-none');
                        }
                    },
                    error: function (xhr, status, error) {
                        // Get detailed error message if available
                        let errorMessage = 'Error loading more posts';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                errorMessage = response.error;
                            }
                        } catch (e) {
                            errorMessage += ': ' + error;
                        }

                        console.error(errorMessage);

                        // Show button, hide spinner on error
                        spinner.addClass('d-none');
                        button.removeClass('d-none');
                    }
                });
            });
        });
    </script>
@endpush
@extends('site.layouts.app')

@section('title', 'Cental - Premium Car Rental Services | Book Your Dream Car Today')
@section('meta_description', 'Cental offers premium car rental services with a wide selection of luxury vehicles including Mercedes, BMW, Tesla, and more. Enjoy 24/7 support, free pick-up, and competitive rates.')
@section('meta_keywords', 'car rental, luxury car rental, premium cars, rent a car, cheap car rental, Mercedes Benz, Tesla, BMW, Toyota')

@section('og_title', 'Premium Car Rental Services | Cental')
@section('og_description', 'Choose from our fleet of luxury vehicles. Get 15% off your rental today with free pick-up and 24/7 road assistance.')
@section('og_image', asset('site/img/carousel-1.jpg'))


@section('content')

    @include('site.includes.head')
    <!-- Car categories Start -->
    <div class="container-fluid categories py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize mb-3">Vehicle <span class="text-primary">Categories</span></h1>
                <p class="mb-0">Explore our selection of premium vehicles for any occasion. From luxury sedans to spacious SUVs,
                    we have the perfect car for your needs with competitive rates and exceptional service.
                </p>
            </div>

            <!-- Filter Section -->
            <div class="row mb-5">
                <div class="col-lg-3 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Filter Options</h5>
                            <form id="filter-form">
                                <!-- Categories Filter -->
                                <div class="mb-4">
                                    <label class="form-label">Categories</label>
                                    <select class="form-select filter-input" name="category_id">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!--_brands Filter -->
                                <div class="mb-4">
                                    <label class="form-label">Brands</label>
                                    <select class="form-select filter-input" name="brand_id">
                                        <option value="">All_brands</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Price Range Filter -->
                                <div class="mb-4">
                                    <label class="form-label">Price Range</label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" class="form-control filter-input" name="price_min" placeholder="Min" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" value="{{ request('price_min', $priceRange['min']) }}">
                                        <span class="mx-2">-</span>
                                        <input type="number" class="form-control filter-input" name="price_max" placeholder="Max" min="{{ $priceRange['min'] }}" max="{{ $priceRange['max'] }}" value="{{ request('price_max', $priceRange['max']) }}">
                                    </div>
                                </div>

                                <!-- Transmission Filter -->
                                <div class="mb-4">
                                    <label class="form-label">Transmission</label>
                                    <select class="form-select filter-input" name="transmission">
                                        <option value="">All Transmissions</option>
                                        @foreach($transmissionTypes as $type)
                                            <option value="{{ $type }}" {{ request('transmission') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Fuel Type Filter -->
                                <div class="mb-4">
                                    <label class="form-label">Fuel Type</label>
                                    <select class="form-select filter-input" name="fuel_type">
                                        <option value="">All Fuel Types</option>
                                        @foreach($fuelTypes as $type)
                                            <option value="{{ $type }}" {{ request('fuel_type') == $type ? 'selected' : '' }}>
                                                {{ ucfirst($type) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sort By -->
                                <div class="mb-4">
                                    <label class="form-label">Sort By</label>
                                    <select class="form-select filter-input" name="sort_by">
                                        <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>Newest</option>
                                        <option value="price_per_day" {{ request('sort_by') == 'price_per_day' ? 'selected' : '' }}>Price</option>
                                        <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
                                    </select>
                                </div>

                                <!-- Sort Direction -->
                                <div class="mb-4">
                                    <label class="form-label">Sort Direction</label>
                                    <select class="form-select filter-input" name="sort_direction">
                                        <option value="asc" {{ request('sort_direction', 'desc') == 'asc' ? 'selected' : '' }}>Low to High</option>
                                        <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>High to Low</option>
                                    </select>
                                </div>

                                <button type="button" id="apply-filters" class="btn btn-primary w-100">Apply Filters</button>
                                <button type="button" id="reset-filters" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <!-- Cars results info -->
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div id="results-info">Loading vehicles...</div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                Display Options
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#" data-view="grid">Grid View</a></li>
                                <li><a class="dropdown-item" href="#" data-view="list">List View</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Cars Listing -->
                    <div id="car-listings" class="row g-4"></div>

                    <!-- Loading and Load More -->
                    <div class="text-center mt-5">
                        <input type="hidden" id="current-page" value="1">
                        <div id="loading-spinner" class="spinner-border text-primary mt-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <button id="load-more" class="btn btn-primary px-5 py-3 rounded d-none">Load More Cars</button>
                        <div id="end-of-results" class="mt-4 d-none">
                            <p class="text-muted">You've reached the end of results.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Car categories End -->

    <!-- Car Steps Start -->
    <div class="container-fluid steps py-5">
        <div class="container py-5">
            <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.1s" style="max-width: 800px;">
                <h1 class="display-5 text-capitalize text-white mb-3">Cental<span class="text-primary"> Process</span></h1>
                <p class="mb-0 text-white">Our simple 3-step process makes renting a car quick and hassle-free. 
                    Get on the road in no time with our efficient booking system.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Come In Contact</h4>
                        <p class="mb-0">Browse our selection and use our simple online booking system or call us directly.</p>
                        <div class="setps-number">01.</div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Choose A Car</h4>
                        <p class="mb-0">Select from our premium fleet of vehicles to match your needs and preferences.</p>
                        <div class="setps-number">02.</div>
                    </div>
                </div>
                <div class="col-lg-4 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="steps-item p-4 mb-4">
                        <h4>Enjoy Driving</h4>
                        <p class="mb-0">Pick up your car and enjoy the freedom to explore with our reliable vehicles.</p>
                        <div class="setps-number">03.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Car Steps End -->
    @include('site.includes.banner')
    <!-- Structured Data for Car Rental Service -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "CarRentalBusiness",
      "name": "Cental Car Rental",
      "image": "{{asset('site/img/carousel-1.jpg')}}",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "123 Main Street",
        "addressLocality": "New York",
        "addressRegion": "NY",
        "postalCode": "10001",
        "addressCountry": "US"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": "40.7128",
        "longitude": "-74.0060"
      },
      "url": "{{url('/')}}",
      "telephone": "+1-234-567-8901",
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
          "opens": "08:00",
          "closes": "18:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Saturday"],
          "opens": "09:00",
          "closes": "16:00"
        }
      ],
      "priceRange": "99 MAD - 187 MAD",
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.5",
        "reviewCount": "829"
      }
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "serviceType": "Car Rental",
        "provider": {
            "@type": "Organization",
            "name": "Cental Car Rental"
        },
        "areaServed": "USA",
        "offers": {
            "@type": "Offer",
            "priceCurrency": "MAD",
            "priceRange": "99 MAD - 187 MAD"
        }
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "Cental Car Rental",
      "image": "{{ asset('site/img/logo.png') }}",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "123 Main St",
        "addressLocality": "New York",
        "addressRegion": "NY",
        "postalCode": "10001",
        "addressCountry": "US"
      },
      "telephone": "+1-234-567-8901",
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
          "opens": "08:00",
          "closes": "18:00"
        },
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Saturday"],
          "opens": "09:00",
          "closes": "16:00"
        }
      ]
    }
    </script>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Define perPage constant that matches the controller
        const PER_PAGE = {{ $perPage }};
        
        // Current view (grid or list)
        let currentView = 'grid';
        
        // Load initial cars as soon as the page is ready
        loadCars(1, true);
        
        // Apply filters
        $('#apply-filters').on('click', function() {
            loadCars(1, true);
        });
        
        // Reset filters
        $('#reset-filters').on('click', function() {
            $('#filter-form')[0].reset();
            loadCars(1, true);
        });
        
        // Load more cars on button click
        $(document).on('click', '#load-more', function() {
            const nextPage = parseInt($('#current-page').val()) + 1;
            loadCars(nextPage, false);
        });
        
        // Toggle between grid and list view
        $('.dropdown-item').on('click', function(e) {
            e.preventDefault();
            currentView = $(this).data('view');
            
            if (currentView === 'grid') {
                $('#car-listings').removeClass('list-view').addClass('grid-view');
            } else {
                $('#car-listings').removeClass('grid-view').addClass('list-view');
            }
        });
        
        // Function to load cars via AJAX
        function loadCars(page, resetListing) {
            // Show loading spinner, hide load more and end of results
            $('#loading-spinner').removeClass('d-none');
            $('#load-more').addClass('d-none');
            $('#end-of-results').addClass('d-none');
            
            // Get filter form data and serialize it
            const formData = $('#filter-form').serialize() + '&page=' + page;
            
            // Make AJAX request
            $.ajax({
                url: '{{ route("cars.loadCars") }}',
                type: 'GET',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    // Hide loading spinner
                    $('#loading-spinner').addClass('d-none');
                    
                    // Update results info
                    $('#results-info').html(`Showing ${Math.min(response.totalCars, (page-1)*PER_PAGE+1)}-${Math.min(response.totalCars, page*PER_PAGE)} of ${response.totalCars} vehicles`);
                    
                    // Update car listings
                    if (resetListing) {
                        $('#car-listings').html(response.html);
                        $('#current-page').val(1);
                    } else {
                        $('#car-listings').append(response.html);
                        $('#current-page').val(page);
                    }
                    
                    // Show/hide load more button or end of results message
                    if (response.hasMorePages) {
                        $('#load-more').removeClass('d-none');
                    } else {
                        $('#end-of-results').removeClass('d-none');
                    }
                    
                    // Apply current view
                    if (currentView === 'list') {
                        $('#car-listings').removeClass('grid-view').addClass('list-view');
                    } else {
                        $('#car-listings').removeClass('list-view').addClass('grid-view');
                    }
                    
                    // Reinitialize WOW animations for newly added elements
                    if (typeof WOW !== 'undefined') {
                        new WOW().init();
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading spinner
                    $('#loading-spinner').addClass('d-none');
                    
                    // Show error message
                    console.error('Error loading cars:', error);
                    console.log(xhr.responseText);
                    $('#car-listings').html(`
                        <div class="col-12 text-center py-5">
                            <div class="alert alert-danger">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                An error occurred while loading cars. Please try again.
                            </div>
                            <button class="btn btn-primary mt-3" onclick="location.reload()">Reload Page</button>
                        </div>
                    `);
                }
            });
        }
    });
</script>
@endpush
@push('styles')
<style>
    /* Grid view (default) */
    #car-listings.grid-view {
        display: flex;
        flex-wrap: wrap;
    }
    
    #car-listings.grid-view > div {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
    
    /* List view */
    #car-listings.list-view > div {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    #car-listings.list-view .categories-item-inner {
        display: flex;
        flex-direction: row !important;
    }
    
    #car-listings.list-view .categories-img {
        flex: 0 0 40%;
        max-width: 40%;
    }
    
    #car-listings.list-view .categories-content {
        flex: 0 0 60%;
        max-width: 60%;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        #car-listings.grid-view > div {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }
    
    @media (max-width: 768px) {
        #car-listings.grid-view > div {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        #car-listings.list-view .categories-item-inner {
            flex-direction: column !important;
        }
        
        #car-listings.list-view .categories-img,
        #car-listings.list-view .categories-content {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>
@endpush
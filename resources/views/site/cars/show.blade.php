@extends('site.layouts.app')

@section('title', $car->meta_title ?? $car->name . ' - Premium Car Rental Services | Cental')
@section('meta_description', $car->meta_description ?? 'Rent the ' . $car->name . ' from Cental. ' . $car->seats . ' seats, ' . ucfirst($car->transmission) . ' transmission, ' . ucfirst($car->fuel_type) . ' engine.')
@section('meta_keywords', $car->meta_keywords ?? 'car rental, ' . $car->name . ', ' . $car->brand->name . ', ' . $car->category->name . ', luxury car rental')

@section('og_title', $car->name . ' - Premium Car Rental | Cental')
@section('og_description', 'Rent the ' . $car->name . number_format($car->price_per_day, 2) . 'MAD' . ' per day. ' . $car->seats . ' seats, ' . ucfirst($car->transmission) . ' transmission.')
@section('og_image', Storage::url($car->main_image))

@section('content')
    @include('site.includes.head')

    <!-- Car Detail Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <!-- Car Images and Basic Details -->
                <div class="col-lg-8 col-xl-9">
                    <div class="car-detail-wrapper">
                        <!-- Car Images -->
                        <div class="car-images mb-5">
                            <div class="car-main-image mb-4">
                                <img src="{{ Storage::url($car->main_image) }}" class="img-fluid w-100 rounded"
                                    alt="{{ $car->name }}">
                            </div>
                            @if($car->images->count() > 0)
                                <div class="car-gallery row g-2">
                                    @foreach($car->images as $image)
                                        <div class="col-4 col-md-3 col-lg-2">
                                            <img src="{{ asset($image->image_path) }}" alt="{{ $car->name }}"
                                                class="img-fluid w-100 rounded car-gallery-item"
                                                onclick="showFullImage('{{ asset($image->image_path) }}')">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Car Basic Details -->
                        <div class="car-basic-details mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h1 class="display-5 text-capitalize mb-0">{{ $car->name }}</h1>
                                <div class="car-rating d-flex align-items-center">
                                    <div class="me-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($car->rating))
                                                <i class="fas fa-star text-primary"></i>
                                            @else
                                                <i class="far fa-star text-primary"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-muted">({{ $car->review_count }} reviews)</span>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-car text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Brand</h5>
                                            <p class="mb-0">{{ $car->brand->name }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-tag text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Category</h5>
                                            <p class="mb-0">{{ $car->category->name }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-users text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Seats</h5>
                                            <p class="mb-0">{{ $car->seats }} seats</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-gas-pump text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Fuel Type</h5>
                                            <p class="mb-0">{{ ucfirst($car->fuel_type) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-cogs text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Transmission</h5>
                                            <p class="mb-0">{{ ucfirst($car->transmission) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="fas fa-tachometer-alt text-primary fa-2x me-3"></i>
                                        <div>
                                            <h5 class="mb-0">Engine</h5>
                                            <p class="mb-0">{{ $car->engine_capacity ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Car Description -->
                        <div class="car-description mb-5">
                            <h3 class="mb-4">Description</h3>
                            <div class="car-description-content">
                                {!! $car->description !!}
                            </div>
                        </div>

                        <!-- Car Features -->
                        <div class="car-features mb-5">
                            <h3 class="mb-4">Features</h3>
                            <div class="row g-4">
                                @if(is_array($car->features) && count($car->features) > 0)
                                    @foreach($car->features as $feature)
                                        <div class="col-md-4 col-sm-6">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check text-primary me-2"></i>
                                                <span>{{ ucfirst($feature) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <p class="text-muted">No additional features listed for this vehicle.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Customer Reviews -->
                        <div class="car-reviews mb-5">
                            <h3 class="mb-4">Customer Reviews</h3>
                            @if($car->reviews->count() > 0)
                                <div class="car-reviews-list">
                                    @foreach($car->reviews as $review)
                                        <div class="car-review-item mb-4 p-4 bg-light rounded">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="review-avatar me-3">
                                                    <img src="{{ $review->user && $review->user->avatar ? asset($review->user->avatar) : asset('site/img/avatar-placeholder.jpg') }}"
                                                        class="rounded-circle" alt="{{ $review->name }}" width="50" height="50">
                                                </div>
                                                <div class="review-author">
                                                    <h5 class="mb-1">{{ $review->name }}</h5>
                                                    <div class="d-flex">
                                                        <div class="me-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $review->rating)
                                                                    <i class="fas fa-star text-primary"></i>
                                                                @else
                                                                    <i class="far fa-star text-primary"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="review-content">
                                                <p class="mb-0">{{ $review->review }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No reviews yet for this vehicle. Be the first to leave a review!
                                </div>
                            @endif

                            <!-- Write a Review Form -->
                            <div class="write-review-form mt-5" id="write-review">
                                <h4 class="mb-4">Write a Review</h4>
                                <form action="{{ route('reviews.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="car_id" value="{{ $car->id }}">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="name" name="name"
                                                    placeholder="Your Name">
                                                <label for="name">Your Name</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-floating">
                                                <input type="email" class="form-control" id="email" name="email"
                                                    placeholder="Your Email">
                                                <label for="email">Your Email</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="rating-select mb-3">
                                                <label class="form-label">Rating</label>
                                                <div class="d-flex">
                                                    @for($i = 5; $i >= 1; $i--)
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="rating"
                                                                id="rating{{ $i }}" value="{{ $i }}">
                                                            <label class="form-check-label"
                                                                for="rating{{ $i }}">{{ $i }}</label>
                                                        </div>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-floating">
                                                <textarea class="form-control" placeholder="Special Review" id="review"
                                                    name="review" style="height: 150px"></textarea>
                                                <label for="review">Your Review</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary py-3 px-5" type="submit">Submit Review</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar - Booking and Related Cars -->
                <div class="col-lg-4 col-xl-3">
                    <!-- Booking Sidebar -->
                    <div class="booking-card card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="price-section mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="mb-0">Rental Price</h4>
                                    <span
                                        class="badge bg-primary p-2">{{ $car->discount_percentage > 0 ? $car->discount_percentage . '% OFF' : 'Best Price' }}</span>
                                </div>
                                <div class="d-flex align-items-end">
                                    <h2 class="text-primary mb-0">
                                        {{ number_format($car->price_per_day * (1 - $car->discount_percentage / 100), 2) }}
                                        MAD
                                    </h2>
                                    <span class="text-muted ms-2">/day</span>
                                    @if($car->discount_percentage > 0)
                                        <span
                                            class="text-muted text-decoration-line-through ms-3">{{ number_format($car->price_per_day, 2) }}
                                            MAD</span>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('bookings.store', $car->slug) }}" method="POST" id="bookingForm">
                                @csrf
                                <input type="hidden" name="car_id" value="{{ $car->id }}">
                                <input type="hidden" name="start_mileage" value="{{ $car->current_mileage ?? 0 }}">
                                <div class="mb-3">
                                    <label for="pickup_date" class="form-label">Pickup Date</label>
                                    <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                                        min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pickup_location" class="form-label">Pickup Location</label>
                                    <select class="form-select" id="pickup_location" name="pickup_location" required>
                                        <option value="">Select location</option>
                                        <optgroup label="Grandes villes">
                                            <option value="Casablanca">Casablanca</option>
                                            <option value="Rabat">Rabat</option>
                                            <option value="Marrakech">Marrakech</option>
                                            <option value="Fès">Fès</option>
                                            <option value="Tanger">Tanger</option>
                                            <option value="Agadir">Agadir</option>
                                        </optgroup>
                                        <optgroup label="Aéroports">
                                            <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                (Casablanca)</option>
                                            <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara</option>
                                            <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira</option>
                                            <option value="Aéroport Fès-Saïs">Aéroport Fès-Saïs</option>
                                            <option value="Aéroport Rabat-Salé">Aéroport Rabat-Salé</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="dropoff_date" class="form-label">Dropoff Date</label>
                                    <input type="date" class="form-control" id="dropoff_date" name="dropoff_date"
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dropoff_location" class="form-label">Dropoff Location</label>
                                    <select class="form-select" id="dropoff_location" name="dropoff_location" required>
                                        <option value="">Select location</option>
                                        <optgroup label="Grandes villes">
                                            <option value="Casablanca">Casablanca</option>
                                            <option value="Rabat">Rabat</option>
                                            <option value="Marrakech">Marrakech</option>
                                            <option value="Fès">Fès</option>
                                            <option value="Tanger">Tanger</option>
                                            <option value="Agadir">Agadir</option>
                                        </optgroup>
                                        <optgroup label="Aéroports">
                                            <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                (Casablanca)</option>
                                            <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara</option>
                                            <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira</option>
                                            <option value="Aéroport Fès-Saïs">Aéroport Fès-Saïs</option>
                                            <option value="Aéroport Rabat-Salé">Aéroport Rabat-Salé</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="rental-summary mb-4 d-none" id="rental-summary">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="mb-3">Rental Summary</h5>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Duration:</span>
                                                <span id="rental-days">0 days</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Rate per day:</span>
                                                <span>{{ number_format($car->price_per_day * (1 - $car->discount_percentage / 100), 2) }}
                                                    MAD</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span id="rental-subtotal">0.00 MAD</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total:</span>
                                                <span id="rental-total">0.00 MAD</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-3">Book Now</button>
                            </form>
                        </div>
                    </div>

                    <!-- Related Cars -->
                    @if($relatedCars->count() > 0)
                        <div class="related-cars card border-0 shadow-sm">
                            <div class="card-body">
                                <h4 class="mb-4">Similar Cars</h4>
                                <div class="related-cars-list">
                                    @foreach($relatedCars as $relatedCar)
                                        <div class="related-car-item mb-3">
                                            <a href="{{ route('cars.show', $relatedCar->slug) }}" class="text-decoration-none">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0" style="width: 80px; height: 60px;">
                                                        <img src="{{ Storage::url($relatedCar->main_image) }}"
                                                            alt="{{ $relatedCar->name }}"
                                                            class="img-fluid rounded object-fit-cover w-100 h-100">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-1 text-dark">{{ $relatedCar->name }}</h6>
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div class="me-2">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    @if($i <= round($relatedCar->rating))
                                                                        <i class="fas fa-star text-primary" style="font-size: 10px;"></i>
                                                                    @else
                                                                        <i class="far fa-star text-primary" style="font-size: 10px;"></i>
                                                                    @endif
                                                                @endfor
                                                            </div>
                                                            <small class="text-muted">({{ $relatedCar->review_count }})</small>
                                                        </div>
                                                        <div class="text-primary">
                                                            {{ number_format($relatedCar->price_per_day * (1 - $relatedCar->discount_percentage / 100), 2) }}
                                                            MAD/day
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Car Detail End -->

    <!-- Full Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ $car->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <img id="fullImage" src="" class="img-fluid w-100" alt="{{ $car->name }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Structured Data for Car -->
    <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "Product",
                        "name": "{{ $car->name }}",
                        "image": "{{ Storage::url($car->main_image) }}",
                        "description": "{{ strip_tags($car->description) }}",
                        "brand": {
                            "@type": "Brand",
                            "name": "{{ $car->brand->name }}"
                        },
                        "category": "{{ $car->category->name }}",
                        "offers": {
                            "@type": "Offer",
                            "price": "{{ $car->price_per_day * (1 - $car->discount_percentage / 100) }}",
                            "priceCurrency": "MAD",
                            "availability": "{{ $car->is_available ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}"
                        },
                        "aggregateRating": {
                            "@type": "AggregateRating",
                            "ratingValue": "{{ $car->rating }}",
                            "reviewCount": "{{ $car->review_count }}"
                        }
                    }
                </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize date inputs with today and tomorrow
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const pickupDateInput = document.getElementById('pickup_date');
            const dropoffDateInput = document.getElementById('dropoff_date');

            pickupDateInput.value = formatDate(today);
            dropoffDateInput.value = formatDate(tomorrow);

            // Show rental summary
            updateRentalSummary();

            // Add event listeners
            pickupDateInput.addEventListener('change', function () {
                // Ensure dropoff date is after pickup date
                const pickupDate = new Date(this.value);
                const dropoffDate = new Date(dropoffDateInput.value);

                if (dropoffDate <= pickupDate) {
                    const newDropoffDate = new Date(pickupDate);
                    newDropoffDate.setDate(newDropoffDate.getDate() + 1);
                    dropoffDateInput.value = formatDate(newDropoffDate);
                }

                updateRentalSummary();
            });

            dropoffDateInput.addEventListener('change', updateRentalSummary);

            // Handle booking form submission
            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm) {
                bookingForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;

                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Processing...
                                `;

                    try {
                        const formData = new FormData(bookingForm);
                        const response = await fetch(bookingForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                title: 'Booking Successful!',
                                html: `
                                                <div class="text-center">
                                                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 5rem;"></i>
                                                    <p>${data.message}</p>
                                                    <p class="fw-bold">Booking Number: ${data.booking_number}</p>
                                                </div>
                                            `,
                                icon: 'success',
                                confirmButtonText: 'OK',
                                willClose: () => {
                                    // Optional: Redirect to booking confirmation page
                                    // window.location.href = `/bookings/${data.booking_number}/thankyou`;
                                }
                            });

                            // Reset form
                            bookingForm.reset();
                            pickupDateInput.value = formatDate(today);
                            dropoffDateInput.value = formatDate(tomorrow);
                            updateRentalSummary();
                        } else {
                            // Handle validation errors
                            let errorMessage = data.message;

                            if (data.errors) {
                                errorMessage = '<ul class="text-start">';
                                for (const field in data.errors) {
                                    errorMessage += `<li>${data.errors[field][0]}</li>`;
                                }
                                errorMessage += '</ul>';
                            }

                            Swal.fire({
                                title: 'Booking Failed',
                                html: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        console.error('Booking error:', error);
                    } finally {
                        // Reset button state
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            }

            // Function to update rental summary
            function updateRentalSummary() {
                const pickupDate = new Date(pickupDateInput.value);
                const dropoffDate = new Date(dropoffDateInput.value);

                // Calculate number of days
                const timeDiff = Math.abs(dropoffDate - pickupDate);
                const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                if (days > 0) {
                    // Calculate costs
                    const ratePerDay = {{ $car->price_per_day * (1 - $car->discount_percentage / 100) }};
                    const subtotal = days * ratePerDay;

                    // Update the DOM
                    document.getElementById('rental-days').textContent = days + ' day' + (days > 1 ? 's' : '');
                    document.getElementById('rental-subtotal').textContent = subtotal.toFixed(2) + ' MAD';
                    document一方面

                    // Show the summary
                    document.getElementById('rental-summary').classList.remove('d-none');
                }
            }

            // Format date as YYYY-MM-DD
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
        });

        // Function to show full image in modal
        function showFullImage(imageSrc) {
            document.getElementById('fullImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    </script>
@endpush

@push('styles')
    <style>
        /* Car detail page specific styles */
        .car-gallery-item {
            cursor: pointer;
            transition: all 0.3s;
        }

        .car-gallery-item:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }

        .booking-card {
            position: sticky;
            top: 100px;
        }

        .related-car-item {
            transition: all 0.3s;
            border-radius: 10px;
            padding: 10px;
        }

        .related-car-item:hover {
            background-color: var(--bs-light);
        }

        .car-review-item {
            transition: all 0.3s;
        }

        .car-review-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .review-avatar img {
            object-fit: cover;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .booking-card {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
@endpush
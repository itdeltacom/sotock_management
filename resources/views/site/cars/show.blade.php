@extends('site.layouts.app')

@section('title', $car->meta_title ?? $car->name . ' - Premium Car Rental Services | Cental')
@section('meta_description', $car->meta_description ?? 'Rent the ' . $car->name . ' from Cental. ' . $car->seats . ' seats, ' . ucfirst($car->transmission) . ' transmission, ' . ucfirst($car->fuel_type) . ' engine.')
@section('meta_keywords', $car->meta_keywords ?? 'car rental, ' . $car->name . ', ' . $car->brand->name . ', ' . $car->category->name . ', luxury car rental')

@section('og_title', $car->name . ' - Premium Car Rental | Cental')
@section('og_description', 'Rent the ' . $car->name . ' for ' . number_format($car->price_per_day, 2) . ' MAD per day. ' . $car->seats . ' seats, ' . ucfirst($car->transmission) . ' transmission.')
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
                                            volumizing<h5 class="mb-0">Brand</h5>
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

                            <!-- Booking Form -->
                            <div class="write-review-form mt-5" id="write-review">
                                <h4 class="mb-4">Book This Car</h4>
                                <form action="{{ route('bookings.store', $car->slug) }}" method="POST" id="bookingForm">
                                    @csrf
                                    <input type="hidden" name="car_id" value="{{ $car->id }}">
                                    <input type="hidden" name="start_mileage" value="{{ (int) ($car->mileage ?? 0) }}">

                                    <!-- Customer Information -->
                                    <div class="mb-4">
                                        <h5>Customer Information</h5>
                                        @if(auth()->check())
                                            <div class="alert alert-info">
                                                <p>Using your account details. Please confirm or edit below:</p>
                                                <div class="mb-3">
                                                    <label for="customer_name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="customer_name"
                                                        name="customer_name" value="{{ auth()->user()->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="customer_email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="customer_email"
                                                        name="customer_email" value="{{ auth()->user()->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="customer_phone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="customer_phone"
                                                        name="customer_phone" value="{{ auth()->user()->phone ?? '' }}"
                                                        required>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <label for="customer_name" class="form-label">Name</label>
                                                <input type="text" class="form-control" id="customer_name" name="customer_name"
                                                    required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="customer_email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="customer_email"
                                                    name="customer_email" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="customer_phone" class="form-label">Phone</label>
                                                <input type="text" class="form-control" id="customer_phone"
                                                    name="customer_phone" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="customer_id_number" class="form-label">Moroccan ID (CIN or
                                                    Passport)</label>
                                                <input type="text" class="form-control" id="customer_id_number"
                                                    name="customer_id_number">
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="register_account"
                                                    id="register_account" value="1">
                                                <label class="form-check-label" for="register_account">
                                                    Create an account with this information
                                                </label>
                                                <!-- Add a hidden field to ensure register_account is always sent -->
                                                <input type="hidden" name="register_account" value="0">
                                            </div>
                                            <div class="mb-3 register-fields d-none">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" name="password">
                                            </div>
                                            <div class="mb-3 register-fields d-none">
                                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" id="password_confirmation"
                                                    name="password_confirmation">
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Booking Details -->
                                    <div class="mb-3">
                                        <label for="pickup_date" class="form-label">Pickup Date</label>
                                        <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                                            min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="pickup_time" class="form-label">Pickup Time</label>
                                        <input type="time" class="form-control" id="pickup_time" name="pickup_time"
                                            value="10:00" required>
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
                                                <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira
                                                </option>
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
                                        <label for="dropoff_time" class="form-label">Dropoff Time</label>
                                        <input type="time" class="form-control" id="dropoff_time" name="dropoff_time"
                                            value="10:00" required>
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
                                                <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira
                                                </option>
                                                <option value="Aéroport Fès-Saïs">Aéroport Fès-Saïs</option>
                                                <option value="Aéroport Rabat-Salé">Aéroport Rabat-Salé</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- Insurance Plan -->
                                    <div class="mb-3">
                                        <label class="form-label">Insurance Plan</label>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="insurance_plan"
                                                    id="insurance_basic" value="basic" checked>
                                                <label class="form-check-label" for="insurance_basic">
                                                    <strong>Basic</strong> - Standard coverage (included)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="insurance_plan"
                                                    id="insurance_standard" value="standard">
                                                <label class="form-check-label" for="insurance_standard">
                                                    <strong>Standard</strong> - Extended coverage
                                                    (+{{ config('booking.insurance.standard', 50) }} MAD/day)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="insurance_plan"
                                                    id="insurance_premium" value="premium">
                                                <label class="form-check-label" for="insurance_premium">
                                                    <strong>Premium</strong> - Full protection
                                                    (+{{ config('booking.insurance.premium', 100) }} MAD/day)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Method -->
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method</label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="">Select payment method</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="cash_on_delivery">Cash on Delivery</option>
                                        </select>
                                    </div>

                                    <!-- Additional Options -->
                                    <div class="mb-3">
                                        <label class="form-label">Additional Options</label>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="additional_driver"
                                                    id="additional_driver" value="1">
                                                <label class="form-check-label" for="additional_driver">
                                                    Additional Driver (+{{ config('booking.additional_driver_fee', 30) }}
                                                    MAD)
                                                </label>
                                            </div>
                                            <div class="mb-3 additional-driver-fields d-none">
                                                <label for="additional_driver_name" class="form-label">Additional Driver
                                                    Name</label>
                                                <input type="text" class="form-control" id="additional_driver_name"
                                                    name="additional_driver_name">
                                            </div>
                                            <div class="mb-3 additional-driver-fields d-none">
                                                <label for="additional_driver_license" class="form-label">Additional Driver
                                                    License</label>
                                                <input type="text" class="form-control" id="additional_driver_license"
                                                    name="additional_driver_license">
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="gps_enabled"
                                                    id="gps_enabled" value="1">
                                                <label class="form-check-label" for="gps_enabled">
                                                    GPS Navigation (+{{ config('booking.gps_fee', 20) }} MAD)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="child_seat"
                                                    id="child_seat" value="1">
                                                <label class="form-check-label" for="child_seat">
                                                    Child Seat (+{{ config('booking.child_seat_fee', 15) }} MAD)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delivery Options -->
                                    <div class="mb-3">
                                        <label class="form-label">Delivery Option</label>
                                        <select class="form-select" id="delivery_option" name="delivery_option">
                                            <option value="none" selected>No Delivery</option>
                                            <option value="home">Home Delivery</option>
                                            <option value="airport">Airport Delivery</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 delivery-address-field d-none">
                                        <label for="delivery_address" class="form-label">Delivery Address</label>
                                        <input type="text" class="form-control" id="delivery_address"
                                            name="delivery_address">
                                    </div>

                                    <!-- Language Preference -->
                                    <div class="mb-3">
                                        <label for="language_preference" class="form-label">Language Preference</label>
                                        <select class="form-select" id="language_preference" name="language_preference">
                                            <option value="fr" selected>French</option>
                                            <option value="ar">Arabic</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>

                                    <!-- Special Requests -->
                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests"
                                            rows="4"></textarea>
                                    </div>

                                    <!-- Terms and Conditions -->
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="terms_accepted"
                                                id="terms_accepted" value="1" required>
                                            <label class="form-check-label" for="terms_accepted">
                                                I have read and agree to the <a href="{{ route('policy') }}"
                                                    target="_blank">Terms and Conditions</a>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Rental Summary -->
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
                                                <div class="d-flex justify-content-between mb-2 d-none"
                                                    id="insurance-cost-row">
                                                    <span>Insurance:</span>
                                                    <span id="insurance-cost">0.00 MAD</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2 d-none"
                                                    id="extras-cost-row">
                                                    <span>Extra options:</span>
                                                    <span id="extras-cost">0.00 MAD</span>
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

                            <form action="{{ route('bookings.store', $car->slug) }}" method="POST" id="bookingFormSidebar">
                                @csrf
                                <input type="hidden" name="car_id" value="{{ $car->id }}">
                                <input type="hidden" name="start_mileage" value="{{ (int) ($car->mileage ?? 0) }}">
                                <div class="mb-3">
                                    <label for="pickup_date_sidebar" class="form-label">Pickup Date</label>
                                    <input type="date" class="form-control" id="pickup_date_sidebar" name="pickup_date"
                                        min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pickup_time_sidebar" class="form-label">Pickup Time</label>
                                    <input type="time" class="form-control" id="pickup_time_sidebar" name="pickup_time"
                                        value="10:00" required>
                                </div>
                                <div class="mb-3">
                                    <label for="pickup_location_sidebar" class="form-label">Pickup Location</label>
                                    <select class="form-select" id="pickup_location_sidebar" name="pickup_location"
                                        required>
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
                                    <label for="dropoff_date_sidebar" class="form-label">Dropoff Date</label>
                                    <input type="date" class="form-control" id="dropoff_date_sidebar" name="dropoff_date"
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dropoff_time_sidebar" class="form-label">Dropoff Time</label>
                                    <input type="time" class="form-control" id="dropoff_time_sidebar" name="dropoff_time"
                                        value="10:00" required>
                                </div>
                                <div class="mb-3">
                                    <label for="dropoff_location_sidebar" class="form-label">Dropoff Location</label>
                                    <select class="form-select" id="dropoff_location_sidebar" name="dropoff_location"
                                        required>
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
                                <div class="rental-summary mb-4 d-none" id="rental-summary-sidebar">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h5 class="mb-3">Rental Summary</h5>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Duration:</span>
                                                <span id="rental-days-sidebar">0 days</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Rate per day:</span>
                                                <span>{{ number_format($car->price_per_day * (1 - $car->discount_percentage / 100), 2) }}
                                                    MAD</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span id="rental-subtotal-sidebar">0.00 MAD</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total:</span>
                                                <span id="rental-total-sidebar">0.00 MAD</span>
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
        /**
     * Car Detail Page JavaScript
     * This script handles booking form, date validation, price calculation,
     * and UI interactions
     */
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize date inputs with today and tomorrow
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            // Main form elements
            const pickupDateInput = document.getElementById('pickup_date');
            const dropoffDateInput = document.getElementById('dropoff_date');
            const pickupTimeInput = document.getElementById('pickup_time');
            const dropoffTimeInput = document.getElementById('dropoff_time');

            // Sidebar form elements
            const pickupDateSidebar = document.getElementById('pickup_date_sidebar');
            const dropoffDateSidebar = document.getElementById('dropoff_date_sidebar');
            const pickupTimeSidebar = document.getElementById('pickup_time_sidebar');
            const dropoffTimeSidebar = document.getElementById('dropoff_time_sidebar');

            // Set initial dates
            if (pickupDateInput) pickupDateInput.value = formatDate(today);
            if (dropoffDateInput) dropoffDateInput.value = formatDate(tomorrow);
            if (pickupDateSidebar) pickupDateSidebar.value = formatDate(today);
            if (dropoffDateSidebar) dropoffDateSidebar.value = formatDate(tomorrow);

            // Update rental summary on page load
            updateRentalSummary();
            updateSidebarRentalSummary();

            // Add event listeners for date changes
            if (pickupDateInput) {
                pickupDateInput.addEventListener('change', function () {
                    const pickupDate = new Date(this.value);
                    const dropoffDate = new Date(dropoffDateInput.value);

                    if (dropoffDate <= pickupDate) {
                        const newDropoffDate = new Date(pickupDate);
                        newDropoffDate.setDate(newDropoffDate.getDate() + 1);
                        dropoffDateInput.value = formatDate(newDropoffDate);
                    }

                    updateRentalSummary();
                });
            }

            if (dropoffDateInput) {
                dropoffDateInput.addEventListener('change', updateRentalSummary);
            }

            // Sidebar date inputs
            if (pickupDateSidebar) {
                pickupDateSidebar.addEventListener('change', function () {
                    const pickupDate = new Date(this.value);
                    const dropoffDate = new Date(dropoffDateSidebar.value);

                    if (dropoffDate <= pickupDate) {
                        const newDropoffDate = new Date(pickupDate);
                        newDropoffDate.setDate(newDropoffDate.getDate() + 1);
                        dropoffDateSidebar.value = formatDate(newDropoffDate);
                    }

                    updateSidebarRentalSummary();
                });
            }

            if (dropoffDateSidebar) {
                dropoffDateSidebar.addEventListener('change', updateSidebarRentalSummary);
            }

            // Insurance radio buttons
            document.querySelectorAll('input[name="insurance_plan"]').forEach(input => {
                input.addEventListener('change', updateRentalSummary);
            });

            // Additional driver checkbox
            const additionalDriver = document.getElementById('additional_driver');
            if (additionalDriver) {
                additionalDriver.addEventListener('change', function () {
                    const fields = document.querySelectorAll('.additional-driver-fields');
                    fields.forEach(field => {
                        field.classList.toggle('d-none', !this.checked);
                        const inputFields = field.querySelectorAll('input');
                        inputFields.forEach(input => {
                            input.required = this.checked;
                        });
                    });
                    updateRentalSummary();
                });
            }

            // GPS and child seat checkboxes
            const gpsEnabled = document.getElementById('gps_enabled');
            if (gpsEnabled) {
                gpsEnabled.addEventListener('change', updateRentalSummary);
            }

            const childSeat = document.getElementById('child_seat');
            if (childSeat) {
                childSeat.addEventListener('change', updateRentalSummary);
            }

            // Delivery option dropdown
            const deliveryOption = document.getElementById('delivery_option');
            const deliveryAddressField = document.querySelector('.delivery-address-field');
            if (deliveryOption && deliveryAddressField) {
                deliveryOption.addEventListener('change', function () {
                    const showAddress = this.value === 'home' || this.value === 'airport';
                    deliveryAddressField.classList.toggle('d-none', !showAddress);
                    const addressInput = deliveryAddressField.querySelector('input');
                    if (addressInput) {
                        addressInput.required = showAddress;
                        if (!showAddress) {
                            addressInput.value = '';
                        }
                    }
                });
            }

            // Register account checkbox
            const registerAccountCheckbox = document.getElementById('register_account');
            if (registerAccountCheckbox) {
                registerAccountCheckbox.addEventListener('change', function () {
                    const registerFields = document.querySelectorAll('.register-fields');
                    registerFields.forEach(field => {
                        field.classList.toggle('d-none', !this.checked);
                        const inputs = field.querySelectorAll('input');
                        inputs.forEach(input => {
                            input.required = this.checked;
                            if (!this.checked) {
                                input.value = ''; // Clear the fields when unchecked
                            }
                        });
                    });
                });
            }

            // Add hidden input for register_account to ensure it's always sent
            if (registerAccountCheckbox) {
                // Create a small utility function to ensure we have the hidden input
                const ensureHiddenRegisterInput = () => {
                    const form = registerAccountCheckbox.closest('form');
                    let hiddenInput = form.querySelector('input[type="hidden"][name="register_account"]');

                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'register_account';
                        hiddenInput.value = '0';
                        form.appendChild(hiddenInput);
                    }

                    // Update the main checkbox behavior
                    registerAccountCheckbox.addEventListener('change', function () {
                        hiddenInput.disabled = this.checked;
                    });
                };

                ensureHiddenRegisterInput();
            }

            // Form validation and submission - Main booking form
            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm) {
                bookingForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    // Client-side validation
                    if (!bookingForm.checkValidity()) {
                        bookingForm.reportValidity();
                        return;
                    }

                    const submitBtn = bookingForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Processing...
                `;

                    try {
                        const formData = new FormData(bookingForm);

                        // Ensure start_mileage is an integer
                        formData.set('start_mileage', parseInt(formData.get('start_mileage')));

                        // Handle register_account checkbox properly
                        const registerAccount = document.getElementById('register_account');

                        // IMPORTANT: Make sure registerAccount is explicitly set as a string "0" or "1"
                        if (registerAccount) {
                            if (registerAccount.checked) {
                                formData.set('register_account', '1');

                                // Validate password fields if they're required
                                const password = document.getElementById('password');
                                const passwordConfirmation = document.getElementById('password_confirmation');

                                if (!password.value) {
                                    Swal.fire({
                                        title: 'Password Required',
                                        text: 'Please enter a password for your new account',
                                        icon: 'warning',
                                        confirmButtonText: 'OK'
                                    });
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalBtnText;
                                    return;
                                }

                                if (password.value !== passwordConfirmation.value) {
                                    Swal.fire({
                                        title: 'Passwords Do Not Match',
                                        text: 'Please make sure your passwords match',
                                        icon: 'warning',
                                        confirmButtonText: 'OK'
                                    });
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalBtnText;
                                    return;
                                }
                            } else {
                                // If unchecked, explicitly set to "0" (not false or undefined)
                                formData.set('register_account', '0');

                                // Remove password fields if register_account is not checked
                                formData.delete('password');
                                formData.delete('password_confirmation');
                            }
                        }

                        // Handle boolean checkboxes - ensure they're always sent as "0" or "1"
                        const booleanFields = ['additional_driver', 'gps_enabled', 'child_seat', 'terms_accepted'];
                        booleanFields.forEach(field => {
                            const checkbox = document.getElementById(field);
                            if (checkbox) {
                                formData.set(field, checkbox.checked ? '1' : '0');
                            }
                        });

                        // Debug log the form data being sent
                        console.log('Form submission data:');
                        for (let [key, value] of formData.entries()) {
                            console.log(`${key}: ${value}`);
                        }

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
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset all forms after success
                                bookingForm.reset();

                                // Initialize date fields again
                                if (pickupDateInput) pickupDateInput.value = formatDate(today);
                                if (dropoffDateInput) dropoffDateInput.value = formatDate(tomorrow);

                                // Reset sidebar form if it exists
                                const sidebarForm = document.getElementById('bookingFormSidebar');
                                if (sidebarForm) {
                                    sidebarForm.reset();
                                    if (pickupDateSidebar) pickupDateSidebar.value = formatDate(today);
                                    if (dropoffDateSidebar) dropoffDateSidebar.value = formatDate(tomorrow);
                                }

                                // Reset UI elements
                                resetUIElements();
                            });
                        } else {
                            let errorMessage = data.message || 'Please correct the following errors:';
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
                        console.error('Booking error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again later.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            }

            // Sidebar form submission
            const sidebarForm = document.getElementById('bookingFormSidebar');
            if (sidebarForm) {
                sidebarForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    // Client-side validation
                    if (!sidebarForm.checkValidity()) {
                        sidebarForm.reportValidity();
                        return;
                    }

                    const submitBtn = sidebarForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Processing...
                `;

                    try {
                        const formData = new FormData(sidebarForm);

                        // Ensure start_mileage is an integer
                        formData.set('start_mileage', parseInt(formData.get('start_mileage')));

                        // Add required fields not in the sidebar form
                        formData.set('insurance_plan', 'basic');
                        formData.set('payment_method', 'cash_on_delivery');
                        formData.set('terms_accepted', '1');
                        formData.set('register_account', '0'); // No account creation from sidebar form

                        const response = await fetch(sidebarForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
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
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset all forms after success
                                sidebarForm.reset();
                                if (bookingForm) bookingForm.reset();

                                // Initialize date fields again
                                if (pickupDateSidebar) pickupDateSidebar.value = formatDate(today);
                                if (dropoffDateSidebar) dropoffDateSidebar.value = formatDate(tomorrow);
                                if (pickupDateInput) pickupDateInput.value = formatDate(today);
                                if (dropoffDateInput) dropoffDateInput.value = formatDate(tomorrow);

                                // Reset UI elements
                                resetUIElements();
                            });
                        } else {
                            let errorMessage = data.message || 'Please correct the following errors:';
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
                        console.error('Booking error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again later.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            }

            // Function to reset all UI elements after booking
            function resetUIElements() {
                // Reset rental summary
                const rentalSummary = document.getElementById('rental-summary');
                if (rentalSummary) {
                    rentalSummary.classList.add('d-none');
                }

                const sidebarRentalSummary = document.getElementById('rental-summary-sidebar');
                if (sidebarRentalSummary) {
                    sidebarRentalSummary.classList.add('d-none');
                }

                // Reset additional driver fields
                const additionalDriverFields = document.querySelectorAll('.additional-driver-fields');
                additionalDriverFields.forEach(field => {
                    field.classList.add('d-none');
                    const inputs = field.querySelectorAll('input');
                    inputs.forEach(input => {
                        input.required = false;
                        input.value = '';
                    });
                });

                // Reset delivery address field
                const deliveryAddressField = document.querySelector('.delivery-address-field');
                if (deliveryAddressField) {
                    deliveryAddressField.classList.add('d-none');
                    const input = deliveryAddressField.querySelector('input');
                    if (input) {
                        input.required = false;
                        input.value = '';
                    }
                }

                // Reset insurance and extras cost rows
                const insuranceCostRow = document.getElementById('insurance-cost-row');
                if (insuranceCostRow) {
                    insuranceCostRow.classList.add('d-none');
                }

                const extrasCostRow = document.getElementById('extras-cost-row');
                if (extrasCostRow) {
                    extrasCostRow.classList.add('d-none');
                }

                // Reset register fields
                const registerFields = document.querySelectorAll('.register-fields');
                registerFields.forEach(field => {
                    field.classList.add('d-none');
                    const inputs = field.querySelectorAll('input');
                    inputs.forEach(input => {
                        input.required = false;
                        input.value = '';
                    });
                });

                // Reset checkboxes
                const checkboxes = ['register_account', 'additional_driver', 'gps_enabled', 'child_seat', 'terms_accepted'];
                checkboxes.forEach(id => {
                    const checkbox = document.getElementById(id);
                    if (checkbox) {
                        checkbox.checked = id === 'terms_accepted'; // Only terms_accepted starts checked
                    }
                });

                // Reset radio buttons to default (basic insurance)
                const basicInsurance = document.getElementById('insurance_basic');
                if (basicInsurance) {
                    basicInsurance.checked = true;
                }

                // Reset dropdown selections to defaults
                const deliveryOption = document.getElementById('delivery_option');
                if (deliveryOption) {
                    deliveryOption.value = 'none';
                }

                const paymentMethod = document.getElementById('payment_method');
                if (paymentMethod) {
                    paymentMethod.value = '';  // Reset to prompt user to select
                }

                // Reset other form fields
                const specialRequests = document.getElementById('special_requests');
                if (specialRequests) {
                    specialRequests.value = '';
                }
            }

            // Function to update rental summary in main form
            function updateRentalSummary() {
                if (!pickupDateInput || !dropoffDateInput) return;

                const pickupDate = new Date(pickupDateInput.value);
                const dropoffDate = new Date(dropoffDateInput.value);

                const timeDiff = Math.abs(dropoffDate - pickupDate);
                const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                if (days > 0) {
                    // Get the rate per day from the data attribute or fallback to car price
                    const rateElement = document.querySelector('[data-rate-per-day]');
                    const ratePerDay = rateElement ?
                        parseFloat(rateElement.dataset.ratePerDay) :
                        {{ $car->price_per_day * (1 - $car->discount_percentage / 100) }};

                const subtotal = days * ratePerDay;

                    let insuranceCost = 0;
                    const insurancePlan = document.querySelector('input[name="insurance_plan"]:checked');
                    if (insurancePlan) {
                        if (insurancePlan.value === 'standard') {
                            insuranceCost = days * {{ config('booking.insurance.standard', 50) }};
                            } else if (insurancePlan.value === 'premium') {
                                insuranceCost = days * {{ config('booking.insurance.premium', 100) }};
                                }
                            }

                            let extrasCost = 0;
                            if (document.getElementById('additional_driver') && document.getElementById('additional_driver').checked) {
                                extrasCost += {{ config('booking.additional_driver_fee', 30) }};
                                }
                                if (document.getElementById('gps_enabled') && document.getElementById('gps_enabled').checked) {
                                    extrasCost += {{ config('booking.gps_fee', 20) }};
                                    }
                                    if (document.getElementById('child_seat') && document.getElementById('child_seat').checked) {
                                        extrasCost += {{ config('booking.child_seat_fee', 15) }};
                                        }

                                        const total = subtotal + insuranceCost + extrasCost;

                                        const rentalDaysElement = document.getElementById('rental-days');
                                        if (rentalDaysElement) {
                                            rentalDaysElement.textContent = days + ' day' + (days > 1 ? 's' : '');
                                        }

                                        const rentalSubtotalElement = document.getElementById('rental-subtotal');
                                        if (rentalSubtotalElement) {
                                            rentalSubtotalElement.textContent = subtotal.toFixed(2) + ' MAD';
                                        }

                                        const insuranceCostRow = document.getElementById('insurance-cost-row');
                                        if (insuranceCostRow) {
                                            if (insuranceCost > 0) {
                                                insuranceCostRow.classList.remove('d-none');
                                                const insuranceCostElement = document.getElementById('insurance-cost');
                                                if (insuranceCostElement) {
                                                    insuranceCostElement.textContent = insuranceCost.toFixed(2) + ' MAD';
                                                }
                                            } else {
                                                insuranceCostRow.classList.add('d-none');
                                            }
                                        }

                                        const extrasCostRow = document.getElementById('extras-cost-row');
                                        if (extrasCostRow) {
                                            if (extrasCost > 0) {
                                                extrasCostRow.classList.remove('d-none');
                                                const extrasCostElement = document.getElementById('extras-cost');
                                                if (extrasCostElement) {
                                                    extrasCostElement.textContent = extrasCost.toFixed(2) + ' MAD';
                                                }
                                            } else {
                                                extrasCostRow.classList.add('d-none');
                                            }
                                        }

                                        const rentalTotalElement = document.getElementById('rental-total');
                                        if (rentalTotalElement) {
                                            rentalTotalElement.textContent = total.toFixed(2) + ' MAD';
                                        }

                                        const rentalSummary = document.getElementById('rental-summary');
                                        if (rentalSummary) {
                                            rentalSummary.classList.remove('d-none');
                                        }
                                    }
                                }

                                // Function to update rental summary in sidebar
                                function updateSidebarRentalSummary() {
                                    if (!pickupDateSidebar || !dropoffDateSidebar) return;

                                    const pickupDate = new Date(pickupDateSidebar.value);
                                    const dropoffDate = new Date(dropoffDateSidebar.value);

                                    const timeDiff = Math.abs(dropoffDate - pickupDate);
                                    const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                                    if (days > 0) {
                                        // Get the rate per day from the data attribute or fallback to car price
                                        const rateElement = document.querySelector('[data-rate-per-day]');
                                        const ratePerDay = rateElement ?
                                            parseFloat(rateElement.dataset.ratePerDay) :
                                            {{ $car->price_per_day * (1 - $car->discount_percentage / 100) }};

                const subtotal = days * ratePerDay;
                                        const total = subtotal; // No extras in sidebar calculation

                                        const rentalDaysSidebar = document.getElementById('rental-days-sidebar');
                                        if (rentalDaysSidebar) {
                                            rentalDaysSidebar.textContent = days + ' day' + (days > 1 ? 's' : '');
                                        }

                                        const rentalSubtotalSidebar = document.getElementById('rental-subtotal-sidebar');
                                        if (rentalSubtotalSidebar) {
                                            rentalSubtotalSidebar.textContent = subtotal.toFixed(2) + ' MAD';
                                        }

                                        const rentalTotalSidebar = document.getElementById('rental-total-sidebar');
                                        if (rentalTotalSidebar) {
                                            rentalTotalSidebar.textContent = total.toFixed(2) + ' MAD';
                                        }

                                        const rentalSummarySidebar = document.getElementById('rental-summary-sidebar');
                                        if (rentalSummarySidebar) {
                                            rentalSummarySidebar.classList.remove('d-none');
                                        }
                                    }
                                }

                                // Format date as YYYY-MM-DD
                                function formatDate(date) {
                                    const year = date.getFullYear();
                                    const month = String(date.getMonth() + 1).padStart(2, '0');
                                    const day = String(date.getDate()).padStart(2, '0');
                                    return `${year}-${month}-${day}`;
                                }

                                // Show full image in modal
                                window.showFullImage = function (imageSrc) {
                                    const fullImage = document.getElementById('fullImage');
                                    if (fullImage) {
                                        fullImage.src = imageSrc;
                                        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
                                        imageModal.show();
                                    }
                                }

                                // Utility function to check car availability status
                                window.checkCarAvailability = async function () {
                                    const carId = document.querySelector('input[name="car_id"]').value;
                                    const pickupDate = pickupDateInput ? pickupDateInput.value : '';
                                    const dropoffDate = dropoffDateInput ? dropoffDateInput.value : '';

                                    if (!pickupDate || !dropoffDate) {
                                        Swal.fire({
                                            title: 'Missing Dates',
                                            text: 'Please select pickup and dropoff dates first',
                                            icon: 'warning',
                                            confirmButtonText: 'OK'
                                        });
                                        return;
                                    }

                                    try {
                                        const response = await fetch('/api/availability/check', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                car_id: carId,
                                                pickup_date: pickupDate,
                                                dropoff_date: dropoffDate
                                            })
                                        });

                                        const data = await response.json();

                                        Swal.fire({
                                            title: data.is_available ? 'Car Available' : 'Car Unavailable',
                                            text: data.message,
                                            icon: data.is_available ? 'success' : 'error',
                                            confirmButtonText: 'OK'
                                        });

                                    } catch (error) {
                                        console.error('Error checking availability:', error);
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Could not check car availability',
                                            icon: 'error',
                                            confirmButtonText: 'OK'
                                        });
                                    }
                                }

                                // Debug helper function - only in development
                                if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                                    window.debugCarStatus = async function () {
                                        const carId = document.querySelector('input[name="car_id"]').value;

                                        try {
                                            const response = await fetch(`/api/cars/${carId}/status`, {
                                                method: 'GET',
                                                headers: {
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                    'Accept': 'application/json'
                                                }
                                            });

                                            const data = await response.json();
                                            console.log('Car status:', data);

                                            Swal.fire({
                                                title: 'Car Status (DEBUG)',
                                                html: `
                            <div class="text-start">
                                <p><strong>ID:</strong> ${data.id}</p>
                                <p><strong>Name:</strong> ${data.name}</p>
                                <p><strong>Status:</strong> ${data.status}</p>
                                <p><strong>Is Available:</strong> ${data.is_available ? 'Yes' : 'No'}</p>
                            </div>
                        `,
                                                icon: 'info',
                                                confirmButtonText: 'OK'
                                            });

                                        } catch (error) {
                                            console.error('Error checking car status:', error);
                                            alert('Could not check car status');
                                        }
                                    }

                                    // Add debug button
                                    const addDebugButton = () => {
                                        const debugButton = document.createElement('button');
                                        debugButton.type = 'button';
                                        debugButton.className = 'btn btn-sm btn-secondary mt-2 ms-2';
                                        debugButton.textContent = 'Debug: Check Car Status';
                                        debugButton.onclick = window.debugCarStatus;

                                        const carBasicDetails = document.querySelector('.car-basic-details');
                                        if (carBasicDetails) {
                                            carBasicDetails.appendChild(debugButton);
                                        }
                                    };

                                    // Uncomment to add debug button
                                    // addDebugButton();
                                }
                            });
    </script>
    {{--
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize date inputs with today and tomorrow
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const pickupDateInput = document.getElementById('pickup_date');
            const dropoffDateInput = document.getElementById('dropoff_date');
            const pickupTimeInput = document.getElementById('pickup_time');
            const dropoffTimeInput = document.getElementById('dropoff_time');

            pickupDateInput.value = formatDate(today);
            dropoffDateInput.value = formatDate(tomorrow);

            // Update rental summary on page load
            updateRentalSummary();

            // Add event listeners for dynamic form updates
            pickupDateInput.addEventListener('change', function () {
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
            document.querySelectorAll('input[name="insurance_plan"]').forEach(input => {
                input.addEventListener('change', updateRentalSummary);
            });

            document.getElementById('additional_driver').addEventListener('change', function () {
                const fields = document.querySelectorAll('.additional-driver-fields');
                fields.forEach(field => {
                    field.classList.toggle('d-none', !this.checked);
                    field.querySelector('input').required = this.checked;
                });
                updateRentalSummary();
            });

            document.getElementById('gps_enabled').addEventListener('change', updateRentalSummary);
            document.getElementById('child_seat').addEventListener('change', updateRentalSummary);

            const deliveryOption = document.getElementById('delivery_option');
            const deliveryAddressField = document.querySelector('.delivery-address-field');
            deliveryOption.addEventListener('change', function () {
                const showAddress = this.value === 'home' || this.value === 'airport';
                deliveryAddressField.classList.toggle('d-none', !showAddress);
                deliveryAddressField.querySelector('input').required = showAddress;
            });

            const registerAccountCheckbox = document.getElementById('register_account');
            if (registerAccountCheckbox) {
                registerAccountCheckbox.addEventListener('change', function () {
                    const registerFields = document.querySelectorAll('.register-fields');
                    registerFields.forEach(field => {
                        field.classList.toggle('d-none', !this.checked);
                        const inputs = field.querySelectorAll('input');
                        inputs.forEach(input => {
                            input.required = this.checked;
                            if (!this.checked) {
                                input.value = ''; // Clear the fields when unchecked
                            }
                        });
                    });
                });
            }

            // Form validation and submission
            const bookingForm = document.getElementById('bookingForm');
            if (bookingForm) {
                bookingForm.addEventListener('submit', async function (e) {
                    e.preventDefault();

                    // Remove password fields if register_account is not checked
                    const registerAccount = document.getElementById('register_account');
                    if (!registerAccount || !registerAccount.checked) {
                        const passwordInput = document.getElementById('password');
                        const passwordConfirmInput = document.getElementById('password_confirmation');
                        if (passwordInput) passwordInput.removeAttribute('name');
                        if (passwordConfirmInput) passwordConfirmInput.removeAttribute('name');
                    }

                    // Client-side validation
                    if (!bookingForm.checkValidity()) {
                        bookingForm.reportValidity();
                        return;
                    }

                    const submitBtn = bookingForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Processing...
                                            `;

                    try {
                        const formData = new FormData(bookingForm);
                        // Ensure start_mileage is an integer
                        formData.set('start_mileage', parseInt(formData.get('start_mileage')));

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
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset all forms after success
                                bookingForm.reset();

                                // Also reset the sidebar form if it exists
                                const sidebarForm = document.getElementById('bookingFormSidebar');
                                if (sidebarForm) {
                                    sidebarForm.reset();
                                }

                                // Reset any dynamic UI elements
                                const rentalSummary = document.getElementById('rental-summary');
                                if (rentalSummary) {
                                    rentalSummary.classList.add('d-none');
                                }

                                // Reset additional driver fields
                                const additionalDriverFields = document.querySelectorAll('.additional-driver-fields');
                                additionalDriverFields.forEach(field => {
                                    field.classList.add('d-none');
                                });

                                // Reset delivery address field
                                const deliveryAddressField = document.querySelector('.delivery-address-field');
                                if (deliveryAddressField) {
                                    deliveryAddressField.classList.add('d-none');
                                }

                                // Reset insurance and extras cost rows
                                const insuranceCostRow = document.getElementById('insurance-cost-row');
                                if (insuranceCostRow) {
                                    insuranceCostRow.classList.add('d-none');
                                }

                                const extrasCostRow = document.getElementById('extras-cost-row');
                                if (extrasCostRow) {
                                    extrasCostRow.classList.add('d-none');
                                }

                                // Reset register fields if they exist
                                const registerFields = document.querySelectorAll('.register-fields');
                                registerFields.forEach(field => {
                                    field.classList.add('d-none');
                                });

                                // Scroll back to top of the form section
                                const bookingSection = document.querySelector('.car-reviews');
                                if (bookingSection) {
                                    bookingSection.scrollIntoView({ behavior: 'smooth' });
                                }
                            });
                        } else {
                            let errorMessage = data.message || 'Please correct the following errors:';
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
                        console.error('Booking error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'An unexpected error occurred. Please try again later.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            }

            // Function to update rental summary
            function updateRentalSummary() {
                const pickupDate = new Date(pickupDateInput.value);
                const dropoffDate = new Date(dropoffDateInput.value);

                const timeDiff = Math.abs(dropoffDate - pickupDate);
                const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

                if (days > 0) {
                    const ratePerDay = {{ $car-> price_per_day * (1 - $car -> discount_percentage / 100)
                }
            };
            const subtotal = days * ratePerDay;

            let insuranceCost = 0;
            const insurancePlan = document.querySelector('input[name="insurance_plan"]:checked').value;
            if (insurancePlan === 'standard') {
                insuranceCost = days * {{ config('booking.insurance.standard', 50) }
            };
        } else if (insurancePlan === 'premium') {
            insuranceCost = days * {{ config('booking.insurance.premium', 100) }
        };
                                    }

        let extrasCost = 0;
        if (document.getElementById('additional_driver').checked) {
            extrasCost += {{ config('booking.additional_driver_fee', 30) }
        };
                                    }
        if (document.getElementById('gps_enabled').checked) {
            extrasCost += {{ config('booking.gps_fee', 20) }
        };
                                    }
        if (document.getElementById('child_seat').checked) {
            extrasCost += {{ config('booking.child_seat_fee', 15) }
        };
                                    }

        const total = subtotal + insuranceCost + extrasCost;

        document.getElementById('rental-days').textContent = days + ' day' + (days > 1 ? 's' : '');
        document.getElementById('rental-subtotal').textContent = subtotal.toFixed(2) + ' MAD';

        const insuranceCostRow = document.getElementById('insurance-cost-row');
        if (insuranceCost > 0) {
            insuranceCostRow.classList.remove('d-none');
            document.getElementById('insurance-cost').textContent = insuranceCost.toFixed(2) + ' MAD';
        } else {
            insuranceCostRow.classList.add('d-none');
        }

        const extrasCostRow = document.getElementById('extras-cost-row');
        if (extrasCost > 0) {
            extrasCostRow.classList.remove('d-none');
            document.getElementById('extras-cost').textContent = extrasCost.toFixed(2) + ' MAD';
        } else {
            extrasCostRow.classList.add('d-none');
        }

        document.getElementById('rental-total').textContent = total.toFixed(2) + ' MAD';
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

        // Show full image in modal
        window.showFullImage = function (imageSrc) {
            document.getElementById('fullImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
                        });
    </script> --}}
@endpush

@push('styles')
    <style>
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

        @media (max-width: 992px) {
            .booking-card {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
@endpush
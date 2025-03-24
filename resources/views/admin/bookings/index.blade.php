@extends('admin.layouts.master')

@section('title', 'Booking Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Booking Management</h3>
        <div class="page-actions">
            <button type="button" class="btn btn-primary" id="createBookingBtn">
                <i class="fas fa-plus"></i> Add New Booking
            </button>
            <button type="button" class="btn btn-success" id="exportBtn">
                <i class="fas fa-file-export"></i> Export
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Bookings</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row">
                <div class="col-md-2 mb-3">
                    <label for="filter_car" class="form-label">Car</label>
                    <select class="form-select" id="filter_car">
                        <option value="">All Cars</option>
                        @foreach($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="filter_status" class="form-label">Status</label>
                    <select class="form-select" id="filter_status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="filter_payment_status" class="form-label">Payment Status</label>
                    <select class="form-select" id="filter_payment_status">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="pending">Pending</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="filter_date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="filter_date_from">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="filter_date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="filter_date_to">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <button type="button" id="resetFiltersBtn" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Bookings</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="bookings-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Booking Info</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Rental Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Status Actions</th>
                            <th>Payment Actions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-70w"">
                                        <div class=" modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Add New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bookingForm">
                @csrf
                <input type="hidden" name="booking_id" id="booking_id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Left column: Car and rental details -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Car & Rental Details</h5>
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="car_id" class="form-label">Car <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="car_id" name="car_id" required>
                                            <option value="">Select Car</option>
                                            @foreach($cars as $car)
                                                <option value="{{ $car->id }}" data-price="{{ $car->price_per_day }}"
                                                    data-discount="{{ $car->discount_percentage }}">
                                                    {{ $car->name }} - ${{ number_format($car->price_per_day, 2) }}/day
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="car_id-error"></div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="pickup_date" class="form-label">Pickup Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                                                required>
                                            <div class="invalid-feedback" id="pickup_date-error"></div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="pickup_time" class="form-label">Pickup Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="pickup_time" name="pickup_time"
                                                required>
                                            <div class="invalid-feedback" id="pickup_time-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="dropoff_date" class="form-label">Dropoff Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="dropoff_date" name="dropoff_date"
                                                required>
                                            <div class="invalid-feedback" id="dropoff_date-error"></div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="dropoff_time" class="form-label">Dropoff Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="dropoff_time" name="dropoff_time"
                                                required>
                                            <div class="invalid-feedback" id="dropoff_time-error"></div>
                                        </div>
                                    </div>

                                    <!-- For pickup location -->
                                    <div class="mb-3">
                                        <label for="pickup_location" class="form-label">Lieu de prise en charge <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select location-select" id="pickup_location"
                                            name="pickup_location" required>
                                            <option value="">Sélectionnez un lieu</option>
                                            <optgroup label="Grandes villes">
                                                <option value="Casablanca">Casablanca</option>
                                                <option value="Rabat">Rabat</option>
                                                <option value="Marrakech">Marrakech</option>
                                                <option value="Fès">Fès</option>
                                                <option value="Tanger">Tanger</option>
                                                <option value="Agadir">Agadir</option>
                                                <option value="Meknès">Meknès</option>
                                                <option value="Oujda">Oujda</option>
                                                <option value="Kénitra">Kénitra</option>
                                                <option value="Tétouan">Tétouan</option>
                                                <option value="Safi">Safi</option>
                                                <option value="Mohammedia">Mohammedia</option>
                                                <option value="El Jadida">El Jadida</option>
                                                <option value="Béni Mellal">Béni Mellal</option>
                                                <option value="Nador">Nador</option>
                                                <option value="Taza">Taza</option>
                                                <option value="Khémisset">Khémisset</option>
                                                <option value="Essaouira">Essaouira</option>
                                                <option value="Ouarzazate">Ouarzazate</option>
                                            </optgroup>
                                            <optgroup label="Aéroports">
                                                <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                    (Casablanca)</option>
                                                <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara</option>
                                                <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira
                                                </option>
                                                <option value="Aéroport Fès-Saïs">Aéroport Fès-Saïs</option>
                                                <option value="Aéroport Rabat-Salé">Aéroport Rabat-Salé</option>
                                                <option value="Aéroport Tanger Ibn Battouta">Aéroport Tanger Ibn Battouta
                                                </option>
                                                <option value="Aéroport Oujda-Angads">Aéroport Oujda-Angads</option>
                                                <option value="Aéroport Nador-Al Aroui">Aéroport Nador-Al Aroui</option>
                                                <option value="Aéroport Ouarzazate">Aéroport Ouarzazate</option>
                                                <option value="Aéroport Essaouira-Mogador">Aéroport Essaouira-Mogador
                                                </option>
                                                <option value="Aéroport Dakhla">Aéroport Dakhla</option>
                                                <option value="Aéroport Laâyoune-Hassan Ier">Aéroport Laâyoune-Hassan Ier
                                                </option>
                                            </optgroup>
                                            <optgroup label="Autre">
                                                <option value="custom">Autre (veuillez préciser)</option>
                                            </optgroup>
                                        </select>
                                        <input type="text" class="form-control mt-2 d-none" id="pickup_location_custom"
                                            placeholder="Précisez le lieu de prise en charge">
                                        <div class="invalid-feedback" id="pickup_location-error"></div>
                                    </div>

                                    <!-- For dropoff location -->
                                    <div class="mb-3">
                                        <label for="dropoff_location" class="form-label">Lieu de retour <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select location-select" id="dropoff_location"
                                            name="dropoff_location" required>
                                            <option value="">Sélectionnez un lieu</option>
                                            <optgroup label="Grandes villes">
                                                <option value="Casablanca">Casablanca</option>
                                                <option value="Rabat">Rabat</option>
                                                <option value="Marrakech">Marrakech</option>
                                                <option value="Fès">Fès</option>
                                                <option value="Tanger">Tanger</option>
                                                <option value="Agadir">Agadir</option>
                                                <option value="Meknès">Meknès</option>
                                                <option value="Oujda">Oujda</option>
                                                <option value="Kénitra">Kénitra</option>
                                                <option value="Tétouan">Tétouan</option>
                                                <option value="Safi">Safi</option>
                                                <option value="Mohammedia">Mohammedia</option>
                                                <option value="El Jadida">El Jadida</option>
                                                <option value="Béni Mellal">Béni Mellal</option>
                                                <option value="Nador">Nador</option>
                                                <option value="Taza">Taza</option>
                                                <option value="Khémisset">Khémisset</option>
                                                <option value="Essaouira">Essaouira</option>
                                                <option value="Ouarzazate">Ouarzazate</option>
                                            </optgroup>
                                            <optgroup label="Aéroports">
                                                <option value="Aéroport Mohammed V (Casablanca)">Aéroport Mohammed V
                                                    (Casablanca)</option>
                                                <option value="Aéroport Marrakech Menara">Aéroport Marrakech Menara</option>
                                                <option value="Aéroport Agadir Al Massira">Aéroport Agadir Al Massira
                                                </option>
                                                <option value="Aéroport Fès-Saïs">Aéroport Fès-Saïs</option>
                                                <option value="Aéroport Rabat-Salé">Aéroport Rabat-Salé</option>
                                                <option value="Aéroport Tanger Ibn Battouta">Aéroport Tanger Ibn Battouta
                                                </option>
                                                <option value="Aéroport Oujda-Angads">Aéroport Oujda-Angads</option>
                                                <option value="Aéroport Nador-Al Aroui">Aéroport Nador-Al Aroui</option>
                                                <option value="Aéroport Ouarzazate">Aéroport Ouarzazate</option>
                                                <option value="Aéroport Essaouira-Mogador">Aéroport Essaouira-Mogador
                                                </option>
                                                <option value="Aéroport Dakhla">Aéroport Dakhla</option>
                                                <option value="Aéroport Laâyoune-Hassan Ier">Aéroport Laâyoune-Hassan Ier
                                                </option>
                                            </optgroup>
                                            <optgroup label="Autre">
                                                <option value="custom">Autre (veuillez préciser)</option>
                                            </optgroup>
                                        </select>
                                        <input type="text" class="form-control mt-2 d-none" id="dropoff_location_custom"
                                            placeholder="Précisez le lieu de retour">
                                        <div class="invalid-feedback" id="dropoff_location-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests"
                                            rows="3"></textarea>
                                        <div class="invalid-feedback" id="special_requests-error"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing details -->
                            <h5 class="mb-3">Pricing Details</h5>
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="total_days" class="form-label">Total Days</label>
                                            <input type="number" class="form-control" id="total_days" name="total_days"
                                                readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div id="availability_display" class="my-2 py-2 text-center">
                                                <span class="badge bg-secondary">No car selected</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="base_price" class="form-label">Base Price ($)</label>
                                            <input type="number" step="0.01" class="form-control" id="base_price"
                                                name="base_price" readonly>
                                            <div class="invalid-feedback" id="base_price-error"></div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="discount_amount" class="form-label">Discount ($)</label>
                                            <input type="number" step="0.01" class="form-control" id="discount_amount"
                                                name="discount_amount" readonly>
                                            <div class="invalid-feedback" id="discount_amount-error"></div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="tax_amount" class="form-label">Tax ($)</label>
                                            <input type="number" step="0.01" class="form-control" id="tax_amount"
                                                name="tax_amount" readonly>
                                            <div class="invalid-feedback" id="tax_amount-error"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="total_amount" class="form-label">Total Amount ($)</label>
                                        <input type="number" step="0.01" class="form-control" id="total_amount"
                                            name="total_amount" readonly>
                                        <div class="invalid-feedback" id="total_amount-error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right column: Customer and payment details -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Customer Information</h5>
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Customer Account (Optional)</label>
                                        <select class="form-select" id="user_id" name="user_id">
                                            <option value="">Guest Booking</option>
                                            @php
                                                $users = \App\Models\User::orderBy('name')->get();
                                            @endphp
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}" data-phone="{{ $user->phone ?? '' }}">
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="user_id-error"></div>
                                        <div class="form-text">If selected, customer details will be pre-filled.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_name" class="form-label">Customer Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                                            required>
                                        <div class="invalid-feedback" id="customer_name-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_email" class="form-label">Customer Email <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email"
                                            required>
                                        <div class="invalid-feedback" id="customer_email-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_phone" class="form-label">Customer Phone</label>
                                        <input type="text" class="form-control" id="customer_phone" name="customer_phone">
                                        <div class="invalid-feedback" id="customer_phone-error"></div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3">Payment & Status</h5>
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Booking Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="pending">Pending</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <div class="invalid-feedback" id="status-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="cash_on_delivery">Cash on Delivery</option>
                                            <option value="credit_card">Credit Card</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                        <div class="invalid-feedback" id="payment_method-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_status" class="form-label">Payment Status <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_status" name="payment_status" required>
                                            <option value="unpaid">Unpaid</option>
                                            <option value="pending">Pending</option>
                                            <option value="paid">Paid</option>
                                            <option value="refunded">Refunded</option>
                                        </select>
                                        <div class="invalid-feedback" id="payment_status-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="transaction_id" class="form-label">Transaction ID</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                                        <div class="invalid-feedback" id="transaction_id-error"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save Booking</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- View Booking Modal -->
    <div class="modal fade" id="viewBookingModal" tabindex="-1" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-70w">
            <div class=" modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBookingModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0" id="view-booking-number"></h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span id="view-status-badge"></span>
                                        <span id="view-payment-badge"></span>
                                    </div>
                                    <p class="mb-1"><strong>Created:</strong> <span id="view-created-at"></span></p>
                                    <p class="mb-0"><strong>Updated:</strong> <span id="view-updated-at"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Car Information</h5>
                                </div>
                                <div class="card-body">
                                    <h5 id="view-car-name" class="mb-2"></h5>
                                    <p class="mb-0" id="view-car-details"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <h5 id="view-customer-name" class="mb-2"></h5>
                                    <p class="mb-1" id="view-customer-email"></p>
                                    <p class="mb-1" id="view-customer-phone"></p>
                                    <p class="mb-0" id="view-customer-account"></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Rental Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Pickup:</strong></div>
                                        <div class="col-md-8" id="view-pickup-details"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Dropoff:</strong></div>
                                        <div class="col-md-8" id="view-dropoff-details"></div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-md-4"><strong>Duration:</strong></div>
                                        <div class="col-md-8" id="view-duration"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Payment Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Base Price:</strong></div>
                                        <div class="col-md-8" id="view-base-price"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Discount:</strong></div>
                                        <div class="col-md-8" id="view-discount"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Tax:</strong></div>
                                        <div class="col-md-8" id="view-tax"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Total:</strong></div>
                                        <div class="col-md-8 fw-bold" id="view-total"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4"><strong>Method:</strong></div>
                                        <div class="col-md-8" id="view-payment-method"></div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-md-4"><strong>Transaction:</strong></div>
                                        <div class="col-md-8" id="view-transaction-id"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light mb-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Special Requests</h5>
                                </div>
                                <div class="card-body">
                                    <p id="view-special-requests" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row w-100">
                        <div class="col-md-6 text-start" id="view-status-actions"></div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="viewEditBtn" class="btn btn-primary">Edit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .modal-70w {
            width: 70%;
            max-width: 70%;
        }

        /* Fix for the scrolling issue */
        .modal-70w .modal-content {
            height: auto;
            max-height: 85vh;
            /* Limit height to 85% of viewport height */
        }

        .modal-70w .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
            /* Adjust for header and footer */
        }

        /* Ensure proper centering */
        @media (min-width: 992px) {
            .modal-70w {
                margin-left: auto;
                margin-right: auto;
            }
        }

        #bookings-table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.85rem;
        }

        #availability_display .badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.bookings.data') }}",
            storeUrl: "{{ route('admin.bookings.store') }}",
            showUrl: "{{ route('admin.bookings.show', ':id') }}",
            updateUrl: "{{ route('admin.bookings.update', ':id') }}",
            destroyUrl: "{{ route('admin.bookings.destroy', ':id') }}",
            calculateUrl: "{{ route('admin.bookings.calculate-prices') }}",
            updateStatusUrl: "{{ route('admin.bookings.update-status', ':id') }}",
            updatePaymentStatusUrl: "{{ route('admin.bookings.update-payment-status', ':id') }}",
            exportUrl: "{{ route('admin.bookings.export') }}"
        };

        // CSRF Token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    </script>
    <script>

    </script>
    <!-- Include the bookings management JS -->
    <script src="{{ asset('admin/js/bookings-management.js') }}"></script>
@endpush
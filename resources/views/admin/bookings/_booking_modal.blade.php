
<!-- Create/Edit Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen">
        <div class="modal-content">
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
                                            <input type="date" class="form-control" id="dropoff_date"
                                                name="dropoff_date" required>
                                            <div class="invalid-feedback" id="dropoff_date-error"></div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="dropoff_time" class="form-label">Dropoff Time <span
                                                    class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="dropoff_time"
                                                name="dropoff_time" required>
                                            <div class="invalid-feedback" id="dropoff_time-error"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="pickup_location" class="form-label">Pickup Location <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="pickup_location"
                                            name="pickup_location" required>
                                        <div class="invalid-feedback" id="pickup_location-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="dropoff_location" class="form-label">Dropoff Location <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="dropoff_location"
                                            name="dropoff_location" required>
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
                                        <input type="email" class="form-control" id="customer_email"
                                            name="customer_email" required>
                                        <div class="invalid-feedback" id="customer_email-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="customer_phone" class="form-label">Customer Phone</label>
                                        <input type="text" class="form-control" id="customer_phone"
                                            name="customer_phone">
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
                                        <input type="text" class="form-control" id="transaction_id"
                                            name="transaction_id">
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
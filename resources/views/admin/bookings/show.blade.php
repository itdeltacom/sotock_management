@extends('admin.layouts.master')

@section('title', 'Booking Details')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Booking Details: #{{ $booking->booking_number }}</h3>
        <div class="page-actions">
            <a href="{{ route('admin.bookings.edit', $booking) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Booking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Booking Number:</div>
                        <div class="col-md-8 fw-bold">{{ $booking->booking_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Date Created:</div>
                        <div class="col-md-8">{{ $booking->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Status:</div>
                        <div class="col-md-8">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-warning',
                                    'confirmed' => 'bg-success',
                                    'completed' => 'bg-info',
                                    'cancelled' => 'bg-danger'
                                ];
                                $statusClass = $statusClasses[$booking->status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($booking->status) }}</span>

                            <div class="mt-2">
                                @if($booking->status == 'pending')
                                    <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST"
                                        class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status == 'confirmed')
                                    <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST"
                                        class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-info">
                                            <i class="fas fa-flag-checkered"></i> Complete
                                        </button>
                                    </form>
                                @endif

                                @if($booking->status != 'cancelled' && $booking->status != 'completed')
                                    <form action="{{ route('admin.bookings.update-status', $booking) }}" method="POST"
                                        class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to cancel this booking?')">
                                            <i class="fas fa-ban"></i> Cancel
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Car Information</h5>
                </div>
                <div class="card-body">
                    @if($booking->car)
                        <div class="d-flex align-items-center mb-3">
                            @if($booking->car->main_image)
                                <img src="{{ asset('storage/' . $booking->car->main_image) }}" alt="{{ $booking->car->name }}"
                                    class="me-3" style="width: 80px; height: 60px; object-fit: cover;">
                            @else
                                <div class="bg-secondary me-3"
                                    style="width: 80px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-car fa-2x text-white"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-1">{{ $booking->car->name }}</h5>
                                <p class="mb-0 text-muted">{{ $booking->car->category->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Price Per Day:</div>
                            <div class="col-md-8">${{ number_format($booking->car->price_per_day, 2) }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 text-muted">Discount:</div>
                            <div class="col-md-8">{{ $booking->car->discount_percentage }}%</div>
                        </div>

                        <div class="row mb-0">
                            <div class="col-md-4 text-muted">Features:</div>
                            <div class="col-md-8">
                                @if($booking->car->features)
                                    <ul class="mb-0 ps-3">
                                        @foreach($booking->car->features as $feature)
                                            <li>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">No features listed</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Car information not available or car has been deleted.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Name:</div>
                        <div class="col-md-8">{{ $booking->customer_name }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Email:</div>
                        <div class="col-md-8">{{ $booking->customer_email }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Phone:</div>
                        <div class="col-md-8">{{ $booking->customer_phone ?? 'Not provided' }}</div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-4 text-muted">User Account:</div>
                        <div class="col-md-8">
                            @if($booking->user)
                                <span class="badge bg-info">Registered User</span>
                                <a href="{{ route('admin.users.show', $booking->user) }}" class="ms-2">
                                    View Profile
                                </a>
                            @else
                                <span class="badge bg-secondary">Guest</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Rental Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Pickup:</div>
                        <div class="col-md-8">
                            {{ $booking->pickup_date->format('M d, Y') }} at {{ $booking->pickup_time }}<br>
                            <small class="text-muted">{{ $booking->pickup_location }}</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Dropoff:</div>
                        <div class="col-md-8">
                            {{ $booking->dropoff_date->format('M d, Y') }} at {{ $booking->dropoff_time }}<br>
                            <small class="text-muted">{{ $booking->dropoff_location }}</small>
                        </div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-4 text-muted">Duration:</div>
                        <div class="col-md-8">{{ $booking->total_days }} day(s)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Payment Status:</div>
                        <div class="col-md-8">
                            @php
                                $paymentClasses = [
                                    'paid' => 'bg-success',
                                    'unpaid' => 'bg-danger',
                                    'pending' => 'bg-warning',
                                    'refunded' => 'bg-info'
                                ];
                                $paymentClass = $paymentClasses[$booking->payment_status] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $paymentClass }}">{{ ucfirst($booking->payment_status) }}</span>

                            <div class="mt-2">
                                @if($booking->payment_status == 'unpaid' || $booking->payment_status == 'pending')
                                    <form action="{{ route('admin.bookings.update-payment-status', $booking) }}" method="POST"
                                        class="d-inline-block" id="markPaidForm">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="payment_status" value="paid">
                                        <input type="hidden" name="transaction_id" id="transaction_id_input">
                                        <button type="button" class="btn btn-sm btn-success" onclick="askForTransactionId()">
                                            <i class="fas fa-check"></i> Mark Paid
                                        </button>
                                    </form>
                                @endif

                                @if($booking->payment_status == 'paid')
                                    <form action="{{ route('admin.bookings.update-payment-status', $booking) }}" method="POST"
                                        class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="payment_status" value="refunded">
                                        <button type="submit" class="btn btn-sm btn-warning"
                                            onclick="return confirm('Are you sure you want to mark this payment as refunded?')">
                                            <i class="fas fa-undo"></i> Mark Refunded
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Payment Method:</div>
                        <div class="col-md-8">{{ ucwords(str_replace('_', ' ', $booking->payment_method)) }}</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 text-muted">Transaction ID:</div>
                        <div class="col-md-8">{{ $booking->transaction_id ?? 'N/A' }}</div>
                    </div>

                    <hr>

                    <div class="row mb-2">
                        <div class="col-md-8 text-muted">Base Price ({{ $booking->total_days }} days):</div>
                        <div class="col-md-4 text-end">${{ number_format($booking->base_price, 2) }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-8 text-muted">Discount:</div>
                        <div class="col-md-4 text-end">-${{ number_format($booking->discount_amount, 2) }}</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-8 text-muted">Tax:</div>
                        <div class="col-md-4 text-end">${{ number_format($booking->tax_amount, 2) }}</div>
                    </div>

                    <div class="row mb-0">
                        <div class="col-md-8 fw-bold">Total Amount:</div>
                        <div class="col-md-4 text-end fw-bold">${{ number_format($booking->total_amount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Special Requests</h5>
                </div>
                <div class="card-body">
                    @if($booking->special_requests)
                        <p class="mb-0">{{ $booking->special_requests }}</p>
                    @else
                        <p class="text-muted mb-0">No special requests</p>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Activity Log</h5>
                </div>
                <div class="card-body">
                    @if(method_exists(app(), 'activity'))
                                    @php
                                        $activities = \Spatie\Activitylog\Models\Activity::causedByType('App\\Models\\Admin')
                                            ->where('subject_type', 'App\\Models\\Booking')
                                            ->where('subject_id', $booking->id)
                                            ->orderBy('created_at', 'desc')
                                            ->get();
                                    @endphp

                                    @if($activities->count() > 0)
                                        <ul class="timeline">
                                            @foreach($activities as $activity)
                                                <li class="timeline-item mb-3">
                                                    <span class="timeline-point"></span>
                                                    <div class="timeline-content">
                                                        <small class="text-muted">{{ $activity->created_at->format('M d, Y H:i') }}</small>
                                                        <p class="mb-0">{{ $activity->description }}</p>
                                                        <small>By: {{ $activity->causer->name ?? 'System' }}</small>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-muted mb-0">No activities recorded</p>
                                    @endif
                    @else
                        <div class="alert alert-info mb-0">
                            Activity tracking not available. Install the spatie/laravel-activitylog package to enable activity
                            logging.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .timeline {
            position: relative;
            padding-left: 20px;
            list-style: none;
        }

        .timeline-item {
            position: relative;
            padding-left: 15px;
            border-left: 1px solid #dee2e6;
        }

        .timeline-point {
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #007bff;
            border: 2px solid #fff;
        }
    </style>
@endpush

@push('js')
    <script>
        function askForTransactionId() {
            Swal.fire({
                title: 'Enter Transaction ID',
                input: 'text',
                inputLabel: 'Transaction ID (optional)',
                inputPlaceholder: 'Enter transaction ID',
                showCancelButton: true,
                confirmButtonText: 'Update Payment',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    // Allow empty transaction ID
                    return null;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('transaction_id_input').value = result.value;
                    document.getElementById('markPaidForm').submit();
                }
            });
        }
    </script>
@endpush
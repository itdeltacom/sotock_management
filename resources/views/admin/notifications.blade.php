@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="text-white text-capitalize ps-3">Notifications</h6>
                                <form action="{{ route('admin.notifications.mark-all-as-read') }}" method="POST"
                                    class="me-3">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-info mb-0">
                                        <i class="fas fa-check-double me-1"></i> Mark All as Read
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="px-3">
                            <ul class="nav nav-tabs" id="notificationTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all"
                                        type="button" role="tab" aria-controls="all" aria-selected="true">
                                        All <span class="badge bg-primary ms-1">{{ count($notifications) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="document-tab" data-bs-toggle="tab"
                                        data-bs-target="#document" type="button" role="tab" aria-controls="document"
                                        aria-selected="false">
                                        Documents <span
                                            class="badge bg-warning ms-1">{{ count(array_filter($notifications, function ($n) {
        return $n['type'] == 'document'; })) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="contract-tab" data-bs-toggle="tab"
                                        data-bs-target="#contract" type="button" role="tab" aria-controls="contract"
                                        aria-selected="false">
                                        Contracts <span
                                            class="badge bg-info ms-1">{{ count(array_filter($notifications, function ($n) {
        return $n['type'] == 'contract'; })) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab"
                                        data-bs-target="#maintenance" type="button" role="tab" aria-controls="maintenance"
                                        aria-selected="false">
                                        Maintenance <span
                                            class="badge bg-danger ms-1">{{ count(array_filter($notifications, function ($n) {
        return $n['type'] == 'maintenance'; })) }}</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking"
                                        type="button" role="tab" aria-controls="booking" aria-selected="false">
                                        Bookings <span
                                            class="badge bg-success ms-1">{{ count(array_filter($notifications, function ($n) {
        return $n['type'] == 'booking'; })) }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content mt-3" id="notificationTabContent">
                            <!-- All Notifications Tab -->
                            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                                @if(count($notifications) > 0)
                                    <div class="list-group px-3">
                                        @foreach($notifications as $notification)
                                            <div
                                                class="list-group-item list-group-item-action {{ $notification['read_at'] ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $notification['color'] }} shadow text-center">
                                                            <i class="{{ $notification['icon'] }} text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $notification['title'] }}</h6>
                                                            <p class="mb-1 text-sm">{{ $notification['message'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                {{ $notification['created_at']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $notification['link'] }}" class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(!isset($notification['read_at']))
                                                            <button class="btn btn-sm btn-success mark-read-btn"
                                                                data-id="{{ $notification['id'] }}"
                                                                data-url="{{ route('admin.notifications.mark-as-read') }}">
                                                                <i class="fas fa-check"></i> Mark Read
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                        <h5>No notifications found</h5>
                                        <p class="text-muted">You don't have any notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Document Notifications Tab -->
                            <div class="tab-pane fade" id="document" role="tabpanel" aria-labelledby="document-tab">
                                @php
                                    $documentNotifications = array_filter($notifications, function ($n) {
                                        return $n['type'] == 'document';
                                    });
                                @endphp

                                @if(count($documentNotifications) > 0)
                                    <div class="list-group px-3">
                                        @foreach($documentNotifications as $notification)
                                            <div
                                                class="list-group-item list-group-item-action {{ $notification['read_at'] ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $notification['color'] }} shadow text-center">
                                                            <i class="{{ $notification['icon'] }} text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $notification['title'] }}</h6>
                                                            <p class="mb-1 text-sm">{{ $notification['message'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                {{ $notification['created_at']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $notification['link'] }}" class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(!isset($notification['read_at']))
                                                            <button class="btn btn-sm btn-success mark-read-btn"
                                                                data-id="{{ $notification['id'] }}"
                                                                data-url="{{ route('admin.notifications.mark-as-read') }}">
                                                                <i class="fas fa-check"></i> Mark Read
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                        <h5>No document notifications</h5>
                                        <p class="text-muted">You don't have any document notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Contract Notifications Tab -->
                            <div class="tab-pane fade" id="contract" role="tabpanel" aria-labelledby="contract-tab">
                                @php
                                    $contractNotifications = array_filter($notifications, function ($n) {
                                        return $n['type'] == 'contract';
                                    });
                                @endphp

                                @if(count($contractNotifications) > 0)
                                    <div class="list-group px-3">
                                        @foreach($contractNotifications as $notification)
                                            <div
                                                class="list-group-item list-group-item-action {{ $notification['read_at'] ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $notification['color'] }} shadow text-center">
                                                            <i class="{{ $notification['icon'] }} text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $notification['title'] }}</h6>
                                                            <p class="mb-1 text-sm">{{ $notification['message'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                {{ $notification['created_at']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $notification['link'] }}" class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(!isset($notification['read_at']))
                                                            <button class="btn btn-sm btn-success mark-read-btn"
                                                                data-id="{{ $notification['id'] }}"
                                                                data-url="{{ route('admin.notifications.mark-as-read') }}">
                                                                <i class="fas fa-check"></i> Mark Read
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-file-contract fa-4x text-muted mb-3"></i>
                                        <h5>No contract notifications</h5>
                                        <p class="text-muted">You don't have any contract notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Maintenance Notifications Tab -->
                            <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
                                @php
                                    $maintenanceNotifications = array_filter($notifications, function ($n) {
                                        return $n['type'] == 'maintenance';
                                    });
                                @endphp

                                @if(count($maintenanceNotifications) > 0)
                                    <div class="list-group px-3">
                                        @foreach($maintenanceNotifications as $notification)
                                            <div
                                                class="list-group-item list-group-item-action {{ $notification['read_at'] ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $notification['color'] }} shadow text-center">
                                                            <i class="{{ $notification['icon'] }} text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $notification['title'] }}</h6>
                                                            <p class="mb-1 text-sm">{{ $notification['message'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                {{ $notification['created_at']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $notification['link'] }}" class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(!isset($notification['read_at']))
                                                            <button class="btn btn-sm btn-success mark-read-btn"
                                                                data-id="{{ $notification['id'] }}"
                                                                data-url="{{ route('admin.notifications.mark-as-read') }}">
                                                                <i class="fas fa-check"></i> Mark Read
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-tools fa-4x text-muted mb-3"></i>
                                        <h5>No maintenance notifications</h5>
                                        <p class="text-muted">You don't have any maintenance notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Booking Notifications Tab -->
                            <div class="tab-pane fade" id="booking" role="tabpanel" aria-labelledby="booking-tab">
                                @php
                                    $bookingNotifications = array_filter($notifications, function ($n) {
                                        return $n['type'] == 'booking';
                                    });
                                @endphp

                                @if(count($bookingNotifications) > 0)
                                    <div class="list-group px-3">
                                        @foreach($bookingNotifications as $notification)
                                            <div
                                                class="list-group-item list-group-item-action {{ $notification['read_at'] ? '' : 'bg-light' }}">
                                                <div class="d-flex w-100 justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $notification['color'] }} shadow text-center">
                                                            <i class="{{ $notification['icon'] }} text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $notification['title'] }}</h6>
                                                            <p class="mb-1 text-sm">{{ $notification['message'] }}</p>
                                                            <small class="text-muted">
                                                                <i class="fa fa-clock me-1"></i>
                                                                {{ $notification['created_at']->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $notification['link'] }}" class="btn btn-sm btn-info me-2">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(!isset($notification['read_at']))
                                                            <button class="btn btn-sm btn-success mark-read-btn"
                                                                data-id="{{ $notification['id'] }}"
                                                                data-url="{{ route('admin.notifications.mark-as-read') }}">
                                                                <i class="fas fa-check"></i> Mark Read
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
                                        <h5>No booking notifications</h5>
                                        <p class="text-muted">You don't have any booking notifications at the moment.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer pt-3">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>,
                            Moroccan Car Rental Management System
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link text-muted">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.bookings.index') }}" class="nav-link text-muted">Bookings</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.cars.index') }}" class="nav-link text-muted">Vehicles</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link pe-0 text-muted">Reports</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle marking notifications as read
            document.querySelectorAll('.mark-read-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const notificationId = this.dataset.id;
                    const url = this.dataset.url;
                    const buttonElement = this;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            notification_id: notificationId
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the highlight
                                buttonElement.closest('.list-group-item').classList.remove('bg-light');

                                // Remove the button
                                buttonElement.remove();

                                // Update notification counter in navbar if needed
                                const counter = document.querySelector('.nav-item .badge');
                                if (counter) {
                                    const count = parseInt(counter.textContent);
                                    if (count > 1) {
                                        counter.textContent = count - 1;
                                    } else {
                                        counter.remove();
                                    }
                                }

                                // Show toast notification
                                const toastHTML = `
                                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="fas fa-check me-2"></i> Notification marked as read
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            `;

                                const toastContainer = document.getElementById('toast-container');
                                if (toastContainer) {
                                    toastContainer.innerHTML += toastHTML;
                                    const toast = toastContainer.querySelector('.toast:last-child');
                                    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
                                    bsToast.show();
                                }
                            }
                        });
                });
            });
        });
    </script>
@endpush
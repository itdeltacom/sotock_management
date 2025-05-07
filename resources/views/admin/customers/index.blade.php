@extends('admin.layouts.master')

@section('title', 'Customer Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Customers</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['total_clients'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="text-success text-sm font-weight-bolder">+{{ $statistics['new_this_month'] }}</span>
                                        this month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="fas fa-users text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Contracts</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['active_contracts'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="text-info text-sm font-weight-bolder">{{ $statistics['contracts_ending_soon'] }}</span>
                                        ending soon
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="fas fa-file-contract text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Overdue Contracts</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['overdue_contracts'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="text-danger text-sm font-weight-bolder">{{ number_format($statistics['overdue_amount'], 2) }}
                                            MAD</span>
                                        total due
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="fas fa-exclamation-triangle text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Risk Clients</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['risk_clients'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="text-warning text-sm font-weight-bolder">{{ $statistics['banned_clients'] }}</span>
                                        banned
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-user-slash text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Customer Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create clients')
                                    <button type="button" class="btn bg-gradient-primary" id="createClientBtn">
                                        <i class="fas fa-plus"></i> Add New Customer
                                    </button>
                                @endcan
                                <button type="button" class="btn bg-gradient-secondary" id="exportClientsBtn">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="overdueFilter">
                                    <option value="">All Contracts</option>
                                    <option value="1">Has Overdue</option>
                                    <option value="0">No Overdue</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="creditScoreFilter">
                                    <option value="">All Credit Scores</option>
                                    <option value="80-100">Excellent (80-100)</option>
                                    <option value="50-79">Good (50-79)</option>
                                    <option value="0-49">Poor (0-49)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-sm bg-gradient-info w-100" id="resetFilters">
                                    <i class="fas fa-undo"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="clients-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Client</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Contact</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Contracts</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Balance</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Credit Score</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Client Modal -->
        <div class="modal fade" id="clientModal" tabindex="-1" aria-labelledby="clientModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-70">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clientModalLabel">Add New Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="clientForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="method" value="POST">
                        <input type="hidden" name="client_id" id="client_id">
                        <div class="modal-body">
                            <ul class="nav nav-pills nav-fill p-1 mb-3" id="clientTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab"
                                        data-bs-target="#basic" type="button" role="tab" aria-controls="basic"
                                        aria-selected="true">Basic Info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab"
                                        data-bs-target="#documents" type="button" role="tab" aria-controls="documents"
                                        aria-selected="false">Documents</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="emergency-tab" data-bs-toggle="tab"
                                        data-bs-target="#emergency" type="button" role="tab" aria-controls="emergency"
                                        aria-selected="false">Emergency Contact</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="clientTabsContent">
                                <!-- Basic Info Tab -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel"
                                    aria-labelledby="basic-tab">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-control-label">Full Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                            <div class="invalid-feedback" id="name-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="email" class="form-control-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                            <div class="invalid-feedback" id="email-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-control-label">Phone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" required>
                                            <div class="invalid-feedback" id="phone-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-control-label">Password <span
                                                    id="password-hint" class="text-muted text-xs">(Leave blank to keep
                                                    current password)</span></label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <div class="invalid-feedback" id="password-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password_confirmation" class="form-control-label">Confirm
                                                Password</label>
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation">
                                            <div class="invalid-feedback" id="password_confirmation-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-control-label">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="banned">Banned</option>
                                            </select>
                                            <div class="invalid-feedback" id="status-error"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-control-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                                        <div class="invalid-feedback" id="address-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="photo" class="form-control-label">Profile Photo</label>
                                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                        <div class="invalid-feedback" id="photo-error"></div>
                                        <div id="photo_preview" class="mt-2 d-none">
                                            <img src="" alt="Profile Photo Preview" class="img-fluid shadow-sm rounded"
                                                style="max-height: 200px;">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-control-label">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                                        <div class="invalid-feedback" id="notes-error"></div>
                                    </div>
                                </div>

                                <!-- Documents Tab -->
                                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
                                        <span class="alert-text">All document information must be accurate and up to
                                            date.</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="id_number" class="form-control-label">ID Number</label>
                                            <input type="text" class="form-control" id="id_number" name="id_number"
                                                required>
                                            <div class="invalid-feedback" id="id_number-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="license_number" class="form-control-label">Driver's License
                                                Number</label>
                                            <input type="text" class="form-control" id="license_number"
                                                name="license_number" required>
                                            <div class="invalid-feedback" id="license_number-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="license_expiry_date" class="form-control-label">License Expiry
                                                Date</label>
                                            <input type="date" class="form-control" id="license_expiry_date"
                                                name="license_expiry_date" required>
                                            <div class="invalid-feedback" id="license_expiry_date-error"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Emergency Contact Tab -->
                                <div class="tab-pane fade" id="emergency" role="tabpanel" aria-labelledby="emergency-tab">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="emergency_contact_name" class="form-control-label">Emergency Contact
                                                Name</label>
                                            <input type="text" class="form-control" id="emergency_contact_name"
                                                name="emergency_contact_name">
                                            <div class="invalid-feedback" id="emergency_contact_name-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="emergency_contact_phone" class="form-control-label">Emergency
                                                Contact Phone</label>
                                            <input type="tel" class="form-control" id="emergency_contact_phone"
                                                name="emergency_contact_phone">
                                            <div class="invalid-feedback" id="emergency_contact_phone-error"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <p>Are you sure you want to delete this customer? This action cannot be undone.</p>
                            <div id="delete-warning" class="text-danger mt-3"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn bg-gradient-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Modal -->
        <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="notificationModalLabel">Send Notification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="notificationForm">
                        <div class="modal-body">
                            <input type="hidden" id="notification_client_id" name="client_id">

                            <div class="mb-3">
                                <label for="notification_type" class="form-control-label">Notification Type</label>
                                <select class="form-control" id="notification_type" name="type" required>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="both">Both Email & SMS</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notification_subject" class="form-control-label">Subject</label>
                                <input type="text" class="form-control" id="notification_subject" name="subject" required>
                            </div>

                            <div class="mb-3">
                                <label for="notification_message" class="form-control-label">Message</label>
                                <textarea class="form-control" id="notification_message" name="message" rows="4"
                                    required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Client Modal -->
        <div class="modal fade" id="viewClientModal" tabindex="-1" aria-labelledby="viewClientModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewClientModalLabel">Customer Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-profile">
                                    <img src="{{ asset('admin/img/bg-profile.jpg') }}" alt="Image placeholder"
                                        class="card-img-top">
                                    <div class="row justify-content-center">
                                        <div class="col-4 col-lg-4 order-lg-2">
                                            <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                                                <a href="javascript:;">
                                                    <img id="view_client_photo"
                                                        src="{{ asset('admin/img/default-avatar.png') }}"
                                                        class="rounded-circle img-fluid border border-2 border-white">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center mt-4">
                                            <h5>
                                                <span id="view_client_name"></span>
                                                <span id="view_client_status_badge"></span>
                                            </h5>
                                            <div class="h6 font-weight-300">
                                                <i class="ni location_pin mr-2"></i><span id="view_client_email"></span>
                                            </div>
                                            <div class="h6 mt-4">
                                                <i class="ni business_briefcase-24 mr-2"></i>Customer ID: #<span
                                                    id="view_client_id"></span>
                                            </div>
                                            <div>
                                                <i class="ni education_hat mr-2"></i>Joined: <span
                                                    id="view_client_joined"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Customer Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Full Name</label>
                                                    <p class="form-control-static" id="view_client_full_name"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Email</label>
                                                    <p class="form-control-static" id="view_client_email_info"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Phone</label>
                                                    <p class="form-control-static" id="view_client_phone"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">ID Number</label>
                                                    <p class="form-control-static" id="view_client_id_number"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">License Number</label>
                                                    <p class="form-control-static" id="view_client_license_number"></p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">License Expiry</label>
                                                    <p class="form-control-static" id="view_client_license_expiry"></p>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-control-label">Address</label>
                                                    <p class="form-control-static" id="view_client_address"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistics Card -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Statistics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="card card-stats">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col">
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Total
                                                                    Contracts</h5>
                                                                <span class="h2 font-weight-bold mb-0"
                                                                    id="view_stats_total_contracts"></span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div
                                                                    class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
                                                                    <i class="fas fa-file-contract"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-stats">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col">
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Active
                                                                    Contracts</h5>
                                                                <span class="h2 font-weight-bold mb-0"
                                                                    id="view_stats_active_contracts"></span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div
                                                                    class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
                                                                    <i class="fas fa-check"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-stats">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col">
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Total
                                                                    Spent</h5>
                                                                <span class="h2 font-weight-bold mb-0"
                                                                    id="view_stats_total_spent"></span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div
                                                                    class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                                                    <i class="fas fa-money-bill"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="card card-stats">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col">
                                                                <h5 class="card-title text-uppercase text-muted mb-0">
                                                                    Outstanding</h5>
                                                                <span class="h2 font-weight-bold mb-0"
                                                                    id="view_stats_outstanding"></span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div
                                                                    class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Contracts -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="mb-0">Recent Contracts</h5>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a href="#" id="view_all_contracts"
                                                    class="btn btn-sm bg-gradient-primary">View All</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0" id="view_contracts_table">
                                                <thead>
                                                    <tr>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Contract #</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Car</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Duration</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Total</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Status</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="view_contracts_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Payments -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-6">
                                                <h5 class="mb-0">Recent Payments</h5>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a href="#" id="view_all_payments"
                                                    class="btn btn-sm bg-gradient-primary">View All</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0" id="view_payments_table">
                                                <thead>
                                                    <tr>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Date</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Contract</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Amount</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Method</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Reference</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="view_payments_tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="#" id="view_client_edit" class="btn bg-gradient-primary"><i class="fas fa-edit"></i>
                            Edit</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        .form-control,
        .form-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .nav-pills .nav-link {
            color: #344767;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .nav-pills .nav-link.active {
            color: #fff;
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-70,
        .modal-xl {
            max-width: 70%;
            margin: 1.75rem auto;
        }

        .modal-70 .modal-content,
        .modal-xl .modal-content {
            max-height: 85vh;
        }

        .modal-70 .modal-body,
        .modal-xl .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
            padding: 1.5rem;
        }

        .progress {
            border-radius: 0.5rem;
            overflow: hidden;
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
            dataUrl: "{{ route('admin.clients.datatable') }}",
            storeUrl: "{{ route('admin.clients.store') }}",
            showUrl: "{{ route('admin.clients.show', ':id') }}",
            editUrl: "{{ route('admin.clients.edit', ':id') }}",
            updateUrl: "{{ route('admin.clients.update', ':id') }}",
            deleteUrl: "{{ route('admin.clients.destroy', ':id') }}",
            banUrl: "{{ route('admin.clients.ban', ':id') }}",
            unbanUrl: "{{ route('admin.clients.unban', ':id') }}",
            sendNotificationUrl: "{{ route('admin.clients.sendNotification', ':id') }}",
            exportUrl: "{{ route('admin.clients.export') }}",
            contractsUrl: "{{ route('admin.clients.contracts', ':id') }}",
            paymentsUrl: "{{ route('admin.clients.payments', ':id') }}"
        };

        document.addEventListener('DOMContentLoaded', function () {
            // DOM Elements
            const clientForm = document.getElementById('clientForm');
            const clientModal = document.getElementById('clientModal');
            const viewClientModal = document.getElementById('viewClientModal');
            const createClientBtn = document.getElementById('createClientBtn');
            const saveBtn = document.getElementById('saveBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const photoPreview = document.getElementById('photo_preview');

            // Initialize DataTable
            const table = new DataTable('#clients-table', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.dataUrl,
                    type: 'POST',
                    data: function (d) {
                        d._token = '{{ csrf_token() }}';
                        d.status = $('#statusFilter').val();
                        d.has_overdue = $('#overdueFilter').val();
                        d.credit_score = $('#creditScoreFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'full_name', name: 'name' },
                    { data: 'contact_info', name: 'email' },
                    { data: 'status_badge', name: 'status' },
                    { data: 'contracts_info', name: 'contracts' },
                    { data: 'balance', name: 'balance' },
                    { data: 'credit_score', name: 'credit_score' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    paginate: {
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>'
                    }
                }
            });

            // Add asterisks to required fields
            document.querySelectorAll('#clientForm [required]').forEach(element => {
                const labelFor = element.id;
                const label = document.querySelector(`label[for="${labelFor}"]`);
                if (label && !label.innerHTML.includes('*')) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }
            });

            // Setup event listeners
            if (createClientBtn) {
                createClientBtn.addEventListener('click', function () {
                    resetForm();
                    document.getElementById('clientModalLabel').textContent = 'Add New Customer';
                    document.getElementById('method').value = 'POST';
                    document.getElementById('password').setAttribute('required', '');
                    document.getElementById('password_confirmation').setAttribute('required', '');
                    document.getElementById('password-hint').classList.add('d-none');

                    $(clientModal).modal('show');
                });
            }

            if (clientForm) {
                clientForm.addEventListener('submit', handleFormSubmit);
            }

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    const clientId = document.getElementById('client_id').value;
                    if (clientId) {
                        deleteClientConfirmed(clientId);
                    }
                });
            }

            // Photo preview
            document.getElementById('photo').addEventListener('change', function () {
                previewImage(this, 'photo_preview');
            });

            // Filter change handlers
            $('#statusFilter, #overdueFilter, #creditScoreFilter').on('change', function () {
                table.ajax.reload();
            });

            $('#resetFilters').on('click', function () {
                $('#statusFilter, #overdueFilter, #creditScoreFilter').val('');
                table.ajax.reload();
            });

            // Export button
            $('#exportClientsBtn').on('click', function () {
                const queryParams = $.param({
                    status: $('#statusFilter').val(),
                    has_overdue: $('#overdueFilter').val(),
                    credit_score: $('#creditScoreFilter').val()
                });
                window.location.href = routes.exportUrl + '?' + queryParams;
            });

            // Event delegation for action buttons
            document.addEventListener('click', function (e) {
                // View button
                if (e.target.closest('.view-client')) {
                    const button = e.target.closest('.view-client');
                    const clientId = button.getAttribute('data-id');
                    if (clientId) {
                        handleViewClient(clientId);
                    }
                }

                // Edit button
                if (e.target.closest('a[href*="edit"]')) {
                    const button = e.target.closest('a[href*="edit"]');
                    const clientId = button.getAttribute('href').match(/\/(\d+)$/)[1];
                    if (clientId) {
                        e.preventDefault();
                        handleEditClient(clientId);
                    }
                }

                // Delete button
                if (e.target.closest('.delete-record')) {
                    const button = e.target.closest('.delete-record');
                    const clientId = button.getAttribute('data-id');
                    if (clientId) {
                        document.getElementById('client_id').value = clientId;
                        document.getElementById('delete-warning').innerHTML = '';
                        $('#deleteModal').modal('show');
                    }
                }

                // Ban button
                if (e.target.closest('.ban-client')) {
                    const button = e.target.closest('.ban-client');
                    const clientId = button.getAttribute('data-id');
                    if (clientId) {
                        Swal.fire({
                            title: 'Ban Customer',
                            text: 'Are you sure you want to ban this customer?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#f5365c',
                            cancelButtonColor: '#5e72e4',
                            confirmButtonText: 'Yes, ban them!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(routes.banUrl.replace(':id', clientId), {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            table.ajax.reload();
                                            showAlert('Success', data.message || 'Customer banned successfully', 'success');
                                        } else {
                                            throw new Error(data.message || 'Failed to ban customer');
                                        }
                                    })
                                    .catch(error => {
                                        showAlert('Error', error.message || 'Failed to ban customer', 'error');
                                    });
                            }
                        });
                    }
                }

                // Unban button
                if (e.target.closest('.unban-client')) {
                    const button = e.target.closest('.unban-client');
                    const clientId = button.getAttribute('data-id');
                    if (clientId) {
                        Swal.fire({
                            title: 'Unban Customer',
                            text: 'Are you sure you want to unban this customer?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#5e72e4',
                            cancelButtonColor: '#f5365c',
                            confirmButtonText: 'Yes, unban them!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(routes.unbanUrl.replace(':id', clientId), {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            table.ajax.reload();
                                            showAlert('Success', data.message || 'Customer unbanned successfully', 'success');
                                        } else {
                                            throw new Error(data.message || 'Failed to unban customer');
                                        }
                                    })
                                    .catch(error => {
                                        showAlert('Error', error.message || 'Failed to unban customer', 'error');
                                    });
                            }
                        });
                    }
                }

                // Send notification button
                if (e.target.closest('.send-notification')) {
                    const button = e.target.closest('.send-notification');
                    const clientId = button.getAttribute('data-id');
                    if (clientId) {
                        document.getElementById('notification_client_id').value = clientId;
                        document.getElementById('notificationForm').reset();
                        $('#notificationModal').modal('show');
                    }
                }
            });

            // Notification form submission
            document.getElementById('notificationForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const clientId = document.getElementById('notification_client_id').value;
                const formData = new FormData(this);

                sendNotification(clientId, formData);
            });

            /**
             * Handle view client operation
             */
            function handleViewClient(clientId) {
                // Fetch client data
                fetch(routes.showUrl.replace(':id', clientId), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            populateViewModal(data);
                            $(viewClientModal).modal('show');
                        } else {
                            throw new Error(data.message || 'Failed to load client details');
                        }
                    })
                    .catch(error => {
                        $(viewClientModal).modal('hide');
                        showAlert('Error', error.message || 'Failed to load client details', 'error');
                    });
            }

            /**
             * Populate view client modal with data
             */
            function populateViewModal(data) {
                const client = data.client;
                const stats = data.stats;
                const contracts = data.contracts;
                const payments = data.payments;

                // Profile Card
                document.getElementById('view_client_photo').src = client.profile_photo;
                document.getElementById('view_client_name').textContent = client.full_name;
                document.getElementById('view_client_status_badge').innerHTML = `<span class="badge bg-${client.status === 'active' ? 'success' : client.status === 'inactive' ? 'danger' : 'warning'}">${client.status.charAt(0).toUpperCase() + client.status.slice(1)}</span>`;
                document.getElementById('view_client_email').textContent = client.email;
                document.getElementById('view_client_id').textContent = client.id;
                document.getElementById('view_client_joined').textContent = client.created_at;

                // Customer Information
                document.getElementById('view_client_full_name').textContent = client.full_name;
                document.getElementById('view_client_email_info').textContent = client.email;
                document.getElementById('view_client_phone').textContent = client.phone || 'N/A';
                document.getElementById('view_client_id_number').textContent = client.id_number;
                document.getElementById('view_client_license_number').textContent = client.license_number;
                document.getElementById('view_client_license_expiry').innerHTML = client.license_expiry_date + (client.license_expired ? ' <span class="badge bg-warning">Expired</span>' : '');
                document.getElementById('view_client_address').textContent = client.address || 'N/A';

                // Statistics
                document.getElementById('view_stats_total_contracts').textContent = stats.total_contracts || 0;
                document.getElementById('view_stats_active_contracts').textContent = stats.active_contracts || 0;
                document.getElementById('view_stats_total_spent').textContent = stats.total_spent ? `${stats.total_spent} MAD` : '0.00 MAD';
                document.getElementById('view_stats_outstanding').textContent = stats.outstanding_balance ? `${stats.outstanding_balance} MAD` : '0.00 MAD';

                // Contracts Table
                const contractsTbody = document.getElementById('view_contracts_tbody');
                contractsTbody.innerHTML = '';
                if (contracts.length > 0) {
                    contracts.forEach(contract => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="text-xs font-weight-bold">${contract.id}</td>
                        <td class="text-xs font-weight-bold">${contract.car}</td>
                        <td class="text-xs font-weight-bold">${contract.start_date} - ${contract.end_date}</td>
                        <td class="text-xs font-weight-bold">${contract.total_amount}</td>
                        <td class="text-xs font-weight-bold"><span class="badge bg-${contract.status === 'active' ? 'success' : 'secondary'}">${contract.status}</span></td>
                        <td>
                            <a href="${contract.view_url}" class="btn btn-sm btn-info mb-0"><i class="fas fa-eye"></i> View</a>
                        </td>
                    `;
                        contractsTbody.appendChild(row);
                    });
                } else {
                    contractsTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No contracts found</td></tr>';
                }

                // Payments Table
                const paymentsTbody = document.getElementById('view_payments_tbody');
                paymentsTbody.innerHTML = '';
                if (payments.length > 0) {
                    payments.forEach(payment => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="text-xs font-weight-bold">${payment.date}</td>
                        <td class="text-xs font-weight-bold">${payment.contract_id} (${payment.car})</td>
                        <td class="text-xs font-weight-bold">${payment.amount}</td>
                        <td class="text-xs font-weight-bold">${payment.method}</td>
                        <td class="text-xs font-weight-bold">${payment.reference}</td>
                        <td class="text-xs font-weight-bold"><span class="badge bg-success">Completed</span></td>
                    `;
                        paymentsTbody.appendChild(row);
                    });
                } else {
                    paymentsTbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No payments found</td></tr>';
                }

                // Dynamic Links
                document.getElementById('view_all_contracts').href = routes.contractsUrl.replace(':id', client.id);
                document.getElementById('view_all_payments').href = routes.paymentsUrl.replace(':id', client.id);
                document.getElementById('view_client_edit').href = routes.editUrl.replace(':id', client.id);
            }

            /**
             * Handle form submission for create/update
             */
            function handleFormSubmit(e) {
                e.preventDefault();
                const formData = new FormData(clientForm);
                const method = document.getElementById('method').value;
                const clientId = document.getElementById('client_id').value;
                const url = method === 'POST' ? routes.storeUrl : routes.updateUrl.replace(':id', clientId);

                // Client-side validation for photo
                const photoInput = document.getElementById('photo');
                if (photoInput.files.length > 0) {
                    const file = photoInput.files[0];
                    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                    if (!file.type.match('image.*')) {
                        showAlert('Error', 'Please upload a valid image file (e.g., JPG, PNG).', 'error');
                        return;
                    }
                    if (file.size > maxSize) {
                        showAlert('Error', 'Image size must not exceed 2MB.', 'error');
                        return;
                    }
                }

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $(clientModal).modal('hide');
                            table.ajax.reload();
                            showAlert('Success', data.message || 'Customer saved successfully', 'success');
                        } else {
                            if (data.errors && data.errors.photo) {
                                displayValidationErrors(data.errors);
                                showAlert('Validation Error', data.errors.photo[0] || 'Please check the photo field.', 'error');
                            } else {
                                throw new Error(data.message || 'Failed to save customer');
                            }
                        }
                    })
                    .catch(error => {
                        showAlert('Error', error.message || 'Failed to save customer', 'error');
                        if (error.errors) {
                            displayValidationErrors(error.errors);
                        }
                    })
                    .finally(() => {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = 'Save';
                    });
            }

            /**
             * Handle edit client operation
             */
            function handleEditClient(clientId) {
                fetch(routes.showUrl.replace(':id', clientId), {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            resetForm();
                            document.getElementById('clientModalLabel').textContent = 'Edit Customer';
                            document.getElementById('method').value = 'PATCH';
                            document.getElementById('client_id').value = clientId;
                            document.getElementById('password').removeAttribute('required');
                            document.getElementById('password_confirmation').removeAttribute('required');
                            document.getElementById('password-hint').classList.remove('d-none');

                            const client = data.client;
                            document.getElementById('name').value = client.full_name;
                            document.getElementById('email').value = client.email;
                            document.getElementById('phone').value = client.phone || '';
                            document.getElementById('address').value = client.address || '';
                            document.getElementById('status').value = client.status;
                            document.getElementById('id_number').value = client.id_number || '';
                            document.getElementById('license_number').value = client.license_number || '';
                            document.getElementById('license_expiry_date').value = client.license_expiry_date ? new Date(client.license_expiry_date).toISOString().split('T')[0] : '';
                            document.getElementById('emergency_contact_name').value = client.emergency_contact_name || '';
                            document.getElementById('emergency_contact_phone').value = client.emergency_contact_phone || '';
                            document.getElementById('notes').value = client.notes || '';

                            if (client.profile_photo) {
                                photoPreview.querySelector('img').src = client.profile_photo;
                                photoPreview.classList.remove('d-none');
                            }

                            $(clientModal).modal('show');
                        } else {
                            throw new Error(data.message || 'Failed to load customer data');
                        }
                    })
                    .catch(error => {
                        showAlert('Error', error.message || 'Failed to load customer data', 'error');
                    });
            }

            /**
             * Handle delete client confirmed
             */
            function deleteClientConfirmed(clientId) {
                fetch(routes.deleteUrl.replace(':id', clientId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();
                            showAlert('Success', data.message || 'Customer deleted successfully', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to delete customer');
                        }
                    })
                    .catch(error => {
                        $('#deleteModal').modal('hide');
                        showAlert('Error', error.message || 'Failed to delete customer', 'error');
                    });
            }

            /**
             * Send notification
             */
            function sendNotification(clientId, formData) {
                fetch(routes.sendNotificationUrl.replace(':id', clientId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#notificationModal').modal('hide');
                            showAlert('Success', data.message || 'Notification sent successfully', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to send notification');
                        }
                    })
                    .catch(error => {
                        showAlert('Error', error.message || 'Failed to send notification', 'error');
                    });
            }

            /**
             * Display validation errors
             */
            function displayValidationErrors(errors) {
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));

                for (const [field, messages] of Object.entries(errors)) {
                    const errorElement = document.getElementById(`${field}-error`);
                    const inputElement = document.getElementById(field);
                    if (errorElement && inputElement) {
                        errorElement.textContent = Array.isArray(messages) ? messages.join(', ') : messages;
                        inputElement.classList.add('is-invalid');
                    }
                }
            }

            /**
             * Preview image
             */
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.querySelector('img').src = e.target.result;
                        preview.classList.remove('d-none');
                    };
                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.classList.add('d-none');
                }
            }

            /**
             * Reset form
             */
            function resetForm() {
                clientForm.reset();
                document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                document.querySelectorAll('.form-control').forEach(el => el.classList.remove('is-invalid'));
                photoPreview.classList.add('d-none');
                document.getElementById('client_id').value = '';
            }

            /**
             * Show alert with SweetAlert2 (Toast style matching brand management)
             */
            function showAlert(title, text, icon) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
                Toast.fire({
                    icon: icon,
                    title: title,
                    text: text
                });
            }
        });
    </script>
@endpush
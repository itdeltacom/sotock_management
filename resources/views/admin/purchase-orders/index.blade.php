@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>{{ __('Purchase Orders') }}</h6>
                        @can('create purchase-orders')
                            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-primary btn-sm ms-auto">
                                <i class="fas fa-plus me-1"></i> {{ __('New Purchase Order') }}
                            </a>
                        @endcan
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <!-- Filters Section -->
                        <div class="p-4">
                            <form id="filters-form" class="row g-3">
                                <div class="col-md-2">
                                    <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no">
                                </div>
                                <div class="col-md-2">
                                    <label for="supplier_id" class="form-label">{{ __('Supplier') }}</label>
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="">{{ __('All Suppliers') }}</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="warehouse_id" class="form-label">{{ __('Warehouse') }}</label>
                                    <select class="form-select" id="warehouse_id" name="warehouse_id">
                                        <option value="">{{ __('All Warehouses') }}</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="status" class="form-label">{{ __('Status') }}</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">{{ __('All Statuses') }}</option>
                                        @foreach($statuses as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label">{{ __('Date From') }}</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from">
                                </div>
                                <div class="col-md-2">
                                    <label for="date_to" class="form-label">{{ __('Date To') }}</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to">
                                </div>
                                <div class="col-md-12 text-end">
                                    <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary">
                                        <i class="fas fa-redo"></i> {{ __('Reset') }}
                                    </button>
                                    <button type="button" id="btn-filter" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> {{ __('Filter') }}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mx-4 mb-4">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-gradient-primary">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">
                                                        {{ __('Total Orders') }}</p>
                                                    <h5 class="font-weight-bolder text-white mt-2 mb-0"
                                                        id="stat-total-orders">
                                                        <div class="spinner-border spinner-border-sm text-white"
                                                            role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-cart text-primary text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-gradient-success">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">
                                                        {{ __('Received') }}</p>
                                                    <h5 class="font-weight-bolder text-white mt-2 mb-0"
                                                        id="stat-received-orders">
                                                        <div class="spinner-border spinner-border-sm text-white"
                                                            role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-check-bold text-success text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-gradient-info">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">
                                                        {{ __('Pending') }}</p>
                                                    <h5 class="font-weight-bolder text-white mt-2 mb-0"
                                                        id="stat-pending-orders">
                                                        <div class="spinner-border spinner-border-sm text-white"
                                                            role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-time-alarm text-info text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card bg-gradient-dark">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">
                                                        {{ __('This Month') }}</p>
                                                    <h5 class="font-weight-bolder text-white mt-2 mb-0"
                                                        id="stat-month-orders">
                                                        <div class="spinner-border spinner-border-sm text-white"
                                                            role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-calendar-grid-58 text-dark text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive p-3">
                            <table class="table align-items-center table-striped table-hover mb-0"
                                id="purchase-orders-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            {{ __('Reference No') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Supplier') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Warehouse') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Order Date') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Status') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Total') }}</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            {{ __('Created By') }}</th>
                                        <th
                                            class="text-end text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            {{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will fill this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">{{ __('Confirm Purchase Order') }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to confirm this purchase order? Once confirmed, it cannot be edited.') }}
                    </p>
                    <input type="hidden" id="confirm-order-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-order">{{ __('Confirm Order') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">{{ __('Cancel Purchase Order') }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to cancel this purchase order? This action cannot be undone.') }}</p>
                    <input type="hidden" id="cancel-order-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-cancel-order">{{ __('Cancel Order') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">{{ __('Delete Purchase Order') }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Are you sure you want to delete this purchase order? This action cannot be undone.') }}</p>
                    <input type="hidden" id="delete-order-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="button" class="btn btn-danger" id="btn-delete-order">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .filter-container {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }

        .table th {
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 0.25rem;
            font-size: 0.75em;
            font-weight: 700;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Load DataTable
            var table = $('#purchase-orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.purchase-orders.data') }}",
                    data: function (d) {
                        d.reference_no = $('#reference_no').val();
                        d.supplier_id = $('#supplier_id').val();
                        d.warehouse_id = $('#warehouse_id').val();
                        d.status = $('#status').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    { data: 'reference_no', name: 'reference_no' },
                    { data: 'supplier_name', name: 'supplier_name' },
                    { data: 'warehouse_name', name: 'warehouse_name' },
                    {
                        data: 'order_date',
                        name: 'order_date',
                        render: function (data) {
                            return new Date(data).toLocaleDateString();
                        }
                    },
                    { data: 'status_label', name: 'status', orderable: false, searchable: false },
                    {
                        data: 'total_amount',
                        name: 'total_amount',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    { data: 'created_by_name', name: 'created_by_name' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[3, 'desc']], // Order by date
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search purchase orders...",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    }
                },
                drawCallback: function () {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                    loadStats();
                }
            });

            // Filter button click
            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            // Reset filters
            $('#btn-reset-filter').click(function () {
                $('#filters-form')[0].reset();
                table.ajax.reload();
            });

            // Confirm Order
            $(document).on('click', '.btn-confirm', function () {
                var orderId = $(this).data('id');
                $('#confirm-order-id').val(orderId);
                $('#confirmModal').modal('show');
            });

            $('#btn-confirm-order').click(function () {
                var orderId = $('#confirm-order-id').val();

                $.ajax({
                    url: "{{ url('admin/purchase-orders') }}/" + orderId + "/confirm",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#confirmModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Cancel Order
            $(document).on('click', '.btn-cancel', function () {
                var orderId = $(this).data('id');
                $('#cancel-order-id').val(orderId);
                $('#cancelModal').modal('show');
            });

            $('#btn-cancel-order').click(function () {
                var orderId = $('#cancel-order-id').val();

                $.ajax({
                    url: "{{ url('admin/purchase-orders') }}/" + orderId + "/cancel",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#cancelModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Delete Order
            $(document).on('click', '.btn-delete', function () {
                var orderId = $(this).data('id');
                $('#delete-order-id').val(orderId);
                $('#deleteModal').modal('show');
            });

            $('#btn-delete-order').click(function () {
                var orderId = $('#delete-order-id').val();

                $.ajax({
                    url: "{{ url('admin/purchase-orders') }}/" + orderId,
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Load Stats
            function loadStats() {
                $.ajax({
                    url: "{{ route('admin.dashboard.chart-data') }}",
                    method: 'GET',
                    data: {
                        type: 'purchase_orders',
                    },
                    success: function (response) {
                        $('#stat-total-orders').text(response.total || 0);
                        $('#stat-received-orders').text(response.received || 0);
                        $('#stat-pending-orders').text(response.pending || 0);
                        $('#stat-month-orders').text(response.thisMonth || 0);
                    },
                    error: function (xhr) {
                        console.error('Error loading stats:', xhr);
                    }
                });
            }

            // Toast notifications
            function showToast(type, message) {
                var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
                var html = `
                    <div class="position-fixed top-1 end-1 z-index-2">
                        <div class="toast fade show p-2 mt-2 ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-body text-white">
                                ${message}
                            </div>
                        </div>
                    </div>
                `;

                // Remove existing toasts
                $('.toast').remove();

                // Append and show new toast
                $('body').append(html);

                // Auto hide after 3 seconds
                setTimeout(function () {
                    $('.toast').remove();
                }, 3000);
            }

            function showErrorToast(xhr) {
                var message = 'An error occurred. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    message = 'Error: ' + xhr.statusText;
                }

                showToast('error', message);
            }

            // Load initial stats
            loadStats();
        });
    </script>
@endpush
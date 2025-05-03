@extends('admin.layouts.master')

@section('title', 'Maintenance Due Soon')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Overdue</p>
                                    <h5 class="font-weight-bolder" id="overdue-counter">
                                        <div class="spinner-border spinner-border-sm text-danger" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-danger text-sm font-weight-bolder">
                                            <i class="fas fa-exclamation-circle"></i> Require immediate attention
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="fas fa-tools text-lg opacity-10" aria-hidden="true"></i>
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
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Due This Week</p>
                                    <h5 class="font-weight-bolder" id="due-this-week-counter">
                                        <div class="spinner-border spinner-border-sm text-warning" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-warning text-sm font-weight-bolder">
                                            <i class="fas fa-clock"></i> Schedule soon
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-calendar-alt text-lg opacity-10" aria-hidden="true"></i>
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
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Coming Up (15 Days)</p>
                                    <h5 class="font-weight-bolder" id="coming-up-counter">
                                        <div class="spinner-border spinner-border-sm text-info" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-info text-sm font-weight-bolder">
                                            <i class="fas fa-eye"></i> Monitor
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="fas fa-bell text-lg opacity-10" aria-hidden="true"></i>
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
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">All Cars</p>
                                    <h5 class="font-weight-bolder" id="all-cars-counter">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-primary text-sm font-weight-bolder">
                                            <i class="fas fa-car"></i> In fleet
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="fas fa-car text-lg opacity-10" aria-hidden="true"></i>
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
                                <h6 class="mb-0">Maintenance Due Soon</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.cars.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-car"></i> All Cars
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="due-maintenance-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Car
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Maintenance Type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last
                                            Performed</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Due
                                            Details</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
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
        .badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35em 0.65em;
        }

        .maintenance-badge {
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
        }

        .due-badge {
            display: inline-block;
            margin-bottom: 0.25rem;
        }

        /* Argon-style card and shadow effects */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            height: 100%;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        /* DataTable styling */
        table.dataTable {
            margin-top: 0 !important;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            border-bottom-width: 1px;
        }

        .table td {
            white-space: nowrap;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        /* Loading spinner */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Constants
            const MAINTENANCE_INTERVALS = {
                oil_change: { km: 10000, months: 6 },
                tire_rotation: { km: 8000, months: 6 },
                brake_service: { km: 20000, months: 12 },
                air_filter: { km: 15000, months: 12 },
                cabin_filter: { km: 15000, months: 12 },
                timing_belt: { km: 80000, months: 36 },
                spark_plugs: { km: 40000, months: 24 },
                battery_replacement: { km: 50000, months: 24 },
                transmission_service: { km: 60000, months: 24 },
                wheel_alignment: { km: 20000, months: 12 },
                fluid_flush: { km: 30000, months: 18 },
                general_service: { km: 15000, months: 12 }
            };

            // Toast notification configuration
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

            // Function to show toast notifications
            function showToast(title, text, icon) {
                Toast.fire({
                    icon: icon,
                    title: title,
                    text: text
                });
            }

            // DataTable initialization
            const dueMaintenanceTable = $('#due-maintenance-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.cars.maintenance.due-soon.datatable') }}",
                columns: [
                    { data: 'car_details', name: 'car_details' },
                    { data: 'maintenance_type', name: 'maintenance_type' },
                    { data: 'last_performed', name: 'last_performed' },
                    { data: 'due_details', name: 'due_details' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[3, 'asc']], // Sort by due date by default
                pageLength: 25,
                dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                },
                drawCallback: function () {
                    // Update counters
                    updateMaintenanceCounters();
                }
            });

            // Update counters when the table is first loaded
            updateMaintenanceCounters();

            // Function to update maintenance counters
            function updateMaintenanceCounters() {
                $.ajax({
                    url: "{{ route('admin.cars.maintenance.counters') }}",
                    type: 'GET',
                    success: function (response) {
                        $('#overdue-counter').html(response.overdue);
                        $('#due-this-week-counter').html(response.due_this_week);
                        $('#coming-up-counter').html(response.coming_up);
                        $('#all-cars-counter').html(response.all_cars);
                    },
                    error: function () {
                        // If there's an error, just show dashes
                        $('#overdue-counter').html('-');
                        $('#due-this-week-counter').html('-');
                        $('#coming-up-counter').html('-');
                        $('#all-cars-counter').html('-');
                    }
                });
            }

            // Add click event for "Manage" button
            $(document).on('click', '.manage-maintenance-btn', function () {
                const carId = $(this).data('car-id');
                window.location.href = "{{ route('admin.cars.maintenance.index', ':carId') }}".replace(':carId', carId);
            });

            // Function to handle quick add maintenance record
            $(document).on('click', '.quick-add-btn', function () {
                const carId = $(this).data('car-id');
                const maintenanceType = $(this).data('maintenance-type');

                // Redirect to the maintenance page with pre-filled type
                const url = "{{ route('admin.cars.maintenance.index', ':carId') }}".replace(':carId', carId);
                window.location.href = url + '?quick_add=' + maintenanceType;
            });

            // Export functionality
            $('#export-btn').click(function () {
                const exportFormat = $('#export-format').val();
                const url = "{{ route('admin.cars.maintenance.due-soon.export-csv') }}?format=" + exportFormat; window.location.href = url;
            });

            // Filter functionality
            $('#filter-maintenance-btn').click(function () {
                const filterType = $('#filter-maintenance-type').val();
                const filterStatus = $('#filter-maintenance-status').val();

                // Reload table with filters
                dueMaintenanceTable.ajax.url("{{ route('admin.cars.maintenance.due-soon.datatable') }}?type=" + filterType + "&status=" + filterStatus).load();
            });

            // Reset filter
            $('#reset-filter-btn').click(function () {
                $('#filter-maintenance-type').val('');
                $('#filter-maintenance-status').val('');

                // Reload table without filters
                dueMaintenanceTable.ajax.url("{{ route('admin.cars.maintenance.due-soon.datatable') }}").load();
            });

            // Refresh data every 5 minutes
            setInterval(function () {
                dueMaintenanceTable.ajax.reload(null, false);
                updateMaintenanceCounters();
            }, 300000); // 5 minutes in milliseconds
        });
    </script>
@endpush
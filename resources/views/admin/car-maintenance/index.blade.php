@extends('admin.layouts.master')

@section('title', 'Maintenance Records - ' . $car->brand_name . ' ' . $car->model)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Maintenance Records</h6>
                            </div>
                            <div class="col-6 text-end">
                                <div class="btn-group">
                                    <button type="button" class="btn bg-gradient-primary" id="addMaintenanceBtn">
                                        <i class="fas fa-plus"></i> Add Record
                                    </button>
                                    <button type="button" class="btn bg-gradient-info dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#"
                                                onclick="quickAddMaintenance('oil_change')">Add Oil Change</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="quickAddMaintenance('tire_rotation')">Add Tire Rotation</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="quickAddMaintenance('brake_service')">Add Brake Service</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="quickAddMaintenance('general_service')">Add General Service</a>
                                        </li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item"
                                                href="{{ route('admin.cars.maintenance.export-csv', $car->id) }}"><i
                                                    class="fas fa-file-csv"></i> Export CSV</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="window.carMaintenance.printMaintenanceHistory()"><i
                                                    class="fas fa-print"></i> Print History</a></li>
                                    </ul>
                                </div>
                                <a href="{{ route('admin.cars.show', $car->id) }}" class="btn bg-gradient-secondary ms-2">
                                    <i class="fas fa-arrow-left"></i> Back to Car
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pt-3 pb-2">
                        <!-- Car Info Summary Card -->
                        <div class="mx-3 mb-4">
                            <div class="card card-body p-3 border">
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Car</p>
                                        <h6 class="mb-0">
                                            {{ $car->brand_name }} {{ $car->model }} ({{ $car->year }})
                                        </h6>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">License Plate</p>
                                        <h6 class="mb-0">{{ $car->matricule }}</h6>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Current Mileage</p>
                                        <h6 class="mb-0" id="car-mileage">{{ number_format($car->mileage) }} km</h6>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Status</p>
                                        <h6 class="mb-0">
                                            @php
                                                $statusClass = [
                                                    'available' => 'success',
                                                    'rented' => 'primary',
                                                    'maintenance' => 'warning',
                                                    'unavailable' => 'danger'
                                                ][$car->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($car->status) }}</span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="maintenance-table" data-car-id="{{ $car->id }}"
                                width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date
                                            Performed</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Mileage</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Next
                                            Due</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cost
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Parts Replaced</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Performed By</th>
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

    <!-- Maintenance Record Modal -->
    <div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceModalLabel">Add Maintenance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="maintenanceForm">
                    @csrf
                    <input type="hidden" name="maintenance_id" id="maintenance_id">
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="maintenance_type" class="form-control-label">Maintenance Type <span
                                        class="text-danger">*</span></label>
                                <select class="form-control" id="maintenance_type" name="maintenance_type" required>
                                    <option value="">Select Type</option>
                                    <option value="oil_change">Oil Change</option>
                                    <option value="tire_rotation">Tire Rotation</option>
                                    <option value="brake_service">Brake Service</option>
                                    <option value="air_filter">Air Filter Replacement</option>
                                    <option value="cabin_filter">Cabin Filter Replacement</option>
                                    <option value="timing_belt">Timing Belt Replacement</option>
                                    <option value="spark_plugs">Spark Plugs Replacement</option>
                                    <option value="battery_replacement">Battery Replacement</option>
                                    <option value="transmission_service">Transmission Service</option>
                                    <option value="wheel_alignment">Wheel Alignment</option>
                                    <option value="fluid_flush">Fluid Flush</option>
                                    <option value="general_service">General Service</option>
                                    <option value="engine_repair">Engine Repair</option>
                                    <option value="suspension_repair">Suspension Repair</option>
                                    <option value="electrical_repair">Electrical Repair</option>
                                    <option value="inspection">Inspection</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback" id="maintenance_type-error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_performed" class="form-control-label">Date Performed <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_performed" name="date_performed" required
                                    value="{{ date('Y-m-d') }}">
                                <div class="invalid-feedback" id="date_performed-error"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mileage_at_service" class="form-control-label">Mileage at Service (km) <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="mileage_at_service" name="mileage_at_service"
                                    min="0" value="{{ $car->mileage }}" required>
                                <div class="invalid-feedback" id="mileage_at_service-error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cost" class="form-control-label">Total Cost (MAD)</label>
                                <input type="number" class="form-control" id="cost" name="cost" min="0" step="0.01">
                                <div class="invalid-feedback" id="cost-error"></div>
                                <small class="text-muted">Parts costs will be added to this total automatically</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="next_due_date" class="form-control-label">Next Due Date</label>
                                <input type="date" class="form-control" id="next_due_date" name="next_due_date">
                                <div class="invalid-feedback" id="next_due_date-error"></div>
                                <button type="button" class="btn btn-link btn-sm text-xs p-0 mt-1"
                                    id="calculate-next-due-date">
                                    Calculate based on maintenance type
                                </button>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="next_due_mileage" class="form-control-label">Next Due Mileage (km)</label>
                                <input type="number" class="form-control" id="next_due_mileage" name="next_due_mileage"
                                    min="{{ $car->mileage }}">
                                <div class="invalid-feedback" id="next_due_mileage-error"></div>
                                <button type="button" class="btn btn-link btn-sm text-xs p-0 mt-1"
                                    id="calculate-next-due-mileage">
                                    Calculate based on maintenance type
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="performed_by" class="form-control-label">Performed By</label>
                                <input type="text" class="form-control" id="performed_by" name="performed_by">
                                <div class="invalid-feedback" id="performed_by-error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="service_location" class="form-control-label">Service Location</label>
                                <input type="text" class="form-control" id="service_location" name="service_location">
                                <div class="invalid-feedback" id="service_location-error"></div>
                            </div>
                        </div>

                        <!-- Oil Change Specific Fields -->
                        <div id="oil-fields-section" class="d-none">
                            <div class="card p-3 mb-3 bg-light">
                                <h6 class="text-uppercase text-primary mb-3">Oil Change Details</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="oil_type" class="form-control-label">Oil Type</label>
                                        <select class="form-control" id="oil_type" name="oil_type">
                                            <option value="">Select Oil Type</option>
                                            <option value="0W-20">0W-20</option>
                                            <option value="0W-30">0W-30</option>
                                            <option value="5W-20">5W-20</option>
                                            <option value="5W-30">5W-30</option>
                                            <option value="5W-40">5W-40</option>
                                            <option value="10W-30">10W-30</option>
                                            <option value="10W-40">10W-40</option>
                                            <option value="15W-40">15W-40</option>
                                            <option value="20W-50">20W-50</option>
                                        </select>
                                        <div class="invalid-feedback" id="oil_type-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="oil_quantity_value" class="form-control-label">Oil Quantity</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="oil_quantity_value" step="0.1"
                                                min="0">
                                            <span class="input-group-text">L</span>
                                        </div>
                                        <input type="hidden" id="oil_quantity" name="oil_quantity">
                                        <div class="invalid-feedback" id="oil_quantity-error"></div>
                                    </div>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="oil_filter_replaced"
                                        name="oil_filter_replaced">
                                    <label class="form-check-label" for="oil_filter_replaced">
                                        Oil Filter Replaced
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Parts Replacement Section -->
                        <div class="card p-3 mb-3">
                            <h6 class="text-uppercase text-primary mb-3">Parts Replaced</h6>
                            <div id="parts-section">
                                <div class="parts-container mb-2">
                                    <div class="row parts-row mb-2">
                                        <div class="col-md-5">
                                            <select class="form-control part-name">
                                                <option value="">Select Part</option>
                                                <option value="Oil Filter">Oil Filter</option>
                                                <option value="Air Filter">Air Filter</option>
                                                <option value="Cabin Filter">Cabin Filter</option>
                                                <option value="Fuel Filter">Fuel Filter</option>
                                                <option value="Spark Plugs">Spark Plugs</option>
                                                <option value="Brake Pads">Brake Pads</option>
                                                <option value="Brake Discs">Brake Discs</option>
                                                <option value="Battery">Battery</option>
                                                <option value="Wiper Blades">Wiper Blades</option>
                                                <option value="Timing Belt">Timing Belt</option>
                                                <option value="Serpentine Belt">Serpentine Belt</option>
                                                <option value="Other">Other (specify)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control part-brand" placeholder="Brand">
                                        </div>
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <input type="number" class="form-control part-cost" placeholder="Cost"
                                                    min="0" step="0.01">
                                                <span class="input-group-text">MAD</span>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-link text-danger remove-part-btn"><i
                                                    class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm bg-gradient-info" id="add-part-btn">
                                    <i class="fas fa-plus"></i> Add Another Part
                                </button>
                                <input type="hidden" id="parts_replaced" name="parts_replaced">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-control-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4"></textarea>
                            <div class="invalid-feedback" id="notes-error"></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary" id="saveMaintenanceBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMaintenanceModal" tabindex="-1" aria-labelledby="deleteMaintenanceModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteMaintenanceModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="py-3 text-center">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p>Are you sure you want to delete this maintenance record? This action cannot be undone.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bg-gradient-danger" id="confirmDeleteMaintenanceBtn">Delete</button>
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

        .parts-row {
            transition: all 0.3s;
            padding: 8px;
            border-radius: 0.5rem;
        }

        .parts-row:hover {
            background-color: rgba(94, 114, 228, 0.05);
        }

        .remove-part-btn {
            padding: 0.25rem;
            margin-top: 0.25rem;
        }

        .part-info {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background-color: #f8f9fa;
        }

        .parts-summary .badge {
            font-size: 0.65rem;
            font-weight: 500;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        /**
         * Car Maintenance JavaScript
         * Handles functionality for car maintenance management in the Moroccan car rental system
         */
        document.addEventListener('DOMContentLoaded', function () {
            // Constants for maintenance intervals (in km)
            const MAINTENANCE_INTERVALS = {
                oil_change: 10000,
                tire_rotation: 8000,
                brake_service: 20000,
                air_filter: 15000,
                cabin_filter: 15000,
                timing_belt: 80000,
                spark_plugs: 40000,
                battery_replacement: 50000,
                transmission_service: 60000,
                wheel_alignment: 20000,
                fluid_flush: 30000,
                general_service: 15000
            };

            // Constants for maintenance intervals (in months)
            const MAINTENANCE_INTERVALS_MONTHS = {
                oil_change: 6,
                tire_rotation: 6,
                brake_service: 12,
                air_filter: 12,
                cabin_filter: 12,
                timing_belt: 36,
                spark_plugs: 24,
                battery_replacement: 24,
                transmission_service: 24,
                wheel_alignment: 12,
                fluid_flush: 18,
                general_service: 12
            };

            // Initialize DataTable if it exists on the page
            initDataTable();

            // Initialize form event handlers
            initFormHandlers();

            // Check for quick add parameter in URL
            checkForQuickAdd();

            /**
             * Initialize DataTable if element exists on the page
             */
            function initDataTable() {
                const maintenanceTable = $('#maintenance-table');
                if (maintenanceTable.length > 0) {
                    // Get car ID from data attribute
                    const carId = maintenanceTable.data('car-id');

                    // Initialize DataTable with AJAX source
                    window.maintenanceTable = maintenanceTable.DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: `/admin/cars/maintenance/${carId}/datatable`,
                        columns: [
                            { data: 'maintenance_title', name: 'maintenance_title' },
                            { data: 'date_performed', name: 'date_performed' },
                            {
                                data: 'mileage_at_service',
                                name: 'mileage_at_service',
                                render: function (data) {
                                    return data ? Number(data).toLocaleString() + ' km' : 'N/A';
                                }
                            },
                            { data: 'next_due', name: 'next_due' },
                            { data: 'status', name: 'status' },
                            {
                                data: 'cost',
                                name: 'cost',
                                render: function (data) {
                                    return data ? Number(data).toLocaleString() + ' MAD' : '-';
                                }
                            },
                            {
                                data: 'parts_replaced',
                                name: 'parts_replaced',
                                render: function (data) {
                                    if (!data) return '-';

                                    try {
                                        const parts = JSON.parse(data);
                                        if (!parts.length) return '-';

                                        let html = '<div class="parts-summary">';
                                        parts.forEach(part => {
                                            html += `<span class="badge bg-info me-1 mb-1">${part.name}</span>`;
                                        });
                                        html += '</div>';

                                        return html;
                                    } catch (e) {
                                        return data || '-';
                                    }
                                }
                            },
                            { data: 'performed_by', name: 'performed_by' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        order: [[1, 'desc']], // Sort by date performed by default
                        pageLength: 25,
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search maintenance",
                            paginate: {
                                previous: '<i class="fas fa-chevron-left"></i>',
                                next: '<i class="fas fa-chevron-right"></i>'
                            }
                        }
                    });
                }

                // Initialize Due Soon DataTable if it exists
                const dueSoonTable = $('#due-maintenance-table');
                if (dueSoonTable.length > 0) {
                    window.dueSoonTable = dueSoonTable.DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: `/admin/cars/maintenance-due-soon/data`,
                        columns: [
                            { data: 'car_details', name: 'car_details' },
                            { data: 'maintenance_type', name: 'maintenance_type' },
                            { data: 'last_performed', name: 'last_performed' },
                            { data: 'due_details', name: 'due_details' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        order: [[3, 'asc']], // Sort by due details by default
                        pageLength: 25,
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search due maintenance",
                            paginate: {
                                previous: '<i class="fas fa-chevron-left"></i>',
                                next: '<i class="fas fa-chevron-right"></i>'
                            }
                        }
                    });
                }
            }

            /**
             * Initialize form event handlers
             */
            function initFormHandlers() {
                // Add Maintenance button click
                const addMaintenanceBtn = document.getElementById('addMaintenanceBtn');
                if (addMaintenanceBtn) {
                    addMaintenanceBtn.addEventListener('click', function () {
                        resetMaintenanceForm();

                        // Get current date in YYYY-MM-DD format
                        const today = new Date().toISOString().split('T')[0];
                        document.getElementById('date_performed').value = today;

                        // Set default mileage from the car's current mileage
                        const carMileage = document.getElementById('car-mileage');
                        if (carMileage) {
                            const mileage = parseInt(carMileage.textContent.replace(/,/g, ''));
                            document.getElementById('mileage_at_service').value = mileage;
                        }

                        // Update modal title and set method to POST
                        document.getElementById('maintenanceModalLabel').textContent = 'Add Maintenance Record';
                        document.getElementById('method').value = 'POST';

                        // Show the modal
                        const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
                        maintenanceModal.show();
                    });
                }

                // Maintenance type change event
                const maintenanceTypeSelect = document.getElementById('maintenance_type');
                if (maintenanceTypeSelect) {
                    maintenanceTypeSelect.addEventListener('change', function () {
                        updateMaintenanceFormBasedOnType(this.value);
                    });
                }

                // Oil quantity input
                const oilQuantityValueInput = document.getElementById('oil_quantity_value');
                if (oilQuantityValueInput) {
                    oilQuantityValueInput.addEventListener('input', function () {
                        document.getElementById('oil_quantity').value = this.value + 'L';
                    });
                }

                // Date performed change event
                const datePerformedInput = document.getElementById('date_performed');
                if (datePerformedInput) {
                    datePerformedInput.addEventListener('change', function () {
                        updateNextDueDate();
                    });
                }

                // Calculate next due date button
                const calculateNextDueDateBtn = document.getElementById('calculate-next-due-date');
                if (calculateNextDueDateBtn) {
                    calculateNextDueDateBtn.addEventListener('click', function () {
                        updateNextDueDate();
                    });
                }

                // Calculate next due mileage button
                const calculateNextDueMileageBtn = document.getElementById('calculate-next-due-mileage');
                if (calculateNextDueMileageBtn) {
                    calculateNextDueMileageBtn.addEventListener('click', function () {
                        updateNextDueMileage();
                    });
                }

                // Mileage at service change event
                const mileageAtServiceInput = document.getElementById('mileage_at_service');
                if (mileageAtServiceInput) {
                    mileageAtServiceInput.addEventListener('change', function () {
                        updateNextDueMileage();
                    });
                }

                // Add part button
                const addPartBtn = document.getElementById('add-part-btn');
                const partsContainer = document.querySelector('.parts-container');
                if (addPartBtn && partsContainer) {
                    addPartBtn.addEventListener('click', function () {
                        const newRow = document.querySelector('.parts-row').cloneNode(true);

                        // Clear values in the new row
                        newRow.querySelectorAll('input, select').forEach(input => {
                            input.value = '';
                        });
                        partsContainer.appendChild(newRow);

                        // Add event listener to remove button
                        newRow.querySelector('.remove-part-btn').addEventListener('click', function () {
                            newRow.remove();
                            updatePartsField();
                        });

                        // Add input event listeners
                        newRow.querySelectorAll('input, select').forEach(input => {
                            input.addEventListener('input', updatePartsField);
                        });
                    });

                    // Remove part row - initial setup
                    document.querySelectorAll('.remove-part-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            // Don't remove if it's the only row
                            if (document.querySelectorAll('.parts-row').length > 1) {
                                const row = this.closest('.parts-row');
                                row.remove();
                                updatePartsField();
                            } else {
                                // Just clear the inputs
                                const row = this.closest('.parts-row');
                                row.querySelectorAll('input, select').forEach(input => {
                                    input.value = '';
                                });
                                updatePartsField();
                            }
                        });
                    });

                    // Add input event listeners to initial fields
                    document.querySelectorAll('.parts-row input, .parts-row select').forEach(input => {
                        input.addEventListener('input', updatePartsField);
                    });
                }

                // Oil filter checkbox for oil changes
                const oilFilterCheckbox = document.getElementById('oil_filter_replaced');
                if (oilFilterCheckbox) {
                    oilFilterCheckbox.addEventListener('change', function () {
                        if (this.checked) {
                            // Check if oil filter is already in parts list
                            const partRows = document.querySelectorAll('.parts-row');
                            let oilFilterExists = false;

                            partRows.forEach(row => {
                                const partName = row.querySelector('.part-name').value;
                                if (partName === 'Oil Filter') {
                                    oilFilterExists = true;
                                }
                            });

                            // If not exists, add it
                            if (!oilFilterExists) {
                                // Get the first empty row or create a new one
                                let emptyRow = null;
                                partRows.forEach(row => {
                                    const partName = row.querySelector('.part-name').value;
                                    if (!partName) {
                                        emptyRow = row;
                                    }
                                });

                                if (!emptyRow) {
                                    // Create new row by clicking the add button
                                    document.getElementById('add-part-btn').click();
                                    emptyRow = document.querySelector('.parts-row:last-child');
                                }

                                // Fill the row with oil filter info
                                emptyRow.querySelector('.part-name').value = 'Oil Filter';
                                updatePartsField();
                            }
                        } else {
                            // Remove oil filter from parts list
                            const partRows = document.querySelectorAll('.parts-row');

                            partRows.forEach(row => {
                                const partName = row.querySelector('.part-name').value;
                                if (partName === 'Oil Filter') {
                                    // Clear inputs or remove row
                                    if (document.querySelectorAll('.parts-row').length > 1) {
                                        row.remove();
                                    } else {
                                        row.querySelectorAll('input, select').forEach(input => {
                                            input.value = '';
                                        });
                                    }
                                    updatePartsField();
                                }
                            });
                        }
                    });
                }

                // Form submission
                const maintenanceForm = document.getElementById('maintenanceForm');
                if (maintenanceForm) {
                    maintenanceForm.addEventListener('submit', function (event) {
                        event.preventDefault();
                        if (validateMaintenanceForm()) {
                            submitMaintenanceForm();
                        }
                    });
                }

                // Initialize edit maintenance buttons
                document.addEventListener('click', function (event) {
                    if (event.target.closest('.edit-maintenance')) {
                        const button = event.target.closest('.edit-maintenance');
                        const maintenanceId = button.dataset.id;
                        editMaintenanceRecord(maintenanceId);
                    }
                });

                // Initialize delete maintenance buttons
                document.addEventListener('click', function (event) {
                    if (event.target.closest('.delete-maintenance')) {
                        const button = event.target.closest('.delete-maintenance');
                        const maintenanceId = button.dataset.id;

                        // Show delete confirmation modal
                        document.getElementById('confirmDeleteMaintenanceBtn').dataset.id = maintenanceId;
                        const deleteModal = new bootstrap.Modal(document.getElementById('deleteMaintenanceModal'));
                        deleteModal.show();
                    }
                });

                // Delete confirmation button
                const confirmDeleteBtn = document.getElementById('confirmDeleteMaintenanceBtn');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.addEventListener('click', function () {
                        const maintenanceId = this.dataset.id;
                        const carId = document.querySelector('#maintenance-table').dataset.carId;

                        deleteMaintenanceRecord(carId, maintenanceId);
                    });
                }
            }

            /**
             * Update the hidden parts field with JSON data
             */
            function updatePartsField() {
                const parts = [];
                document.querySelectorAll('.parts-row').forEach(row => {
                    const name = row.querySelector('.part-name').value;
                    const brand = row.querySelector('.part-brand').value;
                    const cost = row.querySelector('.part-cost').value;

                    if (name) {
                        parts.push({
                            name: name,
                            brand: brand || null,
                            cost: cost || null
                        });
                    }
                });

                document.getElementById('parts_replaced').value = JSON.stringify(parts);

                // Update total cost
                calculateTotalCost();
            }

            /**
             * Calculate total cost from parts and main cost field
             */
            function calculateTotalCost() {
                // Get base cost
                const baseCost = parseFloat(document.getElementById('cost').value) || 0;

                // Calculate parts cost
                let partsCost = 0;
                document.querySelectorAll('.part-cost').forEach(costField => {
                    const cost = parseFloat(costField.value) || 0;
                    partsCost += cost;
                });

                // Update total cost field if it exists
                const totalCostField = document.getElementById('total_cost');
                if (totalCostField) {
                    totalCostField.value = (baseCost + partsCost).toFixed(2);
                }
            }

            /**
             * Reset maintenance form to default state
             */
            function resetMaintenanceForm() {
                const form = document.getElementById('maintenanceForm');
                if (form) {
                    form.reset();

                    // Clear hidden fields
                    document.getElementById('maintenance_id').value = '';
                    document.getElementById('method').value = 'POST';
                    document.getElementById('parts_replaced').value = JSON.stringify([]);

                    // Reset oil fields
                    document.getElementById('oil-fields-section').classList.add('d-none');
                    document.getElementById('oil_quantity').value = '';

                    // Reset parts container - keep first row but clear it
                    const partsContainer = document.querySelector('.parts-container');
                    if (partsContainer) {
                        // Keep only the first row
                        const firstRow = document.querySelector('.parts-row');
                        if (firstRow) {
                            // Clear inputs
                            firstRow.querySelectorAll('input, select').forEach(input => {
                                input.value = '';
                            });

                            // Remove all other rows
                            document.querySelectorAll('.parts-row:not(:first-child)').forEach(row => {
                                row.remove();
                            });
                        }
                    }

                    // Clear validation errors
                    document.querySelectorAll('.is-invalid').forEach(field => {
                        field.classList.remove('is-invalid');
                    });
                    document.querySelectorAll('.invalid-feedback').forEach(field => {
                        field.textContent = '';
                    });

                    // Set a few defaults for quick data entry
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('date_performed').value = today;
                }
            }

            /**
             * Update form fields based on maintenance type
             */
            function updateMaintenanceFormBasedOnType(maintenanceType) {
                // Show/hide oil fields based on maintenance type
                const oilFields = document.getElementById('oil-fields-section');
                if (maintenanceType === 'oil_change') {
                    oilFields.classList.remove('d-none');
                } else {
                    oilFields.classList.add('d-none');
                }

                // Auto-fill parts based on maintenance type
                autoFillPartsBasedOnType(maintenanceType);

                // Update next due date and mileage based on type
                updateNextDueDate();
                updateNextDueMileage();
            }

            /**
             * Auto-fill parts based on maintenance type
             */
            function autoFillPartsBasedOnType(maintenanceType) {
                // Clear existing parts
                const firstRow = document.querySelector('.parts-row');
                if (firstRow) {
                    firstRow.querySelectorAll('input, select').forEach(input => {
                        input.value = '';
                    });
                }

                // Remove additional rows
                document.querySelectorAll('.parts-row:not(:first-child)').forEach(row => {
                    row.remove();
                });

                // Determine parts based on maintenance type
                let partsToAdd = [];

                switch (maintenanceType) {
                    case 'oil_change':
                        partsToAdd.push({ name: 'Oil Filter' });
                        // Set the oil filter checkbox
                        const oilFilterCheckbox = document.getElementById('oil_filter_replaced');
                        if (oilFilterCheckbox) {
                            oilFilterCheckbox.checked = true;
                        }
                        break;
                    case 'air_filter':
                        partsToAdd.push({ name: 'Air Filter' });
                        break;
                    case 'cabin_filter':
                        partsToAdd.push({ name: 'Cabin Filter' });
                        break;
                    case 'brake_service':
                        partsToAdd.push({ name: 'Brake Pads' });
                        partsToAdd.push({ name: 'Brake Discs' });
                        break;
                    case 'spark_plugs':
                        partsToAdd.push({ name: 'Spark Plugs' });
                        break;
                    case 'timing_belt':
                        partsToAdd.push({ name: 'Timing Belt' });
                        break;
                    case 'battery_replacement':
                        partsToAdd.push({ name: 'Battery' });
                        break;
                    case 'tire_rotation':
                        // No parts typically replaced
                        break;
                }

                // Add parts to the form
                if (partsToAdd.length > 0) {
                    // First part goes in the existing row
                    if (firstRow) {
                        firstRow.querySelector('.part-name').value = partsToAdd[0].name;
                    }

                    // Add additional rows for remaining parts
                    for (let i = 1; i < partsToAdd.length; i++) {
                        document.getElementById('add-part-btn').click();
                        const newRow = document.querySelector('.parts-row:last-child');
                        if (newRow) {
                            newRow.querySelector('.part-name').value = partsToAdd[i].name;
                        }
                    }

                    // Update the hidden field
                    updatePartsField();
                }
            }

            /**
             * Calculate next due date based on maintenance type and date performed
             */
            function updateNextDueDate() {
                const maintenanceType = document.getElementById('maintenance_type').value;
                const datePerformed = document.getElementById('date_performed').value;

                if (maintenanceType && datePerformed) {
                    const months = MAINTENANCE_INTERVALS_MONTHS[maintenanceType] || 0;

                    if (months > 0) {
                        const performedDate = new Date(datePerformed);
                        const nextDueDate = new Date(performedDate);
                        nextDueDate.setMonth(nextDueDate.getMonth() + months);

                        document.getElementById('next_due_date').value = nextDueDate.toISOString().split('T')[0];
                    }
                }
            }

            /**
             * Calculate next due mileage based on maintenance type and current mileage
             */
            function updateNextDueMileage() {
                const maintenanceType = document.getElementById('maintenance_type').value;
                const mileageAtService = parseInt(document.getElementById('mileage_at_service').value) || 0;

                if (maintenanceType && mileageAtService > 0) {
                    const kmInterval = MAINTENANCE_INTERVALS[maintenanceType] || 0;

                    if (kmInterval > 0) {
                        document.getElementById('next_due_mileage').value = mileageAtService + kmInterval;
                    }
                }
            }

            /**
             * Validate maintenance form
             */
            function validateMaintenanceForm() {
                let isValid = true;

                // Required fields
                document.querySelectorAll('#maintenanceForm [required]').forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        const errorElement = document.getElementById(`${field.id}-error`);
                        if (errorElement) {
                            errorElement.textContent = 'This field is required';
                        }
                        isValid = false;
                    }
                });

                // Validate oil fields if oil change is selected
                if (document.getElementById('maintenance_type').value === 'oil_change') {
                    const oilType = document.getElementById('oil_type');
                    if (!oilType.value) {
                        oilType.classList.add('is-invalid');
                        document.getElementById('oil_type-error').textContent = 'Please select an oil type';
                        isValid = false;
                    }

                    const oilQuantityValue = document.getElementById('oil_quantity_value');
                    if (!oilQuantityValue.value) {
                        oilQuantityValue.classList.add('is-invalid');
                        document.getElementById('oil_quantity-error').textContent = 'Please enter oil quantity';
                        isValid = false;
                    }
                }

                // Validate mileage at service - should be >= current car mileage
                const mileageAtService = parseInt(document.getElementById('mileage_at_service').value) || 0;
                const carMileage = document.getElementById('car-mileage');
                if (carMileage) {
                    const currentMileage = parseInt(carMileage.textContent.replace(/,/g, ''));
                    if (mileageAtService < currentMileage) {
                        document.getElementById('mileage_at_service').classList.add('is-invalid');
                        document.getElementById('mileage_at_service-error').textContent = 'Mileage cannot be less than current car mileage';
                        isValid = false;
                    }
                }

                // Validate next due mileage - should be > mileage at service
                const nextDueMileage = document.getElementById('next_due_mileage');
                if (nextDueMileage.value) {
                    const nextDueMileageValue = parseInt(nextDueMileage.value) || 0;
                    if (nextDueMileageValue <= mileageAtService) {
                        nextDueMileage.classList.add('is-invalid');
                        document.getElementById('next_due_mileage-error').textContent = 'Next due mileage must be greater than mileage at service';
                        isValid = false;
                    }
                }

                // Validate next due date - should be in the future
                const nextDueDate = document.getElementById('next_due_date');
                if (nextDueDate.value) {
                    const nextDueDateValue = new Date(nextDueDate.value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    if (nextDueDateValue < today) {
                        nextDueDate.classList.add('is-invalid');
                        document.getElementById('next_due_date-error').textContent = 'Next due date must be in the future';
                        isValid = false;
                    }
                }

                // If not valid, show an alert
                if (!isValid) {
                    Swal.fire({
                        title: 'Validation Error',
                        text: 'Please check the form for errors',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }

                return isValid;
            }

            /**
             * Submit maintenance form via AJAX
             */
            function submitMaintenanceForm() {
                const form = document.getElementById('maintenanceForm');
                const formData = new FormData(form);
                const method = document.getElementById('method').value;
                const carId = document.querySelector('#maintenance-table').dataset.carId;

                // Determine URL based on method
                let url = `/admin/cars/maintenance/${carId}`;
                if (method === 'PUT') {
                    const maintenanceId = document.getElementById('maintenance_id').value;
                    url = `/admin/cars/maintenance/${maintenanceId}`;
                }

                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                // If method is PUT, add _method field for Laravel
                if (method === 'PUT') {
                    formData.append('_method', 'PUT');
                }

                // Show loading
                const saveBtn = document.getElementById('saveMaintenanceBtn');
                const originalBtnText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                saveBtn.disabled = true;

                // Send AJAX request
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showToast('Success', data.message, 'success');

                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('maintenanceModal'));
                            modal.hide();

                            // Reload DataTable
                            if (window.maintenanceTable) {
                                window.maintenanceTable.ajax.reload();
                            }

                            // Update car mileage if it was increased
                            if (data.car && data.car.mileage) {
                                const carMileageEl = document.getElementById('car-mileage');
                                if (carMileageEl) {
                                    carMileageEl.textContent = Number(data.car.mileage).toLocaleString();
                                }
                            }
                        } else {
                            // Show error message
                            showToast('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred while saving the maintenance record.', 'error');
                    })
                    .finally(() => {
                        // Restore button
                        saveBtn.innerHTML = originalBtnText;
                        saveBtn.disabled = false;
                    });
            }

            /**
             * Edit maintenance record
             * @param {number} maintenanceId - ID of the maintenance record to edit
             */
            function editMaintenanceRecord(maintenanceId) {
                const carId = document.querySelector('#maintenance-table').dataset.carId;
                const url = `/admin/cars/maintenance/${carId}/${maintenanceId}/edit`;
                // Show loading
                showLoading(true);

                // Fetch maintenance record data
                fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const maintenance = data.maintenance;

                            // Reset form first
                            resetMaintenanceForm();

                            // Populate form with maintenance data
                            document.getElementById('maintenance_id').value = maintenance.id;
                            document.getElementById('maintenance_type').value = maintenance.maintenance_type;
                            document.getElementById('date_performed').value = maintenance.date_performed;
                            document.getElementById('mileage_at_service').value = maintenance.mileage_at_service;
                            document.getElementById('cost').value = maintenance.cost || '';
                            document.getElementById('next_due_date').value = maintenance.next_due_date || '';
                            document.getElementById('next_due_mileage').value = maintenance.next_due_mileage || '';
                            document.getElementById('performed_by').value = maintenance.performed_by || '';

                            if (maintenance.service_location) {
                                document.getElementById('service_location').value = maintenance.service_location;
                            }

                            // Oil change specific fields
                            if (maintenance.maintenance_type === 'oil_change') {
                                document.getElementById('oil-fields-section').classList.remove('d-none');

                                if (maintenance.oil_type) {
                                    document.getElementById('oil_type').value = maintenance.oil_type;
                                }

                                if (maintenance.oil_quantity) {
                                    document.getElementById('oil_quantity').value = maintenance.oil_quantity;
                                    // Extract quantity value (remove 'L' if present)
                                    const quantityValue = maintenance.oil_quantity.replace('L', '');
                                    document.getElementById('oil_quantity_value').value = quantityValue;
                                }

                                // Check if oil filter was replaced
                                if (maintenance.parts_replaced) {
                                    try {
                                        const parts = JSON.parse(maintenance.parts_replaced);
                                        const oilFilterReplaced = parts.some(part => part.name === 'Oil Filter');
                                        document.getElementById('oil_filter_replaced').checked = oilFilterReplaced;
                                    } catch (e) {
                                        console.error('Error parsing parts JSON:', e);
                                    }
                                }
                            }

                            // Parts replaced
                            if (maintenance.parts_replaced) {
                                fillPartsFields(maintenance.parts_replaced);
                            }

                            document.getElementById('notes').value = maintenance.notes || '';

                            // Update form based on maintenance type
                            updateMaintenanceFormBasedOnType(maintenance.maintenance_type);

                            // Set method to PUT for update
                            document.getElementById('method').value = 'PUT';

                            // Update modal title
                            document.getElementById('maintenanceModalLabel').textContent = 'Edit Maintenance Record';

                            // Show modal
                            const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
                            maintenanceModal.show();
                        } else {
                            // Show error message
                            showToast('Error', data.message || 'Failed to load maintenance record', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred while fetching the maintenance record', 'error');
                    })
                    .finally(() => {
                        // Hide loading
                        showLoading(false);
                    });
            }

            /**
             * Fill parts fields from JSON
             */
            function fillPartsFields(partsJson) {
                try {
                    const parts = JSON.parse(partsJson);

                    // Clear existing rows except the first one
                    const rows = document.querySelectorAll('.parts-row');
                    for (let i = 1; i < rows.length; i++) {
                        rows[i].remove();
                    }

                    // Fill the first row
                    if (parts.length > 0) {
                        const firstRow = document.querySelector('.parts-row');
                        firstRow.querySelector('.part-name').value = parts[0].name || '';
                        firstRow.querySelector('.part-brand').value = parts[0].brand || '';
                        firstRow.querySelector('.part-cost').value = parts[0].cost || '';

                        // Add additional rows for remaining parts
                        for (let i = 1; i < parts.length; i++) {
                            document.getElementById('add-part-btn').click();
                            const newRow = document.querySelector('.parts-row:last-child');
                            newRow.querySelector('.part-name').value = parts[i].name || '';
                            newRow.querySelector('.part-brand').value = parts[i].brand || '';
                            newRow.querySelector('.part-cost').value = parts[i].cost || '';
                        }
                    }

                    // Update the hidden field
                    updatePartsField();
                } catch (e) {
                    console.error('Error parsing parts JSON:', e);
                }
            }

            /**
             * Delete maintenance record
             * @param {number} carId - ID of the car
             * @param {number} maintenanceId - ID of the maintenance record to delete
             */
            function deleteMaintenanceRecord(carId, maintenanceId) {
                const url = `/admin/cars/maintenance/${carId}/${maintenanceId}`;
                // Show loading on delete button
                const deleteBtn = document.getElementById('confirmDeleteMaintenanceBtn');
                const originalBtnText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deleteBtn.disabled = true;

                // Send delete request
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showToast('Success', data.message, 'success');

                            // Close modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteMaintenanceModal'));
                            modal.hide();

                            // Reload DataTable
                            if (window.maintenanceTable) {
                                window.maintenanceTable.ajax.reload();
                            }
                        } else {
                            // Show error message
                            showToast('Error', data.message || 'Failed to delete maintenance record', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Error', 'An error occurred while deleting the maintenance record', 'error');
                    })
                    .finally(() => {
                        // Restore button
                        deleteBtn.innerHTML = originalBtnText;
                        deleteBtn.disabled = false;
                    });
            }

            /**
             * Show toast notification
             * @param {string} title - Toast title
             * @param {string} message - Toast message
             * @param {string} type - Toast type (success, error, warning, info)
             */
            function showToast(title, message, type) {
                // Check if SweetAlert2 is available
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: type,
                        title: title,
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    // Fallback to alert
                    alert(`${title}: ${message}`);
                }
            }

            /**
             * Show/hide loading overlay
             * @param {boolean} show - Whether to show or hide loading
             */
            function showLoading(show) {
                // Create or remove loading overlay
                const existingOverlay = document.querySelector('.loading-overlay');

                if (show) {
                    if (!existingOverlay) {
                        const overlay = document.createElement('div');
                        overlay.className = 'loading-overlay';
                        overlay.innerHTML = `
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        `;
                        document.body.appendChild(overlay);
                    }
                } else {
                    if (existingOverlay) {
                        existingOverlay.remove();
                    }
                }
            }

            /**
             * Check for quick add parameter in URL
             */
            function checkForQuickAdd() {
                const urlParams = new URLSearchParams(window.location.search);
                const quickAdd = urlParams.get('quick_add');

                if (quickAdd) {
                    quickAddMaintenance(quickAdd);

                    // Remove parameter from URL without reloading
                    const url = new URL(window.location);
                    url.searchParams.delete('quick_add');
                    window.history.replaceState({}, '', url);
                }
            }

            /**
             * Quick add maintenance record
             * @param {string} maintenanceType - Type of maintenance to add
             */
            function quickAddMaintenance(maintenanceType) {
                // Reset form
                resetMaintenanceForm();

                // Set maintenance type
                const maintenanceTypeSelect = document.getElementById('maintenance_type');
                if (maintenanceTypeSelect) {
                    maintenanceTypeSelect.value = maintenanceType;

                    // Trigger change event to update form
                    const event = new Event('change');
                    maintenanceTypeSelect.dispatchEvent(event);
                }

                // Get current date in YYYY-MM-DD format
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('date_performed').value = today;

                // Set default mileage from the car's current mileage
                const carMileage = document.getElementById('car-mileage');
                if (carMileage) {
                    const mileage = parseInt(carMileage.textContent.replace(/,/g, ''));
                    document.getElementById('mileage_at_service').value = mileage;
                }

                // Update modal title and set method to POST
                document.getElementById('maintenanceModalLabel').textContent = 'Add Maintenance Record';
                document.getElementById('method').value = 'POST';

                // Show the modal
                const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
                maintenanceModal.show();
            }

            /**
             * Export maintenance records to CSV, Excel or PDF
             * @param {string} format - Export format (csv, xlsx, pdf)
             */
            function exportMaintenanceRecords(format) {
                const carId = document.querySelector('#maintenance-table').dataset.carId;
                const url = `/admin/cars/${carId}/maintenance/export?format=${format}`;

                // Redirect to export URL
                window.location.href = url;
            }

            // Expose some functions to global scope for use in HTML
            window.carMaintenance = {
                exportRecords: exportMaintenanceRecords,
                addRecord: function () {
                    resetMaintenanceForm();
                    document.getElementById('maintenanceModalLabel').textContent = 'Add Maintenance Record';
                    document.getElementById('method').value = 'POST';
                    const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
                    maintenanceModal.show();
                },
                deleteRecord: function (maintenanceId) {
                    const carId = document.querySelector('#maintenance-table').dataset.carId;
                    document.getElementById('confirmDeleteMaintenanceBtn').dataset.id = maintenanceId;
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMaintenanceModal'));
                    deleteModal.show();
                },
                editRecord: editMaintenanceRecord,
                printMaintenanceHistory: function () {
                    const carId = document.querySelector('#maintenance-table').dataset.carId;
                    const url = `/admin/cars/${carId}/maintenance/print`;
                    window.open(url, '_blank');
                },
                quickAddMaintenance: quickAddMaintenance
            };
        });
    </script>
@endpush
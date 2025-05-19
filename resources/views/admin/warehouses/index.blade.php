@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Warehouses</h6>
                        @can('create warehouses')
                            <button class="btn btn-primary btn-sm ms-auto" id="btn-add-warehouse">
                                <i class="fas fa-plus me-1"></i> Add Warehouse
                            </button>
                        @endcan
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="row mx-4 mt-3">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-white bg-gradient-primary">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Warehouses
                                                    </p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $totalWarehouses ?? 0 }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-building text-primary text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <div class="card text-white bg-gradient-success">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active
                                                        Warehouses</p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $activeWarehouses ?? 0 }}
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
                        </div>

                        <!-- Filters -->
                        <div class="row mx-4 mt-2">
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Filters</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label for="filter-search" class="form-label">Search</label>
                                                <input type="text" class="form-control" id="filter-search"
                                                    placeholder="Search by name, code, location...">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label for="filter-status" class="form-label">Status</label>
                                                <select class="form-control" id="filter-status">
                                                    <option value="">All Status</option>
                                                    <option value="1">Active</option>
                                                    <option value="0">Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary btn-sm" id="btn-apply-filters">
                                                    <i class="fas fa-filter me-1"></i> Apply Filters
                                                </button>
                                                <button class="btn btn-secondary btn-sm" id="btn-reset-filters">
                                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive p-3">
                            <table class="table align-items-center justify-content-center mb-0" id="warehouses-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Name</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Location</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Products</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-end opacity-7 ps-2">
                                            Stock Value</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-end opacity-7">
                                            Actions</th>
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

        <!-- Add/Edit Warehouse Modal -->
        <div class="modal fade" id="warehouseModal" tabindex="-1" role="dialog" aria-labelledby="warehouseModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="warehouseModalLabel">Add New Warehouse</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form id="warehouseForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="_method" id="method" value="POST">
                            <input type="hidden" name="id" id="warehouse_id">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code" class="form-control-label">Warehouse Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" required>
                                        <div class="invalid-feedback" id="code-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Warehouse Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="location" class="form-control-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location">
                                <div class="invalid-feedback" id="location-error"></div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="description" class="form-control-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback" id="description-error"></div>
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save-warehouse">Save Warehouse</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Warehouse Modal -->
        <div class="modal fade" id="viewWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="viewWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewWarehouseModalLabel">Warehouse Details</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h3 id="view-name" class="font-weight-bold mb-0"></h3>
                                        <p id="view-code" class="text-sm text-muted mb-0"></p>
                                        <p id="view-location" class="text-sm mb-0"></p>
                                    </div>
                                    <span class="badge badge-pill bg-gradient-success" id="view-status">Active</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Description</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p id="view-description" class="text-sm mb-0">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Stock Summary</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h4 id="view-total-products" class="mb-0">0</h4>
                                                    <p class="text-sm text-muted">Products</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h4 id="view-total-quantity" class="mb-0">0</h4>
                                                    <p class="text-sm text-muted">Units</p>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="text-center">
                                                    <h4 id="view-total-value" class="mb-0">0.00</h4>
                                                    <p class="text-sm text-muted">Total Value</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="#" id="view-stock-details" class="btn btn-sm btn-outline-primary">View
                                                Inventory</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Recent Stock Movements</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Product</th>
                                                        <th>Type</th>
                                                        <th class="text-end">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="view-recent-movements">
                                                    <!-- Recent movements will be filled dynamically -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="no-movements" class="text-center py-2 d-none">
                                            <p class="text-muted mb-0">No recent stock movements.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @can('edit warehouses')
                            <button type="button" class="btn btn-primary" id="btn-edit-view">Edit Warehouse</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteWarehouseModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteWarehouseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteWarehouseModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this warehouse: <span id="delete-warehouse-name"
                            class="font-weight-bold"></span>?
                        <input type="hidden" id="delete-warehouse-id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirm-delete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTable for warehouses
            var table = $('#warehouses-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.warehouses.data') }}",
                    data: function (d) {
                        d.search = $('#filter-search').val();
                        d.active = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'location',
                        name: 'location',
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        data: 'active',
                        name: 'active',
                        className: 'text-center',
                        render: function (data) {
                            if (data) {
                                return '<span class="badge badge-sm bg-gradient-success">Active</span>';
                            }
                            return '<span class="badge badge-sm bg-gradient-secondary">Inactive</span>';
                        }
                    },
                    {
                        data: 'products_count',
                        name: 'products_count',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    },
                    {
                        data: 'stock_value',
                        name: 'stock_value',
                        className: 'text-end',
                        render: function (data) {
                            return '<span class="text-primary font-weight-bold">' + data + '</span>';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                order: [[1, 'asc']]
            });

            // Apply filters
            $('#btn-apply-filters').click(function () {
                table.ajax.reload();
            });

            // Reset filters
            $('#btn-reset-filters').click(function () {
                $('#filter-search').val('');
                $('#filter-status').val('');
                table.ajax.reload();
            });

            // Reset form when warehouse modal is closed
            $('#warehouseModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Open modal to add new warehouse
            $('#btn-add-warehouse').click(function () {
                resetForm();
                $('#warehouseModalLabel').text('Add New Warehouse');
                $('#method').val('POST');
                $('#warehouseModal').modal('show');
            });

            // Handle edit button click
            $(document).on('click', '.btn-edit', function () {
                resetForm();
                var warehouseId = $(this).data('id');
                $('#warehouseModalLabel').text('Edit Warehouse');
                $('#method').val('PUT');
                $('#warehouse_id').val(warehouseId);

                $.ajax({
                    url: "{{ route('admin.warehouses.edit', ['id' => ':id']) }}".replace(':id', warehouseId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var warehouse = response.warehouse;
                            $('#code').val(warehouse.code);
                            $('#name').val(warehouse.name);
                            $('#location').val(warehouse.location);
                            $('#description').val(warehouse.description);
                            $('#active').prop('checked', warehouse.active);

                            $('#warehouseModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Switch from view modal to edit modal
            $('#btn-edit-view').click(function () {
                var warehouseId = $('#view-warehouse-modal-id').val();
                $('#viewWarehouseModal').modal('hide');
                $('.btn-edit[data-id="' + warehouseId + '"]').click();
            });

            // Handle view button click
            $(document).on('click', '.btn-view', function () {
                var warehouseId = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.warehouses.show', ['id' => ':id']) }}".replace(':id', warehouseId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var warehouse = response.warehouse;
                            $('#view-warehouse-modal-id').val(warehouse.id);
                            $('#view-name').text(warehouse.name);
                            $('#view-code').text('Code: ' + warehouse.code);
                            $('#view-location').text(warehouse.location || '');

                            $('#view-description').text(warehouse.description || 'No description available');

                            $('#view-status').removeClass('bg-gradient-success bg-gradient-secondary')
                                .addClass(warehouse.active ? 'bg-gradient-success' : 'bg-gradient-secondary')
                                .text(warehouse.active ? 'Active' : 'Inactive');

                            // Set stock summary
                            $('#view-total-products').text(response.stockData.total_products);
                            $('#view-total-quantity').text(response.stockData.total_quantity);
                            $('#view-total-value').text(parseFloat(response.stockData.total_value).toFixed(2));

                            // Set URL for inventory link
                            $('#view-stock-details').attr('href', "{{ route('admin.inventory.warehouse', ['id' => ':id']) }}".replace(':id', warehouse.id));

                            // Fill recent movements
                            if (response.recentMovements && response.recentMovements.length > 0) {
                                let movementsHtml = '';
                                response.recentMovements.forEach(function (movement) {
                                    let typeClass = '';
                                    let typeText = '';

                                    switch (movement.movement_type) {
                                        case 'in':
                                            typeClass = 'bg-success';
                                            typeText = 'IN';
                                            break;
                                        case 'out':
                                            typeClass = 'bg-danger';
                                            typeText = 'OUT';
                                            break;
                                    }

                                    let date = new Date(movement.created_at).toLocaleString();

                                    movementsHtml += `
                                            <tr>
                                                <td>${date}</td>
                                                <td>${movement.product ? movement.product.name : 'Unknown'}</td>
                                                <td><span class="badge badge-sm ${typeClass}">${typeText}</span></td>
                                                <td class="text-end">${movement.quantity}</td>
                                            </tr>
                                        `;
                                });

                                $('#view-recent-movements').html(movementsHtml);
                                $('#no-movements').addClass('d-none');
                            } else {
                                $('#view-recent-movements').html('');
                                $('#no-movements').removeClass('d-none');
                            }

                            $('#viewWarehouseModal').modal('show');
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        showErrorToast(xhr);
                    }
                });
            });

            // Handle delete button click (open confirmation modal)
            $(document).on('click', '.btn-delete:not(.disabled)', function () {
                var warehouseId = $(this).data('id');
                var warehouseName = $(this).closest('tr').find('td:nth-child(2)').text();

                $('#delete-warehouse-id').val(warehouseId);
                $('#delete-warehouse-name').text(warehouseName);
                $('#deleteWarehouseModal').modal('show');
            });

            // Confirm delete action
            $('#confirm-delete').click(function () {
                var warehouseId = $('#delete-warehouse-id').val();

                $.ajax({
                    url: "{{ route('admin.warehouses.destroy', ['id' => ':id']) }}".replace(':id', warehouseId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteWarehouseModal').modal('hide');
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

            // Handle warehouse form submission (create/update)
            $('#warehouseForm').submit(function (e) {
                e.preventDefault();

                clearFormErrors();

                var formData = new FormData(this);
                formData.set('active', $('#active').is(':checked') ? '1' : '0');

                var method = $('#method').val();
                var url = "{{ route('admin.warehouses.store') }}";

                if (method === 'PUT') {
                    var warehouseId = $('#warehouse_id').val();
                    url = "{{ route('admin.warehouses.update', ['id' => ':id']) }}".replace(':id', warehouseId);
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#warehouseModal').modal('hide');
                            table.ajax.reload();
                            showToast('success', response.message);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            displayFormErrors(errors);
                        } else {
                            showErrorToast(xhr);
                        }
                    }
                });
            });

            // Reset form fields
            function resetForm() {
                $('#warehouseForm')[0].reset();
                $('#warehouse_id').val('');
                $('#active').prop('checked', true);
                clearFormErrors();
            }

            // Clear form validation errors
            function clearFormErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            // Display form validation errors
            function displayFormErrors(errors) {
                $.each(errors, function (field, messages) {
                    var input = $('#' + field);
                    var feedback = $('#' + field + '-error');

                    input.addClass('is-invalid');
                    feedback.text(messages[0]);
                });
            }

            // Show toast notification
            function showToast(type, message) {
                var bgClass = 'bg-' + (type === 'success' ? 'success' : 'danger');
                var html = `
                    <div class="position-fixed top-1 end-1 z-index-2">
                        <div class="toast fade p-2 mt-2 ${bgClass}" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-body text-white">
                                ${message}
                            </div>
                        </div>
                    </div>
                `;

                $('body').append(html);
                $('.toast').toast({
                    delay: 3000,
                    animation: true
                }).toast('show');

                setTimeout(function () {
                    $('.toast').remove();
                }, 3500);
            }

            // Show error toast for AJAX errors
            function showErrorToast(xhr) {
                var message = 'An error occurred. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.error) {
                    message = xhr.responseJSON.error;
                } else if (xhr.statusText) {
                    message = 'Error: ' + xhr.statusText;
                }

                showToast('error', message);
            }
        });
    </script>
@endpush
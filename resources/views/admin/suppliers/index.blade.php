@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Suppliers</h6>
                        @can('create suppliers')
                            <button class="btn btn-primary btn-sm ms-auto" id="btn-add-supplier">
                                <i class="fas fa-plus me-1"></i> Add Supplier
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Suppliers
                                                    </p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $totalSuppliers ?? 0 }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                    <i class="ni ni-shop text-primary text-lg opacity-10"
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Suppliers
                                                    </p>
                                                    <h5 class="font-weight-bolder text-white mt-2">
                                                        {{ $activeSuppliers ?? 0 }}
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
                                                    placeholder="Search by name, code, contact person...">
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
                            <table class="table align-items-center justify-content-center mb-0" id="suppliers-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code
                                        </th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Name</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Contact Person</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Email/Phone</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Location</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Orders</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">
                                            Total Purchases</th>
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

        <!-- Add/Edit Supplier Modal -->
        <div class="modal fade" id="supplierModal" tabindex="-1" role="dialog" aria-labelledby="supplierModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="supplierModalLabel">Add New Supplier</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <form id="supplierForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" name="_method" id="method" value="POST">
                            <input type="hidden" name="id" id="supplier_id">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code" class="form-control-label">Supplier Code <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="code" name="code" required>
                                        <div class="invalid-feedback" id="code-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Supplier Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_person" class="form-control-label">Contact Person</label>
                                        <input type="text" class="form-control" id="contact_person" name="contact_person">
                                        <div class="invalid-feedback" id="contact_person-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                        <div class="invalid-feedback" id="email-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Phone</label>
                                        <input type="text" class="form-control" id="phone" name="phone">
                                        <div class="invalid-feedback" id="phone-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tax_id" class="form-control-label">Tax ID</label>
                                        <input type="text" class="form-control" id="tax_id" name="tax_id">
                                        <div class="invalid-feedback" id="tax_id-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address" class="form-control-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address">
                                        <div class="invalid-feedback" id="address-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="city" class="form-control-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city">
                                        <div class="invalid-feedback" id="city-error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country" class="form-control-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country">
                                        <div class="invalid-feedback" id="country-error"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <label for="notes" class="form-control-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                <div class="invalid-feedback" id="notes-error"></div>
                            </div>

                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="save-supplier">Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Supplier Modal -->
        <div class="modal fade" id="viewSupplierModal" tabindex="-1" role="dialog" aria-labelledby="viewSupplierModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewSupplierModalLabel">Supplier Details</h5>
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
                                    </div>
                                    <span class="badge badge-pill bg-gradient-success" id="view-status">Active</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Contact Information</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="text-sm mb-1"><strong>Contact Person:</strong> <span
                                                id="view-contact-person">-</span></p>
                                        <p class="text-sm mb-1"><strong>Email:</strong> <span id="view-email">-</span></p>
                                        <p class="text-sm mb-1"><strong>Phone:</strong> <span id="view-phone">-</span></p>
                                        <p class="text-sm mb-1"><strong>Tax ID:</strong> <span id="view-tax-id">-</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Address</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="text-sm mb-1"><strong>Address:</strong> <span id="view-address">-</span>
                                        </p>
                                        <p class="text-sm mb-1"><strong>City:</strong> <span id="view-city">-</span></p>
                                        <p class="text-sm mb-1"><strong>Country:</strong> <span id="view-country">-</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header p-3">
                                        <h6 class="mb-0">Notes</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <p id="view-notes" class="text-sm mb-0">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header p-3 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Purchase Orders</h6>
                                        <span class="badge bg-primary" id="view-orders-count">0</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Reference</th>
                                                        <th>Date</th>
                                                        <th>Warehouse</th>
                                                        <th>Status</th>
                                                        <th class="text-end">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="view-recent-orders">
                                                    <!-- Recent orders will be filled dynamically -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="no-orders" class="text-center py-2 d-none">
                                            <p class="text-muted mb-0">No purchase orders found for this supplier.</p>
                                        </div>
                                        <div class="text-center mt-3">
                                            <a href="#" id="view-all-orders" class="btn btn-sm btn-outline-primary">View All
                                                Orders</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        @can('edit suppliers')
                            <button type="button" class="btn btn-primary" id="btn-edit-view">Edit Supplier</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteSupplierModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSupplierModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete this supplier: <span id="delete-supplier-name"
                            class="font-weight-bold"></span>?
                        <input type="hidden" id="delete-supplier-id">
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
            // Initialize DataTable for suppliers
            var table = $('#suppliers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.suppliers.data') }}",
                    data: function (d) {
                        d.search = $('#filter-search').val();
                        d.active = $('#filter-status').val();
                    }
                },
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'contact_person',
                        name: 'contact_person',
                        render: function (data) {
                            return data || '-';
                        }
                    },
                    {
                        data: null,
                        name: 'email_phone',
                        render: function (data) {
                            let result = '';
                            if (data.email) {
                                result += '<span><i class="fas fa-envelope me-1"></i>' + data.email + '</span>';
                            }
                            if (data.phone) {
                                if (result) result += '<br>';
                                result += '<span><i class="fas fa-phone me-1"></i>' + data.phone + '</span>';
                            }
                            return result || '-';
                        }
                    },
                    {
                        data: null,
                        name: 'location',
                        render: function (data) {
                            let location = [];
                            if (data.city) location.push(data.city);
                            if (data.country) location.push(data.country);
                            return location.join(', ') || '-';
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
                        data: 'orders_count',
                        name: 'orders_count',
                        className: 'text-center',
                        render: function (data) {
                            return '<span class="badge bg-primary">' + data + '</span>';
                        }
                    },
                    {
                        data: 'total_purchases',
                        name: 'total_purchases',
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

            // Reset form when supplier modal is closed
            $('#supplierModal').on('hidden.bs.modal', function () {
                resetForm();
            });

            // Open modal to add new supplier
            $('#btn-add-supplier').click(function () {
                resetForm();
                $('#supplierModalLabel').text('Add New Supplier');
                $('#method').val('POST');
                $('#supplierModal').modal('show');
            });

            // Handle edit button click
            $(document).on('click', '.btn-edit', function () {
                resetForm();
                var supplierId = $(this).data('id');
                $('#supplierModalLabel').text('Edit Supplier');
                $('#method').val('PUT');
                $('#supplier_id').val(supplierId);

                $.ajax({
                    url: "{{ route('admin.suppliers.edit', ['id' => ':id']) }}".replace(':id', supplierId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var supplier = response.supplier;
                            $('#code').val(supplier.code);
                            $('#name').val(supplier.name);
                            $('#contact_person').val(supplier.contact_person);
                            $('#email').val(supplier.email);
                            $('#phone').val(supplier.phone);
                            $('#address').val(supplier.address);
                            $('#city').val(supplier.city);
                            $('#country').val(supplier.country);
                            $('#tax_id').val(supplier.tax_id);
                            $('#notes').val(supplier.notes);
                            $('#active').prop('checked', supplier.active);

                            $('#supplierModal').modal('show');
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
                var supplierId = $('#view-supplier-modal-id').val();
                $('#viewSupplierModal').modal('hide');
                $('.btn-edit[data-id="' + supplierId + '"]').click();
            });

            // Handle view button click
            $(document).on('click', '.btn-view', function () {
                var supplierId = $(this).data('id');

                $.ajax({
                    url: "{{ route('admin.suppliers.show', ['id' => ':id']) }}".replace(':id', supplierId),
                    method: 'GET',
                    success: function (response) {
                        if (response.success) {
                            var supplier = response.supplier;
                            $('#view-supplier-modal-id').val(supplier.id);
                            $('#view-name').text(supplier.name);
                            $('#view-code').text('Code: ' + supplier.code);

                            $('#view-contact-person').text(supplier.contact_person || '-');
                            $('#view-email').text(supplier.email || '-');
                            $('#view-phone').text(supplier.phone || '-');
                            $('#view-tax-id').text(supplier.tax_id || '-');

                            $('#view-address').text(supplier.address || '-');
                            $('#view-city').text(supplier.city || '-');
                            $('#view-country').text(supplier.country || '-');

                            $('#view-notes').text(supplier.notes || 'No notes available');

                            $('#view-status').removeClass('bg-gradient-success bg-gradient-secondary')
                                .addClass(supplier.active ? 'bg-gradient-success' : 'bg-gradient-secondary')
                                .text(supplier.active ? 'Active' : 'Inactive');

                            $('#view-orders-count').text(supplier.purchase_orders_count);

                            // Fill recent orders
                            if (supplier.recent_orders && supplier.recent_orders.length > 0) {
                                let ordersHtml = '';
                                supplier.recent_orders.forEach(function (order) {
                                    let statusClass = '';
                                    switch (order.status) {
                                        case 'draft': statusClass = 'bg-secondary'; break;
                                        case 'confirmed': statusClass = 'bg-info'; break;
                                        case 'partially_received': statusClass = 'bg-warning'; break;
                                        case 'received': statusClass = 'bg-success'; break;
                                        case 'cancelled': statusClass = 'bg-danger'; break;
                                    }

                                    ordersHtml += `
                                            <tr>
                                                <td>${order.reference_no}</td>
                                                <td>${order.order_date}</td>
                                                <td>${order.warehouse ? order.warehouse.name : '-'}</td>
                                                <td><span class="badge badge-sm ${statusClass}">${order.status}</span></td>
                                                <td class="text-end">${parseFloat(order.total_amount).toFixed(2)}</td>
                                            </tr>
                                        `;
                                });

                                $('#view-recent-orders').html(ordersHtml);
                                $('#no-orders').addClass('d-none');
                                $('#view-all-orders').attr('href', "{{ route('admin.purchase-orders.index') }}?supplier_id=" + supplier.id);
                            } else {
                                $('#view-recent-orders').html('');
                                $('#no-orders').removeClass('d-none');
                                $('#view-all-orders').addClass('d-none');
                            }

                            $('#viewSupplierModal').modal('show');
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
                var supplierId = $(this).data('id');
                var supplierName = $(this).closest('tr').find('td:nth-child(2)').text();

                $('#delete-supplier-id').val(supplierId);
                $('#delete-supplier-name').text(supplierName);
                $('#deleteSupplierModal').modal('show');
            });

            // Confirm delete action
            $('#confirm-delete').click(function () {
                var supplierId = $('#delete-supplier-id').val();

                $.ajax({
                    url: "{{ route('admin.suppliers.destroy', ['id' => ':id']) }}".replace(':id', supplierId),
                    method: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            $('#deleteSupplierModal').modal('hide');
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

            // Handle supplier form submission (create/update)
            $('#supplierForm').submit(function (e) {
                e.preventDefault();

                clearFormErrors();

                var formData = new FormData(this);
                formData.set('active', $('#active').is(':checked') ? '1' : '0');

                var method = $('#method').val();
                var url = "{{ route('admin.suppliers.store') }}";

                if (method === 'PUT') {
                    var supplierId = $('#supplier_id').val();
                    url = "{{ route('admin.suppliers.update', ['id' => ':id']) }}".replace(':id', supplierId);
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
                            $('#supplierModal').modal('hide');
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
                $('#supplierForm')[0].reset();
                $('#supplier_id').val('');
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
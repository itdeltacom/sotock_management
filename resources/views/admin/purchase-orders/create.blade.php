@extends('admin.layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>{{ __('Create New Purchase Order') }}</h6>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> {{ __('Back to List') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="purchase-order-form">
                            @csrf

                            <!-- General Information -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                                    <input type="text" class="form-control" id="reference_no" name="reference_no"
                                        value="{{ $refNo }}" readonly>
                                    <div class="invalid-feedback" id="reference_no-error"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="supplier_id" class="form-label">{{ __('Supplier') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="supplier_id" name="supplier_id" required>
                                        <option value="">{{ __('Select Supplier') }}</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="supplier_id-error"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="warehouse_id" class="form-label">{{ __('Warehouse') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                        <option value="">{{ __('Select Warehouse') }}</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="warehouse_id-error"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="order_date" class="form-label">{{ __('Order Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="order_date" name="order_date"
                                        value="{{ date('Y-m-d') }}" required>
                                    <div class="invalid-feedback" id="order_date-error"></div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="expected_delivery_date"
                                        class="form-label">{{ __('Expected Delivery Date') }}</label>
                                    <input type="date" class="form-control" id="expected_delivery_date"
                                        name="expected_delivery_date">
                                    <div class="invalid-feedback" id="expected_delivery_date-error"></div>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-header bg-light p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ __('Order Items') }}</h6>
                                                <button type="button" class="btn btn-sm btn-primary" id="add-item">
                                                    <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0" id="items-table">
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                                {{ __('Product') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Quantity') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Unit Price') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Tax (%)') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Discount (%)') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Subtotal') }}</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                                {{ __('Action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Items will be added here by JavaScript -->
                                                        <tr id="no-items-row">
                                                            <td colspan="7" class="text-center py-4">
                                                                <p class="text-sm mb-0">
                                                                    {{ __('No items added yet. Click "Add Item" to add products to this order.') }}
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <td colspan="5" class="text-end">
                                                                <p class="text-sm font-weight-bold mb-0">
                                                                    {{ __('Subtotal') }}:</p>
                                                            </td>
                                                            <td colspan="2">
                                                                <p class="text-sm font-weight-bold mb-0"
                                                                    id="subtotal-display">0.00</p>
                                                                <input type="hidden" name="subtotal" id="subtotal"
                                                                    value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="5" class="text-end">
                                                                <p class="text-sm font-weight-bold mb-0">
                                                                    {{ __('Tax Amount') }}:</p>
                                                            </td>
                                                            <td colspan="2">
                                                                <p class="text-sm font-weight-bold mb-0"
                                                                    id="tax-amount-display">0.00</p>
                                                                <input type="hidden" name="tax_amount" id="tax_amount"
                                                                    value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="5" class="text-end">
                                                                <p class="text-sm font-weight-bold mb-0">
                                                                    {{ __('Discount Amount') }}:</p>
                                                            </td>
                                                            <td colspan="2">
                                                                <p class="text-sm font-weight-bold mb-0"
                                                                    id="discount-amount-display">0.00</p>
                                                                <input type="hidden" name="discount_amount"
                                                                    id="discount_amount" value="0">
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="5" class="text-end">
                                                                <p class="text-sm font-weight-bold mb-0">
                                                                    {{ __('Grand Total') }}:</p>
                                                            </td>
                                                            <td colspan="2">
                                                                <p class="text-sm font-weight-bold mb-0"
                                                                    id="total-amount-display">0.00</p>
                                                                <input type="hidden" name="total_amount" id="total_amount"
                                                                    value="0">
                                                            </td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="row mt-4">
                                <div class="col-12 mb-3">
                                    <label for="notes" class="form-label">{{ __('Notes') }}</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"
                                        placeholder="{{ __('Enter any additional notes or instructions for this purchase order') }}"></textarea>
                                    <div class="invalid-feedback" id="notes-error"></div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary me-2">
                                            {{ __('Cancel') }}
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> {{ __('Save as Draft') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-labelledby="addItemModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addItemModalLabel">{{ __('Add Product') }}</h5>
                    <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="item-form">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="product_id" class="form-label">{{ __('Product') }} <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">{{ __('Select Product') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-unit="{{ $product->unit }}">{{ $product->name }}
                                            ({{ $product->code }})</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="product_id-error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">{{ __('Quantity') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="0.001"
                                        step="0.001" required>
                                    <span class="input-group-text" id="product-unit">-</span>
                                </div>
                                <div class="invalid-feedback" id="quantity-error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="unit_price" class="form-label">{{ __('Unit Price') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="unit_price" name="unit_price" min="0"
                                        step="0.01" required>
                                    <span class="input-group-text">{{ __('DH') }}</span>
                                </div>
                                <div class="invalid-feedback" id="unit_price-error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tax_rate" class="form-label">{{ __('Tax Rate (%)') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="tax_rate" name="tax_rate" min="0"
                                        step="0.01" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback" id="tax_rate-error"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="discount_rate" class="form-label">{{ __('Discount Rate (%)') }}</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="discount_rate" name="discount_rate"
                                        min="0" step="0.01" value="0">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="invalid-feedback" id="discount_rate-error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="item_notes" class="form-label">{{ __('Notes') }}</label>
                                <textarea class="form-control" id="item_notes" name="notes" rows="2"></textarea>
                                <div class="invalid-feedback" id="item_notes-error"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <span class="text-sm text-muted">{{ __('Subtotal') }}:</span>
                                        <span class="font-weight-bold" id="item-subtotal">0.00 {{ __('DH') }}</span>
                                    </span>
                                    <input type="hidden" id="item_index" value="-1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-selection {
            height: auto !important;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d2d6da !important;
            border-radius: 0.5rem !important;
            font-size: 0.875rem;
        }

        .select2-selection__arrow {
            height: 100% !important;
        }

        .table th {
            font-size: 0.65rem;
        }

        .item-row:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .item-row .delete-item {
            visibility: hidden;
        }

        .item-row:hover .delete-item {
            visibility: visible;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize Select2 for dropdowns
            $('#supplier_id, #warehouse_id, #product_id').select2({
                placeholder: "{{ __('Select...') }}",
                width: '100%'
            });

            // Variables to store order items
            let orderItems = [];
            let itemCount = 0;

            // Show Add Item modal
            $('#add-item').click(function () {
                resetItemForm();
                $('#item_index').val(-1);
                $('#addItemModal').modal('show');
            });

            // Update product unit when product is selected
            $('#product_id').change(function () {
                let selectedOption = $(this).find('option:selected');
                let unit = selectedOption.data('unit') || '-';
                $('#product-unit').text(unit);
            });

            // Calculate item subtotal
            function calculateItemSubtotal() {
                let quantity = parseFloat($('#quantity').val()) || 0;
                let unitPrice = parseFloat($('#unit_price').val()) || 0;
                let taxRate = parseFloat($('#tax_rate').val()) || 0;
                let discountRate = parseFloat($('#discount_rate').val()) || 0;

                let grossAmount = quantity * unitPrice;
                let discountAmount = grossAmount * (discountRate / 100);
                let afterDiscount = grossAmount - discountAmount;
                let taxAmount = afterDiscount * (taxRate / 100);
                let subtotal = afterDiscount + taxAmount;

                $('#item-subtotal').text(subtotal.toFixed(2) + ' {{ __('DH') }}');

                return {
                    grossAmount,
                    discountAmount,
                    taxAmount,
                    subtotal
                };
            }

            // Update item subtotal when inputs change
            $('#quantity, #unit_price, #tax_rate, #discount_rate').on('input', calculateItemSubtotal);

            // Add item to order
            $('#item-form').submit(function (e) {
                e.preventDefault();

                // Get form data
                let productId = $('#product_id').val();
                let productName = $('#product_id option:selected').text();
                let productUnit = $('#product-unit').text();
                let quantity = parseFloat($('#quantity').val());
                let unitPrice = parseFloat($('#unit_price').val());
                let taxRate = parseFloat($('#tax_rate').val()) || 0;
                let discountRate = parseFloat($('#discount_rate').val()) || 0;
                let notes = $('#item_notes').val();
                let itemIndex = parseInt($('#item_index').val());

                // Validate required fields
                if (!productId || !quantity || !unitPrice) {
                    if (!productId) $('#product_id').addClass('is-invalid');
                    if (!quantity) $('#quantity').addClass('is-invalid');
                    if (!unitPrice) $('#unit_price').addClass('is-invalid');
                    return;
                }

                // Calculate amounts
                let calculation = calculateItemSubtotal();

                // Create item object
                let item = {
                    id: itemIndex >= 0 ? orderItems[itemIndex].id : 'new_' + itemCount++,
                    product_id: productId,
                    product_name: productName,
                    product_unit: productUnit,
                    quantity: quantity,
                    unit_price: unitPrice,
                    tax_rate: taxRate,
                    tax_amount: calculation.taxAmount,
                    discount_rate: discountRate,
                    discount_amount: calculation.discountAmount,
                    subtotal: calculation.subtotal,
                    notes: notes
                };

                // Add or update item
                if (itemIndex >= 0) {
                    orderItems[itemIndex] = item;
                } else {
                    orderItems.push(item);
                }

                // Update table
                renderItems();
                calculateOrderTotals();

                // Close modal
                $('#addItemModal').modal('hide');
            });

            // Edit item
            $(document).on('click', '.edit-item', function () {
                let index = $(this).data('index');
                let item = orderItems[index];

                $('#item_index').val(index);
                $('#product_id').val(item.product_id).trigger('change');
                $('#quantity').val(item.quantity);
                $('#unit_price').val(item.unit_price);
                $('#tax_rate').val(item.tax_rate);
                $('#discount_rate').val(item.discount_rate);
                $('#item_notes').val(item.notes);

                calculateItemSubtotal();

                $('#addItemModalLabel').text("{{ __('Edit Product') }}");
                $('#item-form button[type="submit"]').html('<i class="fas fa-check"></i> {{ __('Update Item') }}');

                $('#addItemModal').modal('show');
            });

            // Delete item
            $(document).on('click', '.delete-item', function () {
                let index = $(this).data('index');
                orderItems.splice(index, 1);
                renderItems();
                calculateOrderTotals();
            });

            // Render items in table
            function renderItems() {
                let tbody = $('#items-table tbody');
                tbody.empty();

                if (orderItems.length === 0) {
                    tbody.html(`
                        <tr id="no-items-row">
                            <td colspan="7" class="text-center py-4">
                                <p class="text-sm mb-0">{{ __('No items added yet. Click "Add Item" to add products to this order.') }}</p>
                            </td>
                        </tr>
                    `);
                    return;
                }

                orderItems.forEach((item, index) => {
                    tbody.append(`
                        <tr class="item-row" data-id="${item.id}">
                            <td>
                                <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm">${item.product_name}</h6>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                                <p class="text-sm font-weight-bold mb-0">${item.quantity.toFixed(3)}</p>
                                <p class="text-xs text-secondary mb-0">${item.product_unit}</p>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][unit_price]" value="${item.unit_price}">
                                <p class="text-sm font-weight-bold mb-0">${item.unit_price.toFixed(2)}</p>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][tax_rate]" value="${item.tax_rate}">
                                <input type="hidden" name="items[${index}][tax_amount]" value="${item.tax_amount}">
                                <p class="text-sm font-weight-bold mb-0">${item.tax_rate.toFixed(2)}%</p>
                                <p class="text-xs text-secondary mb-0">${item.tax_amount.toFixed(2)}</p>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][discount_rate]" value="${item.discount_rate}">
                                <input type="hidden" name="items[${index}][discount_amount]" value="${item.discount_amount}">
                                <p class="text-sm font-weight-bold mb-0">${item.discount_rate.toFixed(2)}%</p>
                                <p class="text-xs text-secondary mb-0">${item.discount_amount.toFixed(2)}</p>
                            </td>
                            <td>
                                <input type="hidden" name="items[${index}][subtotal]" value="${item.subtotal}">
                                <input type="hidden" name="items[${index}][notes]" value="${item.notes || ''}">
                                <p class="text-sm font-weight-bold mb-0">${item.subtotal.toFixed(2)}</p>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <button type="button" class="btn btn-link text-primary edit-item" data-index="${index}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-link text-danger delete-item" data-index="${index}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            }

            // Calculate order totals
            function calculateOrderTotals() {
                let subtotal = 0;
                let taxAmount = 0;
                let discountAmount = 0;

                orderItems.forEach(item => {
                    subtotal += item.subtotal;
                    taxAmount += item.tax_amount;
                    discountAmount += item.discount_amount;
                });

                let totalAmount = subtotal;

                $('#subtotal-display').text(subtotal.toFixed(2));
                $('#subtotal').val(subtotal.toFixed(2));

                $('#tax-amount-display').text(taxAmount.toFixed(2));
                $('#tax_amount').val(taxAmount.toFixed(2));

                $('#discount-amount-display').text(discountAmount.toFixed(2));
                $('#discount_amount').val(discountAmount.toFixed(2));

                $('#total-amount-display').text(totalAmount.toFixed(2));
                $('#total_amount').val(totalAmount.toFixed(2));
            }

            // Reset item form
            function resetItemForm() {
                $('#item-form')[0].reset();
                $('#product_id').val('').trigger('change');
                $('#product-unit').text('-');
                $('#item-subtotal').text('0.00 {{ __('DH') }}');
                $('#addItemModalLabel').text("{{ __('Add Product') }}");
                $('#item-form button[type="submit"]').html('<i class="fas fa-plus"></i> {{ __('Add Item') }}');
                $('.is-invalid').removeClass('is-invalid');
            }

            // Validate expected delivery date
            $('#expected_delivery_date').change(function () {
                let orderDate = $('#order_date').val();
                let deliveryDate = $(this).val();

                if (deliveryDate && orderDate && deliveryDate < orderDate) {
                    $(this).addClass('is-invalid');
                    $('#expected_delivery_date-error').text("{{ __('Expected delivery date cannot be earlier than order date') }}");
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Submit purchase order form
            $('#purchase-order-form').submit(function (e) {
                e.preventDefault();

                if (orderItems.length === 0) {
                    showToast('error', "{{ __('Please add at least one product to the order') }}");
                    return;
                }

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('admin.purchase-orders.store') }}",
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            showToast('success', response.message);

                            // Redirect to show page after a delay
                            setTimeout(function () {
                                window.location.href = response.redirect_url;
                            }, 1500);
                        } else {
                            showToast('error', response.error);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            showValidationErrors(xhr.responseJSON.errors);
                        } else {
                            showToast('error', "{{ __('An error occurred while saving the order') }}");
                        }
                    }
                });
            });

            // Show validation errors
            function showValidationErrors(errors) {
                // Clear previous errors
                $('.is-invalid').removeClass('is-invalid');

                $.each(errors, function (field, messages) {
                    // Handle nested fields (items[0][product_id])
                    if (field.includes('.') || field.includes('[')) {
                        // This is a complex field name, we'll handle it separately
                        if (field.startsWith('items.')) {
                            // Show a general error for items
                            showToast('error', messages[0]);
                        }
                    } else {
                        // Simple field
                        $('#' + field).addClass('is-invalid');
                        $('#' + field + '-error').text(messages[0]);
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
        });
    </script>
@endpush
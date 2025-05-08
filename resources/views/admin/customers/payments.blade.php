@extends('admin.layouts.master')

@section('title', 'Customer Payments - ' . $client->name)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Paid</p>
                                    <h5 class="font-weight-bolder">
                                        {{ number_format($stats['total_paid'], 2) }} MAD
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            {{ number_format(($stats['total_paid'] / max($stats['total_contracts_value'], 1)) * 100, 1) }}%
                                        </span>
                                        of total value
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="fas fa-check text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Outstanding Balance</p>
                                    <h5 class="font-weight-bolder">
                                        {{ number_format($stats['total_outstanding'], 2) }} MAD
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-danger text-sm font-weight-bolder">
                                            {{ number_format(($stats['total_outstanding'] / max($stats['total_contracts_value'], 1)) * 100, 1) }}%
                                        </span>
                                        remaining
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-exclamation-triangle text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Contracts Value</p>
                                    <h5 class="font-weight-bolder">
                                        {{ number_format($stats['total_contracts_value'], 2) }} MAD
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-info text-sm font-weight-bolder">
                                            {{ $client->contracts->count() }}
                                        </span>
                                        total contracts
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="fas fa-file-contract text-lg opacity-10" aria-hidden="true"></i>
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
                                <h6 class="mb-0">Payment History - {{ $client->name }}</h6>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn bg-gradient-primary" onclick="showAddPaymentModal()">
                                    <i class="fas fa-plus"></i> Add Payment
                                </button>
                                <a href="{{ route('admin.clients.show', $client->id) }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Customer
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="contractFilter">
                                    <option value="">All Contracts</option>
                                    @foreach($client->contracts as $contract)
                                        <option value="{{ $contract->id }}">
                                            CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }} - 
                                            {{ $contract->car->brand_name }} {{ $contract->car->model }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="methodFilter">
                                    <option value="">All Payment Methods</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="dateRangeFilter">
                                    <option value="">All Time</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="last_3_months">Last 3 Months</option>
                                    <option value="this_year">This Year</option>
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
                            <table class="table align-items-center mb-0" id="payments-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contract</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reference</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Processed By</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Notes</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($client->contracts as $contract)
                                        @foreach($contract->payments as $payment)
                                            <tr class="payment-row" data-contract="{{ $contract->id }}" data-method="{{ $payment->payment_method }}">
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $payment->payment_date->format('M d, Y') }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $payment->payment_date->format('h:i A') }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
                                                    </p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        {{ $contract->car->brand_name }} {{ $contract->car->model }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ number_format($payment->amount, 2) }} MAD</p>
                                                </td>
                                                <td class="align-middle text-sm">
                                                    <span class="badge badge-sm bg-gradient-{{ 
                                                        $payment->payment_method === 'cash' ? 'success' : 
                                                        ($payment->payment_method === 'card' ? 'info' : 
                                                        ($payment->payment_method === 'transfer' ? 'primary' : 'warning')) 
                                                    }}">
                                                        {{ ucfirst($payment->payment_method) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $payment->reference ?? 'N/A' }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $payment->processedBy->name ?? 'System' }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $payment->notes ?? '-' }}</p>
                                                </td>
                                                <td class="align-middle">
                                                    <button class="btn btn-link text-secondary mb-0" 
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v text-xs"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="showEditPaymentModal({{ $payment->id }})">
                                                                <i class="fas fa-edit text-primary"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" onclick="printReceipt({{ $payment->id }})">
                                                                <i class="fas fa-print text-info"></i> Print Receipt
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" onclick="deletePayment({{ $payment->id }})">
                                                                <i class="fas fa-trash text-danger"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contracts with Payment Status -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Contracts Payment Status</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            @foreach($client->contracts()->with(['car'])->orderBy('created_at', 'desc')->get() as $contract)
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header p-3 pb-0">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="mb-0">
                                                        Contract #CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}
                                                        @if($contract->status === 'active')
                                                            <span class="badge bg-success ms-2">Active</span>
                                                        @elseif($contract->status === 'completed')
                                                            <span class="badge bg-info ms-2">Completed</span>
                                                        @else
                                                            <span class="badge bg-danger ms-2">{{ ucfirst($contract->status) }}</span>
                                                        @endif
                                                    </h6>
                                                    <p class="text-sm mb-0">
                                                        {{ $contract->car->brand_name }} {{ $contract->car->model }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-end">{{ number_format($contract->total_amount, 2) }} MAD</h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body p-3 pt-1">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <p class="text-sm mb-1">Duration: {{ $contract->start_date->format('M d, Y') }} - {{ $contract->end_date->format('M d, Y') }}</p>
                                                </div>
                                                <div class="text-end">
                                                    <p class="text-sm mb-1">
                                                        Paid: {{ number_format($contract->total_paid, 2) }} MAD
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="progress-wrapper">
                                                <div class="progress-info">
                                                    <div class="progress-percentage">
                                                        <span class="text-sm font-weight-bold">{{ number_format(($contract->total_paid / $contract->total_amount) * 100, 1) }}%</span>
                                                    </div>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-gradient-info" role="progressbar" 
                                                         aria-valuenow="{{ ($contract->total_paid / $contract->total_amount) * 100 }}" 
                                                         aria-valuemin="0" aria-valuemax="100" 
                                                         style="width: {{ ($contract->total_paid / $contract->total_amount) * 100 }}%;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between mt-2">
                                                <p class="text-sm mb-0">
                                                    <span class="font-weight-bold">Balance:</span> {{ number_format($contract->outstanding_balance, 2) }} MAD
                                                </p>
                                                @if($contract->outstanding_balance > 0)
                                                    <button type="button" class="btn btn-sm bg-gradient-primary"
                                                        onclick="showAddPaymentModal({{ $contract->id }}, {{ $contract->outstanding_balance }})">
                                                        Add Payment
                                                    </button>
                                                @else
                                                    <span class="badge bg-gradient-success">Fully Paid</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" id="client_id" name="client_id" value="{{ $client->id }}">

                        <div class="mb-3">
                            <label for="contract_id" class="form-control-label">Contract</label>
                            <select class="form-control" id="contract_id" name="contract_id" required>
                                <option value="">Select Contract</option>
                                @foreach($client->contracts()->where('payment_status', '!=', 'paid')->get() as $contract)
                                    <option value="{{ $contract->id }}" data-balance="{{ $contract->outstanding_balance }}">
                                        CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }} -
                                        {{ $contract->car->brand_name }} {{ $contract->car->model }}
                                        (Balance: {{ number_format($contract->outstanding_balance, 2) }} MAD)
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback" id="contract_id-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-control-label">Amount (MAD)</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                            <small class="text-muted">Outstanding balance: <span id="outstanding_balance">0.00</span>
                                MAD</small>
                            <div class="invalid-feedback" id="amount-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_method" class="form-control-label">Payment Method</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                            </select>
                            <div class="invalid-feedback" id="payment_method-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="payment_date" class="form-control-label">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            <div class="invalid-feedback" id="payment_date-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="reference" class="form-control-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference" name="reference">
                            <div class="invalid-feedback" id="reference-error"></div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-control-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            <div class="invalid-feedback" id="notes-error"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1" aria-labelledby="deletePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePaymentModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="py-3 text-center">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p>Are you sure you want to delete this payment? This action cannot be undone.</p>
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
            storeUrl: "/admin/clients/{{ $client->id }}/payments",
            updateUrl: "/admin/clients/{{ $client->id }}/payments/:id",
            deleteUrl: "/admin/clients/{{ $client->id }}/payments/:id",
            printReceiptUrl: "/admin/payments/:id/receipt",
        };

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize DataTable
            const table = new DataTable('#payments-table', {
                order: [[0, 'desc']], // Sort by date descending
                pageLength: 10,
                language: {
                    paginate: {
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>'
                    }
                }
            });

            // Cache DOM elements
            const addPaymentForm = document.getElementById('addPaymentForm');
            const contractSelect = document.getElementById('contract_id');
            const outstandingBalanceEl = document.getElementById('outstanding_balance');
            const amountInput = document.getElementById('amount');
            const paymentDateInput = document.getElementById('payment_date');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Set default payment date to today
            paymentDateInput.value = new Date().toISOString().split('T')[0];

            // Event listeners
            if (contractSelect) {
                contractSelect.addEventListener('change', handleContractChange);
            }

            if (addPaymentForm) {
                addPaymentForm.addEventListener('submit', handleFormSubmit);
            }

            // Filter change handlers
            $('#contractFilter, #methodFilter, #dateRangeFilter').on('change', function() {
                filterPayments();
            });

            $('#resetFilters').on('click', function() {
                $('#contractFilter, #methodFilter, #dateRangeFilter').val('');
                filterPayments();
            });

            // Confirm delete button
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    const paymentId = this.getAttribute('data-id');
                    if (paymentId) {
                        deletePaymentConfirmed(paymentId);
                    }
                });
            }

            /**
             * Handle contract selection change
             */
            function handleContractChange() {
                const selectedOption = this.options[this.selectedIndex];
                const balance = selectedOption.dataset.balance || '0.00';
                outstandingBalanceEl.textContent = parseFloat(balance).toFixed(2);
                amountInput.max = balance;
                
                // Pre-fill the amount with the balance
                amountInput.value = balance;
            }

            /**
             * Filter payments based on selected filters
             */
            function filterPayments() {
                const contractId = $('#contractFilter').val();
                const paymentMethod = $('#methodFilter').val();
                const dateRange = $('#dateRangeFilter').val();
                
                table.rows().every(function() {
                    const rowData = this.node();
                    const row = $(rowData);
                    
                    let show = true;
                    
                    // Filter by contract
                    if (contractId && row.attr('data-contract') != contractId) {
                        show = false;
                    }
                    
                    // Filter by payment method
                    if (paymentMethod && row.attr('data-method') != paymentMethod) {
                        show = false;
                    }
                    
                    // Filter by date range (implemented in server-side)
                    // This would require additional data attributes for dates
                    
                    // Show/hide rows
                    row.toggle(show);
                });
            }

            /**
             * Handle payment form submission
             */
            function handleFormSubmit(e) {
                e.preventDefault();

                // Reset validation UI
                clearValidationErrors();

                // Get form data
                const formData = new FormData(e.target);
                const submitButton = addPaymentForm.querySelector('button[type="submit"]');

                // Show loading state
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                submitButton.disabled = true;

                // Send request
                fetch(routes.storeUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw { status: response.status, data: data };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Hide modal
                        $('#addPaymentModal').modal('hide');

                    
                        // Show success message using Toast
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
                            icon: 'success',
                            title: data.message || 'Payment added successfully'
                        }).then(() => {
                            // Reload the page to show updated data
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'An error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Handle validation errors
                    if (error.status === 422 && error.data && error.data.errors) {
                        displayValidationErrors(error.data.errors);
                    } else {
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.data?.message || error.message || 'An error occurred',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000
                        });
                    }
                })
                .finally(() => {
                    // Reset button state
                    submitButton.innerHTML = 'Save Payment';
                    submitButton.disabled = false;
                });
            }

            /**
             * Edit payment modal
             */
            function showEditPaymentModal(paymentId) {
                // Implement edit payment functionality here
                // This would require fetching the payment details and populating the form
                
                // Show notification that this feature is in development
                Swal.fire({
                    icon: 'info',
                    title: 'Coming Soon',
                    text: 'Edit payment functionality is currently in development.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }

            /**
             * Print payment receipt
             */
            function printReceipt(paymentId) {
                // Open receipt in new window
                window.open(routes.printReceiptUrl.replace(':id', paymentId), '_blank');
            }

            /**
             * Delete payment confirmation
             */
            function deletePayment(paymentId) {
                // Set the payment ID on the confirm button
                document.getElementById('confirmDeleteBtn').setAttribute('data-id', paymentId);
                document.getElementById('delete-warning').innerHTML = '';
                
                // Show the confirmation modal
                $('#deletePaymentModal').modal('show');
            }

            /**
             * Perform payment deletion after confirmation
             */
            function deletePaymentConfirmed(paymentId) {
                fetch(routes.deleteUrl.replace(':id', paymentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#deletePaymentModal').modal('hide');
                        
                        // Show success notification
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        Toast.fire({
                            icon: 'success',
                            title: data.message || 'Payment deleted successfully'
                        }).then(() => {
                            // Reload the page
                            location.reload();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to delete payment');
                    }
                })
                .catch(error => {
                    $('#deletePaymentModal').modal('hide');
                    
                    // Show error notification
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to delete payment',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000
                    });
                });
            }

            /**
             * Clear validation errors
             */
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }

            /**
             * Display validation errors
             */
            function displayValidationErrors(errors) {
                clearValidationErrors();

                Object.keys(errors).forEach(field => {
                    const input = document.getElementById(field);
                    const errorEl = document.getElementById(`${field}-error`);

                    if (input) input.classList.add('is-invalid');

                    if (errorEl && errors[field][0]) {
                        errorEl.textContent = errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }
        });

        /**
         * Reset payment form to its initial state
         */
        function resetPaymentForm() {
            const addPaymentForm = document.getElementById('addPaymentForm');
            if (addPaymentForm) {
                addPaymentForm.reset();
                document.getElementById('payment_date').value = new Date().toISOString().split('T')[0];
                document.getElementById('outstanding_balance').textContent = '0.00';

                // Clear validation errors
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }
        }

        /**
         * Show the add payment modal with optional pre-selected contract
         */
        function showAddPaymentModal(contractId = null, balance = null) {
            // Reset form first
            resetPaymentForm();

            if (contractId) {
                document.getElementById('contract_id').value = contractId;
                document.getElementById('outstanding_balance').textContent = parseFloat(balance).toFixed(2);
                document.getElementById('amount').max = balance;
                
                // Pre-fill amount with the outstanding balance
                document.getElementById('amount').value = balance;
            }

            $('#addPaymentModal').modal('show');
        }
    </script>
@endpush

@extends('admin.layouts.master')

@section('title', 'Customer Payments - ' . $client->name)

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
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

                        <!-- Statistics Cards -->
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-stats">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Paid</h5>
                                                    <span
                                                        class="h2 font-weight-bold mb-0">{{ number_format($stats['total_paid'], 2) }}
                                                        MAD</span>
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
                                <div class="col-md-4">
                                    <div class="card card-stats">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">Outstanding</h5>
                                                    <span
                                                        class="h2 font-weight-bold mb-0">{{ number_format($stats['total_outstanding'], 2) }}
                                                        MAD</span>
                                                </div>
                                                <div class="col-auto">
                                                    <div
                                                        class="icon icon-shape bg-gradient-warning text-white rounded-circle shadow">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-stats">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col">
                                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Contracts Value
                                                    </h5>
                                                    <span
                                                        class="h2 font-weight-bold mb-0">{{ number_format($stats['total_contracts_value'], 2) }}
                                                        MAD</span>
                                                </div>
                                                <div class="col-auto">
                                                    <div
                                                        class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                                        <i class="fas fa-file-contract"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contracts with Payments -->
                    @foreach($client->contracts()->with(['payments', 'car'])->orderBy('created_at', 'desc')->get() as $contract)
                        <div class="card mb-4">
                            <div class="card-header pb-0 p-3">
                                <div class="row">
                                    <div class="col-md-6">
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
                                            ({{ $contract->start_date->format('M d, Y') }} -
                                            {{ $contract->end_date->format('M d, Y') }})
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <h6 class="mb-0">Total: {{ number_format($contract->total_amount, 2) }} MAD</h6>
                                        <p class="text-sm mb-0">
                                            Paid: {{ number_format($contract->total_paid, 2) }} MAD |
                                            Balance: {{ number_format($contract->outstanding_balance, 2) }} MAD
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date
                                                </th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Amount</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Method</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Reference</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Processed By</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($contract->payments as $payment)
                                                <tr>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ $payment->payment_date->format('M d, Y') }}</p>
                                                        <p class="text-xs text-secondary mb-0">
                                                            {{ $payment->payment_date->format('h:i A') }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ number_format($payment->amount, 2) }} MAD</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ ucfirst($payment->payment_method) }}
                                                        </p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $payment->reference ?? 'N/A' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            {{ $payment->processedBy->name ?? 'System' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">{{ $payment->notes ?? '-' }}</p>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-3">
                                                        <p class="text-sm text-secondary mb-0">No payments for this contract</p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if($contract->outstanding_balance > 0)
                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-sm bg-gradient-primary"
                                            onclick="showAddPaymentModal({{ $contract->id }}, {{ $contract->outstanding_balance }})">
                                            Add Payment
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
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
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-control-label">Amount (MAD)</label>
                                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                                <small class="text-muted">Outstanding balance: <span id="outstanding_balance">0.00</span>
                                    MAD</small>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-control-label">Payment Method</label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="payment_date" class="form-control-label">Payment Date</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            </div>

                            <div class="mb-3">
                                <label for="reference" class="form-control-label">Reference Number</label>
                                <input type="text" class="form-control" id="reference" name="reference">
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-control-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Add Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            // Cache DOM elements
            const addPaymentForm = document.getElementById('addPaymentForm');
            const contractSelect = document.getElementById('contract_id');
            const outstandingBalanceEl = document.getElementById('outstanding_balance');
            const amountInput = document.getElementById('amount');
            const paymentDateInput = document.getElementById('payment_date');
            const submitButton = addPaymentForm.querySelector('button[type="submit"]');
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

            /**
             * Handle contract selection change
             */
            function handleContractChange() {
                const selectedOption = this.options[this.selectedIndex];
                const balance = selectedOption.dataset.balance || '0.00';
                outstandingBalanceEl.textContent = parseFloat(balance).toFixed(2);
                amountInput.max = balance;
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
                const clientId = document.getElementById('client_id').value;

                // Show loading state
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
                submitButton.disabled = true;

                // Send request
                fetch(`/admin/clients/${clientId}/payments`, {
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
                        submitButton.innerHTML = 'Add Payment';
                        submitButton.disabled = false;
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
            }

            $('#addPaymentModal').modal('show');
        }
    </script>
@endpush
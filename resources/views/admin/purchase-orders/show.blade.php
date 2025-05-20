) }}:</span>
                                            <span>{{ $purchaseOrder->supplier->name ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Contact Person') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->contact_person ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Email') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->email ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Phone') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->phone ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Address') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->address ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('City') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->city ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Tax ID') }}:</span>
                                            <span>{{ $purchaseOrder->supplier->tax_id ?? '—' }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warehouse Info -->
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header bg-light p-3">
                                    <h6 class="mb-0">{{ __('Warehouse & Notes') }}</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group mb-3">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Warehouse') }}:</span>
                                            <span>{{ $purchaseOrder->warehouse->name ?? '—' }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span class="text-sm text-muted">{{ __('Location') }}:</span>
                                            <span>{{ $purchaseOrder->warehouse->location ?? '—' }}</span>
                                        </li>
                                    </ul>
                                    
                                    <div class="card bg-light">
                                        <div class="card-header p-2">
                                            <h6 class="mb-0 text-sm">{{ __('Notes') }}</h6>
                                        </div>
                                        <div class="card-body p-2">
                                            <p class="mb-0 text-sm">{{ $purchaseOrder->notes ?? __('No notes added.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items Table -->
                    <div class="row">
                        <div class="col-12 mb-4">
                            <div class="card">
                                <div class="card-header bg-light p-3">
                                    <h6 class="mb-0">{{ __('Order Items') }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Product') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Quantity') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Unit Price') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Tax') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Discount') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Subtotal') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Received') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchaseOrder->items as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                @if($item->product && $item->product->image)
                                                                <img src="{{ asset('storage/' . $item->product->image) }}" class="avatar avatar-sm me-3" alt="{{ $item->product->name }}">
                                                                @else
                                                                <div class="avatar avatar-sm bg-gradient-secondary me-3">
                                                                    <i class="ni ni-box-2 text-white"></i>
                                                                </div>
                                                                @endif
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">{{ $item->product->name ?? 'N/A' }}</h6>
                                                                <p class="text-xs text-secondary mb-0">{{ $item->product->code ?? '—' }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->quantity, 3) }}</p>
                                                        <p class="text-xs text-secondary mb-0">{{ $item->product->unit ?? '—' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->unit_price, 2) }}</p>
                                                    </td>
                                                    <td>
                                                        @if($item->tax_rate > 0)
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->tax_amount, 2) }}</p>
                                                        <p class="text-xs text-secondary mb-0">{{ number_format($item->tax_rate, 2) }}%</p>
                                                        @else
                                                        <p class="text-sm mb-0">—</p>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($item->discount_rate > 0)
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->discount_amount, 2) }}</p>
                                                        <p class="text-xs text-secondary mb-0">{{ number_format($item->discount_rate, 2) }}%</p>
                                                        @else
                                                        <p class="text-sm mb-0">—</p>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($item->subtotal, 2) }}</p>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $receivedQuantity = $totalReceivedItems[$item->product_id] ?? 0;
                                                            $remainingQuantity = $item->quantity - $receivedQuantity;
                                                            $receivedPercent = $item->quantity > 0 ? ($receivedQuantity / $item->quantity) * 100 : 0;
                                                        @endphp
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2 text-xs">{{ number_format($receivedPercent, 0) }}%</span>
                                                            <div>
                                                                <div class="progress">
                                                                    <div class="progress-bar bg-gradient-info" role="progressbar" aria-valuenow="{{ $receivedPercent }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $receivedPercent }}%;"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="text-xs text-secondary mb-0">
                                                            {{ number_format($receivedQuantity, 3) }} / {{ number_format($item->quantity, 3) }}
                                                        </p>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end">
                                                        <p class="text-sm font-weight-bold mb-0">{{ __('Subtotal') }}:</p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($purchaseOrder->subtotal, 2) }}</p>
                                                    </td>
                                                </tr>
                                                @if($purchaseOrder->tax_amount > 0)
                                                <tr>
                                                    <td colspan="5" class="text-end">
                                                        <p class="text-sm font-weight-bold mb-0">{{ __('Tax Amount') }}:</p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($purchaseOrder->tax_amount, 2) }}</p>
                                                    </td>
                                                </tr>
                                                @endif
                                                @if($purchaseOrder->discount_amount > 0)
                                                <tr>
                                                    <td colspan="5" class="text-end">
                                                        <p class="text-sm font-weight-bold mb-0">{{ __('Discount Amount') }}:</p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($purchaseOrder->discount_amount, 2) }}</p>
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td colspan="5" class="text-end">
                                                        <p class="text-sm font-weight-bold mb-0">{{ __('Grand Total') }}:</p>
                                                    </td>
                                                    <td colspan="2">
                                                        <p class="text-sm font-weight-bold mb-0">{{ number_format($purchaseOrder->total_amount, 2) }}</p>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stock Receptions -->
                    @if($purchaseOrder->receptions && $purchaseOrder->receptions->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light p-3">
                                    <h6 class="mb-0">{{ __('Stock Receptions') }}</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Reference No') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Date') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Status') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Received By') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Items') }}</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchaseOrder->receptions as $reception)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">{{ $reception->reference_no }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ $reception->reception_date->format('d/m/Y') }}</p>
                                                    </td>
                                                    <td>
                                                        @if($reception->status == 'pending')
                                                            <span class="badge badge-sm bg-gradient-secondary">{{ __('Pending') }}</span>
                                                        @elseif($reception->status == 'completed')
                                                            <span class="badge badge-sm bg-gradient-success">{{ __('Completed') }}</span>
                                                        @elseif($reception->status == 'partial')
                                                            <span class="badge badge-sm bg-gradient-warning">{{ __('Partial') }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ $reception->receivedBy->name ?? '—' }}</p>
                                                    </td>
                                                    <td>
                                                        <p class="text-sm font-weight-bold mb-0">{{ $reception->items->count() }}</p>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.stock-receptions.show', $reception->id) }}" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">{{ __('Confirm Purchase Order') }}</h5>
                <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to confirm this purchase order? Once confirmed, it cannot be edited.') }}</p>
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
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
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
@endsection

@push('css')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        
        .card, .card * {
            visibility: visible;
        }
        
        .card {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        .no-print {
            display: none !important;
        }
    }
    
    .list-group-item {
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
</style>
@endpush

@push('js')
<script>
    $(document).ready(function () {
        // Confirm Order
        $('.confirm-order').click(function () {
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
                        showToast('success', response.message);
                        
                        // Reload page after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
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
        $('.cancel-order').click(function () {
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
                        showToast('success', response.message);
                        
                        // Reload page after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('error', response.error);
                    }
                },
                error: function (xhr) {
                    showErrorToast(xhr);
                }
            });
        });

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
    });
</script>
@endpush
@extends('admin.layouts.master')

@section('title', 'Customer Details')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Customer Details</h6>
                            </div>
                            <div class="col-6 text-end">
                                <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn bg-gradient-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('admin.clients.index') }}" class="btn bg-gradient-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card card-profile">
                                    <img src="{{ asset('admin/img/bg-profile.jpg') }}" alt="Image placeholder" class="card-img-top">
                                    <div class="row justify-content-center">
                                        <div class="col-4 col-lg-4 order-lg-2">
                                            <div class="mt-n4 mt-lg-n6 mb-4 mb-lg-0">
                                                <a href="javascript:;">
                                                    <img src="{{ $client->photo_url ?? asset('admin/img/default-avatar.png') }}" 
                                                         class="rounded-circle img-fluid border border-2 border-white">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="text-center mt-4">
                                            <h5>
                                                {{ $client->name }}
                                                @if($client->status === 'active')
                                                    <span class="badge bg-success ms-2">Active</span>
                                                @elseif($client->status === 'banned')
                                                    <span class="badge bg-danger ms-2">Banned</span>
                                                @else
                                                    <span class="badge bg-warning ms-2">Inactive</span>
                                                @endif
                                            </h5>
                                            <div class="h6 font-weight-300">
                                                <i class="ni location_pin mr-2"></i>{{ $client->email }}
                                            </div>
                                            <div class="h6 mt-4">
                                                <i class="ni business_briefcase-24 mr-2"></i>Customer ID: #{{ $client->id }}
                                            </div>
                                            <div>
                                                <i class="ni education_hat mr-2"></i>Joined: {{ $client->created_at->format('M d, Y') }}
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
                                                    <p class="form-control-static">{{ $client->name }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Email</label>
                                                    <p class="form-control-static">{{ $client->email }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">Phone</label>
                                                    <p class="form-control-static">{{ $client->phone }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">ID Number</label>
                                                    <p class="form-control-static">{{ $client->id_number }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">License Number</label>
                                                    <p class="form-control-static">{{ $client->license_number }}</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-control-label">License Expiry</label>
                                                    <p class="form-control-static">
                                                        {{ $client->license_expiry_date ? $client->license_expiry_date->format('M d, Y') : 'N/A' }}
                                                        @if($client->license_expiry_date && $client->license_expiry_date < now())
                                                            <span class="badge bg-danger ms-2">Expired</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label class="form-control-label">Address</label>
                                                    <p class="form-control-static">{{ $client->address ?? 'N/A' }}</p>
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
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Total Contracts</h5>
                                                                <span class="h2 font-weight-bold mb-0">{{ $stats['total_contracts'] }}</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="icon icon-shape bg-gradient-primary text-white rounded-circle shadow">
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
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Active Contracts</h5>
                                                                <span class="h2 font-weight-bold mb-0">{{ $stats['active_contracts'] }}</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="icon icon-shape bg-gradient-success text-white rounded-circle shadow">
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
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Total Spent</h5>
                                                                <span class="h2 font-weight-bold mb-0">{{ number_format($stats['total_spent'], 2) }}</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
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
                                                                <h5 class="card-title text-uppercase text-muted mb-0">Outstanding</h5>
                                                                <span class="h2 font-weight-bold mb-0">{{ number_format($stats['outstanding_balance'], 2) }}</span>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="icon icon-shape bg-gradient-danger text-white rounded-circle shadow">
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
                                                <a href="{{ route('admin.clients.contracts', $client->id) }}" class="btn btn-sm bg-gradient-primary">
                                                    View All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contract #</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Car</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Duration</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($client->contracts()->latest()->take(5)->get() as $contract)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">CT-{{ str_pad($contract->id, 5, '0', STR_PAD_LEFT) }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $contract->car->brand_name }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $contract->car->model }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $contract->start_date->format('M d, Y') }}</p>
                                                                <p class="text-xs text-secondary mb-0">to {{ $contract->end_date->format('M d, Y') }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($contract->total_amount, 2) }} MAD</p>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    Paid: {{ number_format($contract->total_paid, 2) }} MAD
                                                                </p>
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                @if($contract->status === 'active')
                                                                    <span class="badge badge-sm bg-gradient-success">Active</span>
                                                                @elseif($contract->status === 'completed')
                                                                    <span class="badge badge-sm bg-gradient-info">Completed</span>
                                                                @else
                                                                    <span class="badge badge-sm bg-gradient-danger">{{ ucfirst($contract->status) }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle">
                                                                <a href="{{ route('admin.contracts.show', $contract->id) }}" 
                                                                   class="text-secondary font-weight-bold text-xs" 
                                                                   data-toggle="tooltip" 
                                                                   data-original-title="View contract">
                                                                    View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <p class="text-sm text-secondary mb-0">No contracts found</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
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
                                                <a href="{{ route('admin.clients.payments', $client->id) }}" class="btn btn-sm bg-gradient-primary">
                                                    View All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contract</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reference</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($payments as $payment)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $payment->payment_date->format('M d, Y') }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ $payment->payment_date->format('h:i A') }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">CT-{{ str_pad($payment->contract->id, 5, '0', STR_PAD_LEFT) }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $payment->contract->car->brand_name }} {{ $payment->contract->car->model }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($payment->amount, 2) }} MAD</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ ucfirst($payment->payment_method) }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $payment->reference ?? 'N/A' }}</p>
                                                            </td>
                                                            <td class="align-middle text-center text-sm">
                                                                <span class="badge badge-sm bg-gradient-success">Paid</span>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
                                                                <p class="text-sm text-secondary mb-0">No payments found</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
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
 @endsection
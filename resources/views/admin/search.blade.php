@extends('admin.layouts.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>{{ __('Search Results for') }}: "{{ $query }}"</h6>
                    <form action="{{ route('admin.search') }}" method="GET" class="w-50">
                        <div class="input-group">
                            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                            <input type="text" name="query" class="form-control" placeholder="{{ __('Search again...') }}" value="{{ $query }}">
                            <button class="btn btn-primary mb-0" type="submit">{{ __('Search') }}</button>
                        </div>
                    </form>
                </div>
                <div class="card-body p-3">
                    <!-- Stats summary -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ __('Cars') }}</p>
                                                <h5 class="font-weight-bolder">{{ $cars->count() }}</h5>
                                                <p class="mb-0 text-sm">{{ __('results found') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                                <i class="ni ni-bus-front-12 text-lg opacity-10" aria-hidden="true"></i>
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
                                                <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ __('Clients') }}</p>
                                                <h5 class="font-weight-bolder">{{ $clients->count() }}</h5>
                                                <p class="mb-0 text-sm">{{ __('results found') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                                <i class="ni ni-single-02 text-lg opacity-10" aria-hidden="true"></i>
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
                                                <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ __('Bookings') }}</p>
                                                <h5 class="font-weight-bolder">{{ $bookings->count() }}</h5>
                                                <p class="mb-0 text-sm">{{ __('results found') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                                <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ __('Contracts') }}</p>
                                                <h5 class="font-weight-bolder">{{ $contracts->count() }}</h5>
                                                <p class="mb-0 text-sm">{{ __('results found') }}</p>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="nav-wrapper position-relative end-0">
                                <ul class="nav nav-pills nav-fill p-1" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#cars-tab" role="tab" aria-controls="cars-tab" aria-selected="true">
                                            <i class="ni ni-bus-front-12 text-sm me-2"></i> {{ __('Cars') }} ({{ $cars->count() }})
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#clients-tab" role="tab" aria-controls="clients-tab" aria-selected="false">
                                            <i class="ni ni-single-02 text-sm me-2"></i> {{ __('Clients') }} ({{ $clients->count() }})
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#bookings-tab" role="tab" aria-controls="bookings-tab" aria-selected="false">
                                            <i class="ni ni-calendar-grid-58 text-sm me-2"></i> {{ __('Bookings') }} ({{ $bookings->count() }})
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#contracts-tab" role="tab" aria-controls="contracts-tab" aria-selected="false">
                                            <i class="ni ni-paper-diploma text-sm me-2"></i> {{ __('Contracts') }} ({{ $contracts->count() }})
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                <!-- Cars Tab -->
                                <div class="tab-pane fade show active" id="cars-tab" role="tabpanel" aria-labelledby="cars-tab">
                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Vehicle') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Reg. Number') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Status') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Category') }}</th>
                                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($cars as $car)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div>
                                                                        @if($car->featured_image)
                                                                            <img src="{{ asset('storage/' . $car->featured_image) }}" class="avatar avatar-sm me-3" alt="{{ $car->model }}">
                                                                        @else
                                                                            <div class="avatar avatar-sm bg-gradient-primary me-3 d-flex justify-content-center align-items-center">
                                                                                <i class="ni ni-bus-front-12 text-white"></i>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $car->brand->name ?? '' }} {{ $car->model }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ $car->year }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $car->registration_number }}</p>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-sm bg-gradient-{{ $car->status == 'available' ? 'success' : ($car->status == 'maintenance' ? 'warning' : 'secondary') }}">
                                                                    {{ ucfirst($car->status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $car->category->name ?? 'N/A' }}</p>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <a href="{{ route('admin.cars.show', $car->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> {{ __('View') }}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">
                                                                <p class="text-sm text-secondary mb-0">{{ __('No cars found matching your search criteria.') }}</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Clients Tab -->
                                <div class="tab-pane fade" id="clients-tab" role="tabpanel" aria-labelledby="clients-tab">
                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Client') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Contact Info') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('ID Number') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Status') }}</th>
                                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($clients as $client)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div>
                                                                        <div class="avatar avatar-sm bg-gradient-success me-3 d-flex justify-content-center align-items-center">
                                                                            {{ substr($client->name, 0, 1) }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $client->name }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ __('Since') }}: {{ \Carbon\Carbon::parse($client->created_at)->format('M Y') }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $client->email }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $client->phone }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $client->id_number }}</p>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-sm bg-gradient-{{ $client->status == 'active' ? 'success' : 'secondary' }}">
                                                                    {{ ucfirst($client->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> {{ __('View') }}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">
                                                                <p class="text-sm text-secondary mb-0">{{ __('No clients found matching your search criteria.') }}</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bookings Tab -->
                                <div class="tab-pane fade" id="bookings-tab" role="tabpanel" aria-labelledby="bookings-tab">
                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Booking #') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Client') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Vehicle') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Dates') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Status') }}</th>
                                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($bookings as $booking)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $booking->booking_number }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ \Carbon\Carbon::parse($booking->created_at)->format('d M Y') }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $booking->customer->name ?? 'N/A' }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $booking->customer->phone ?? 'N/A' }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $booking->car->brand->name ?? '' }} {{ $booking->car->model ?? 'N/A' }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $booking->car->registration_number ?? 'N/A' }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($booking->pickup_date)->format('d M Y') }}</p>
                                                                <p class="text-xs text-secondary mb-0">to {{ \Carbon\Carbon::parse($booking->return_date)->format('d M Y') }}</p>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-sm bg-gradient-{{ 
                                                                    $booking->status == 'active' ? 'success' : 
                                                                    ($booking->status == 'pending' ? 'warning' : 
                                                                    ($booking->status == 'completed' ? 'info' : 
                                                                    ($booking->status == 'cancelled' ? 'danger' : 'secondary'))) 
                                                                }}">
                                                                    {{ ucfirst($booking->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> {{ __('View') }}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                <p class="text-sm text-secondary mb-0">{{ __('No bookings found matching your search criteria.') }}</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Contracts Tab -->
                                <div class="tab-pane fade" id="contracts-tab" role="tabpanel" aria-labelledby="contracts-tab">
                                    <div class="card">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Contract #') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Client') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Duration') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Amount') }}</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">{{ __('Status') }}</th>
                                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">{{ __('Actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($contracts as $contract)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $contract->contract_number }}</h6>
                                                                        <p class="text-xs text-secondary mb-0">{{ \Carbon\Carbon::parse($contract->created_at)->format('d M Y') }}</p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ $contract->customer->name ?? 'N/A' }}</p>
                                                                <p class="text-xs text-secondary mb-0">{{ $contract->customer->phone ?? 'N/A' }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($contract->start_date)->format('d M Y') }}</p>
                                                                <p class="text-xs text-secondary mb-0">to {{ \Carbon\Carbon::parse($contract->end_date)->format('d M Y') }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($contract->total_amount, 2) }} MAD</p>
                                                            </td>
                                                            <td>
                                                                <span class="badge badge-sm bg-gradient-{{ 
                                                                    $contract->status == 'active' ? 'success' : 
                                                                    ($contract->status == 'pending' ? 'warning' : 
                                                                    ($contract->status == 'completed' ? 'info' : 
                                                                    ($contract->status == 'cancelled' ? 'danger' : 'secondary'))) 
                                                                }}">
                                                                    {{ ucfirst($contract->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="align-middle text-center">
                                                                <a href="{{ route('admin.contracts.show', $contract->id) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> {{ __('View') }}
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                <p class="text-sm text-secondary mb-0">{{ __('No contracts found matching your search criteria.') }}</p>
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
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Activate the correct tab based on counts
        if ({{ $cars->count() }} == 0 && {{ $clients->count() }} > 0) {
            $('a[href="#clients-tab"]').tab('show');
        } else if ({{ $cars->count() }} == 0 && {{ $clients->count() }} == 0 && {{ $bookings->count() }} > 0) {
            $('a[href="#bookings-tab"]').tab('show');
        } else if ({{ $cars->count() }} == 0 && {{ $clients->count() }} == 0 && {{ $bookings->count() }} == 0 && {{ $contracts->count() }} > 0) {
            $('a[href="#contracts-tab"]').tab('show');
        }
    });
</script>
@endpush
@extends('admin.layouts.master')

@section('title', 'View Car Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 p-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Car Details</h6>
                    <div>
                        <a href="{{ route('admin.cars.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                        @can('edit cars')
                            <button type="button" class="btn bg-gradient-primary btn-sm edit-btn" 
                                data-id="{{ $car->id }}">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                        @endcan
                        @can('create cars')
                            <a href="{{ route('admin.cars.maintenance.index', $car->id) }}" 
                                class="btn bg-gradient-info btn-sm">
                                <i class="fas fa-tools me-1"></i> Maintenance
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <!-- Main Car Image and Basic Info -->
                        <div class="col-md-4 mb-4">
                            <div class="card border shadow-none mb-3">
                                <div class="position-relative">
                                    @if($car->main_image)
                                        <img src="{{ Storage::url($car->main_image) }}" alt="{{ $car->name }}" 
                                            class="img-fluid rounded-top" style="width: 100%; height: 250px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-top d-flex justify-content-center align-items-center" 
                                            style="height: 250px;">
                                            <i class="fas fa-car fa-4x text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-{{ $car->status == 'available' ? 'success' : 
                                            ($car->status == 'rented' ? 'primary' : 
                                            ($car->status == 'maintenance' ? 'warning' : 'danger')) }}">
                                            {{ ucfirst($car->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{ $car->brand_name }} {{ $car->name }}</h5>
                                    <p class="card-text text-sm">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i> {{ $car->year }}
                                        <br>
                                        <i class="fas fa-id-card text-primary me-1"></i> {{ $car->matricule }}
                                        <br>
                                        <i class="fas fa-gas-pump text-primary me-1"></i> {{ ucfirst($car->fuel_type) }}
                                        <br>
                                        <i class="fas fa-tachometer-alt text-primary me-1"></i> {{ number_format($car->mileage) }} km
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h4 class="text-primary mb-0">{{ number_format($car->price_per_day, 2) }} MAD</h4>
                                        <span class="text-muted text-sm">per day</span>
                                    </div>
                                    @if($car->discount_percentage > 0)
                                        <div class="bg-light p-2 mt-2 rounded">
                                            <span class="text-success">
                                                <i class="fas fa-tag me-1"></i> {{ $car->discount_percentage }}% discount applied
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Car Details -->
                        <div class="col-md-8 mb-4">
                            <div class="card border shadow-none h-100">
                                <div class="card-header pb-0 p-3">
                                    <h6 class="mb-0">Details</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-sm text-muted">Basic Information</h6>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Category</span>
                                                        <span class="text-sm text-dark font-weight-bold">
                                                            {{ $car->category ? $car->category->name : 'N/A' }}
                                                        </span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Model</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ $car->model }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Color</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ $car->color }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Chassis Number</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ $car->chassis_number }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Service Date</span>
                                                        <span class="text-sm text-dark font-weight-bold">
                                                            {{ $car->mise_en_service_date ? date('d/m/Y', strtotime($car->mise_en_service_date)) : 'N/A' }}
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <h6 class="text-uppercase text-sm text-muted">Technical Specifications</h6>
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Transmission</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ ucfirst($car->transmission) }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Engine Capacity</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ $car->engine_capacity ?? 'N/A' }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Seats</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ $car->seats }}</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Weekly Price</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ number_format($car->weekly_price, 2) }} MAD</span>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                        <span class="text-sm">Monthly Price</span>
                                                        <span class="text-sm text-dark font-weight-bold">{{ number_format($car->monthly_price, 2) }} MAD</span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($car->description)
                                    <div class="mt-3">
                                        <h6 class="text-uppercase text-sm text-muted">Description</h6>
                                        <div class="p-3 bg-light rounded">
                                            {!! $car->description !!}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header pb-0 p-3">
                                    <h6 class="mb-0">Features</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        @if(count($car->features) > 0)
                                            @foreach($car->features as $feature)
                                                <div class="col-md-3 mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon-box bg-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                            <i class="fas fa-check text-white text-sm"></i>
                                                        </div>
                                                        <span class="text-sm">{{ ucfirst($feature) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="col-12">
                                                <p class="text-muted text-sm">No features specified for this car.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header pb-0 p-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Documents</h6>
                                    @can('edit cars')
                                        <a href="{{ route('admin.cars.documents.show', $car->id) }}" class="btn btn-sm btn-outline-primary">
                                            Manage Documents
                                        </a>
                                    @endcan
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        @if(isset($car->documents))
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-3">Insurance</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Number</span>
                                                                <span class="text-sm text-dark font-weight-bold">{{ $car->documents->assurance_number ?? 'N/A' }}</span>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Company</span>
                                                                <span class="text-sm text-dark font-weight-bold">{{ $car->documents->assurance_company ?? 'N/A' }}</span>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Expiry Date</span>
                                                                <span class="text-sm {{ isset($car->documents->assurance_expiry_date) && $car->documents->assurance_expiry_date < now() ? 'text-danger' : 'text-dark' }} font-weight-bold">
                                                                    {{ isset($car->documents->assurance_expiry_date) ? date('d/m/Y', strtotime($car->documents->assurance_expiry_date)) : 'N/A' }}
                                                                </span>
                                                            </li>
                                                        </ul>
                                                        @if(isset($car->documents->file_assurance))
                                                            <a href="{{ Storage::url($car->documents->file_assurance) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="fas fa-file-pdf"></i> View Document
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-3">Carte Grise</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Number</span>
                                                                <span class="text-sm text-dark font-weight-bold">{{ $car->documents->carte_grise_number ?? 'N/A' }}</span>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Expiry Date</span>
                                                                <span class="text-sm {{ isset($car->documents->carte_grise_expiry_date) && $car->documents->carte_grise_expiry_date < now() ? 'text-danger' : 'text-dark' }} font-weight-bold">
                                                                    {{ isset($car->documents->carte_grise_expiry_date) ? date('d/m/Y', strtotime($car->documents->carte_grise_expiry_date)) : 'N/A' }}
                                                                </span>
                                                            </li>
                                                        </ul>
                                                        @if(isset($car->documents->file_carte_grise))
                                                            <a href="{{ Storage::url($car->documents->file_carte_grise) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="fas fa-file-pdf"></i> View Document
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-3">Vignette</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Expiry Date</span>
                                                                <span class="text-sm {{ isset($car->documents->vignette_expiry_date) && $car->documents->vignette_expiry_date < now() ? 'text-danger' : 'text-dark' }} font-weight-bold">
                                                                    {{ isset($car->documents->vignette_expiry_date) ? date('d/m/Y', strtotime($car->documents->vignette_expiry_date)) : 'N/A' }}
                                                                </span>
                                                            </li>
                                                        </ul>
                                                        @if(isset($car->documents->file_vignette))
                                                            <a href="{{ Storage::url($car->documents->file_vignette) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="fas fa-file-pdf"></i> View Document
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6 mb-3">
                                                <div class="card border">
                                                    <div class="card-body p-3">
                                                        <h6 class="card-title mb-3">Technical Inspection</h6>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Last Inspection Date</span>
                                                                <span class="text-sm text-dark font-weight-bold">
                                                                    {{ isset($car->documents->visite_technique_date) ? date('d/m/Y', strtotime($car->documents->visite_technique_date)) : 'N/A' }}
                                                                </span>
                                                            </li>
                                                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                                                <span class="text-sm">Expiry Date</span>
                                                                <span class="text-sm {{ isset($car->documents->visite_technique_expiry_date) && $car->documents->visite_technique_expiry_date < now() ? 'text-danger' : 'text-dark' }} font-weight-bold">
                                                                    {{ isset($car->documents->visite_technique_expiry_date) ? date('d/m/Y', strtotime($car->documents->visite_technique_expiry_date)) : 'N/A' }}
                                                                </span>
                                                            </li>
                                                        </ul>
                                                        @if(isset($car->documents->file_visite_technique))
                                                            <a href="{{ Storage::url($car->documents->file_visite_technique) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                                <i class="fas fa-file-pdf"></i> View Document
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-12">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    No documents are associated with this car. Please add documents for complete record keeping.
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gallery Section -->
                    @if($car->images && count($car->images) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header pb-0 p-3">
                                    <h6 class="mb-0">Gallery</h6>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row">
                                        @foreach($car->images as $image)
                                            <div class="col-md-3 mb-3">
                                                <a href="{{ Storage::url($image->image_path) }}" target="_blank">
                                                    <img src="{{ Storage::url($image->image_path) }}" alt="Car Image" 
                                                        class="img-fluid rounded shadow" style="height: 150px; width: 100%; object-fit: cover;">
                                                </a>
                                                @if($image->is_featured)
                                                    <span class="badge bg-primary position-absolute" style="top: 10px; right: 25px;">
                                                        Featured
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Maintenance History Section -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border shadow-none">
                                <div class="card-header pb-0 p-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Maintenance History</h6>
                                    <a href="{{ route('admin.cars.maintenance.index', $car->id) }}" class="btn btn-sm btn-outline-primary">
                                        View Full History
                                    </a>
                                </div>
                                <div class="card-body p-3">
                                    @if(isset($maintenance) && count($maintenance) > 0)
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mileage</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Next Due</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cost</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($maintenance as $record)
                                                        <tr>
                                                            <td>
                                                                <span class="text-sm">{{ ucfirst(str_replace('_', ' ', $record->maintenance_type)) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-sm">{{ date('d/m/Y', strtotime($record->date_performed)) }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="text-sm">{{ number_format($record->mileage_at_service) }} km</span>
                                                            </td>
                                                            <td>
                                                                @if($record->next_due_date)
                                                                    <span class="badge bg-{{ $record->next_due_date < now() ? 'danger' : 'info' }}">
                                                                        {{ date('d/m/Y', strtotime($record->next_due_date)) }}
                                                                    </span>
                                                                @endif
                                                                
                                                                @if($record->next_due_mileage)
                                                                    <span class="badge bg-{{ ($record->next_due_mileage - $car->mileage) <= 0 ? 'danger' : 'primary' }}">
                                                                        {{ number_format($record->next_due_mileage) }} km
                                                                    </span>
                                                                @endif
                                                                
                                                                @if(!$record->next_due_date && !$record->next_due_mileage)
                                                                    <span class="text-muted text-sm">Not specified</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="text-sm">
                                                                    {{ $record->cost ? number_format($record->cost, 2) . ' MAD' : 'N/A' }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No maintenance records found for this car.
                                        </div>
                                    @endif
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

@push('css')
<style>
    .card {
        box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
        border-radius: 0.75rem;
    }
    
    .card.border {
        border: 1px solid #e9ecef !important;
        box-shadow: none;
    }

    .card .card-header {
        padding: 1.5rem;
    }

    .list-group-item {
        border-bottom: 1px solid #e9ecef;
    }
    
    .list-group-item:last-child {
        border-bottom: 0;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
    }
    
    .bg-gradient-info {
        background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .icon-box {
        min-width: 30px;
        min-height: 30px;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle edit button click
        document.querySelectorAll('.edit-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const carId = this.getAttribute('data-id');
                // Call the handleEditCar function from the global scope
                if (typeof window.handleEditCar === 'function') {
                    window.handleEditCar(carId);
                }
            });
        });
    });
</script>
@endpush
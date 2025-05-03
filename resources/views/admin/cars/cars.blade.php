@extends('admin.layouts.master')

@section('title', 'Cars Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Cars</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['total_cars'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['total_cars_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['total_cars_change'] >= 0 ? '+' : '' }}{{ $statistics['total_cars_change'] }}%
                                        </span>
                                        since last month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="fas fa-car text-lg opacity-10" aria-hidden="true"></i>
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
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Available Cars</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['available_cars'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['available_cars_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['available_cars_change'] >= 0 ? '+' : '' }}{{ $statistics['available_cars_change'] }}%
                                        </span>
                                        since last week
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
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Rented Cars</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['rented_cars'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['rented_cars_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $statistics['rented_cars_change'] >= 0 ? '+' : '' }}{{ $statistics['rented_cars_change'] }}%
                                        </span>
                                        since yesterday
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="fas fa-key text-lg opacity-10" aria-hidden="true"></i>
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
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Maintenance</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $statistics['maintenance_cars'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $statistics['maintenance_cars_needing_attention'] > 0 ? 'text-warning' : 'text-success' }} text-sm font-weight-bolder">
                                            {{ $statistics['maintenance_cars_needing_attention'] > 0 ? '+' : '' }}{{ $statistics['maintenance_cars_needing_attention'] }}
                                        </span> cars
                                        need attention
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="fas fa-tools text-lg opacity-10" aria-hidden="true"></i>
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
                                <h6 class="mb-0">Car Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create cars')
                                    <button type="button" class="btn bg-gradient-primary" id="createCarBtn">
                                        <i class="fas fa-plus"></i> Add New Car
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="cars-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Image</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Brand</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Category</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Price</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        {{-- <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Bookings</th> --}}
                                            @if(auth()->guard('admin')->user()->can('edit cars') || auth()->guard('admin')->user()->can('delete cars'))
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Car Modal -->
        <div class="modal fade" id="carModal" tabindex="-1" aria-labelledby="carModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-70">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="carModalLabel">Add New Car</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="carForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" id="method" value="POST">
                        <input type="hidden" name="car_id" id="car_id">
                        <div class="modal-body">
                            <ul class="nav nav-pills nav-fill p-1 mb-3" id="carTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                        type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Info</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                                        type="button" role="tab" aria-controls="details" aria-selected="false">Details</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="features-tab" data-bs-toggle="tab" data-bs-target="#features"
                                        type="button" role="tab" aria-controls="features"
                                        aria-selected="false">Features</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents"
                                        type="button" role="tab" aria-controls="documents"
                                        aria-selected="false">Documents</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images"
                                        type="button" role="tab" aria-controls="images" aria-selected="false">Images</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo"
                                        type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="carTabsContent">
                                <!-- Basic Info Tab -->
                                <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="name" class="form-control-label">Car Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                            <div class="invalid-feedback" id="name-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="brand_id" class="form-control-label">Brand</label>
                                            <select class="form-control" id="brand_id" name="brand_id" required>
                                                <option value="">Select Brand</option>
                                                @foreach(\App\Models\Brand::where('is_active', true)->orderBy('name')->get() as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="brand_id-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="model" class="form-control-label">Model</label>
                                            <input type="text" class="form-control" id="model" name="model" required>
                                            <div class="invalid-feedback" id="model-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="year" class="form-control-label">Year</label>
                                            <input type="number" class="form-control" id="year" name="year" min="1900"
                                                max="{{ date('Y') + 1 }}" required>
                                            <div class="invalid-feedback" id="year-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="category_id" class="form-control-label">Category</label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="category_id-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="color" class="form-control-label">Color</label>
                                            <input type="text" class="form-control" id="color" name="color">
                                            <div class="invalid-feedback" id="color-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="matricule" class="form-control-label">Matricule (License Plate)</label>
                                            <input type="text" class="form-control" id="matricule" name="matricule" required>
                                            <div class="invalid-feedback" id="matricule-error"></div>
                                            <small class="text-muted text-xs">Examples: 12345-أ-6, 12345-A-6
                                                (numbers-letter-region)</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="chassis_number" class="form-control-label">Chassis Number</label>
                                            <input type="text" class="form-control" id="chassis_number" name="chassis_number"
                                                required>
                                            <div class="invalid-feedback" id="chassis_number-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="price_per_day" class="form-control-label">Price/Day (MAD)</label>
                                            <input type="number" class="form-control" id="price_per_day" name="price_per_day"
                                                min="0" step="0.01" required>
                                            <div class="invalid-feedback" id="price_per_day-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="discount_percentage" class="form-control-label">Discount (%)</label>
                                            <input type="number" class="form-control" id="discount_percentage"
                                                name="discount_percentage" min="0" max="100" step="0.01" value="0">
                                            <div class="invalid-feedback" id="discount_percentage-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="weekly_price" class="form-control-label">Weekly Price (MAD)</label>
                                            <input type="number" class="form-control" id="weekly_price" name="weekly_price"
                                                min="0" step="0.01">
                                            <div class="invalid-feedback" id="weekly_price-error"></div>
                                            <small class="text-muted text-xs">Leave empty for automatic calculation (5
                                                days)</small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="monthly_price" class="form-control-label">Monthly Price (MAD)</label>
                                            <input type="number" class="form-control" id="monthly_price" name="monthly_price"
                                                min="0" step="0.01">
                                            <div class="invalid-feedback" id="monthly_price-error"></div>
                                            <small class="text-muted text-xs">Leave empty for automatic calculation (22
                                                days)</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="status" class="form-control-label">Status</label>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="available">Available</option>
                                                <option value="rented">Rented</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="unavailable">Unavailable</option>
                                            </select>
                                            <div class="invalid-feedback" id="status-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="mise_en_service_date" class="form-control-label">Mise en Service
                                                Date</label>
                                            <input type="date" class="form-control" id="mise_en_service_date"
                                                name="mise_en_service_date" required>
                                            <div class="invalid-feedback" id="mise_en_service_date-error"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-control-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="6"></textarea>
                                        <div class="invalid-feedback" id="description-error"></div>
                                    </div>
                                </div>

                                <!-- Details Tab -->
                                <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label for="seats" class="form-control-label">Seats</label>
                                            <input type="number" class="form-control" id="seats" name="seats" min="1" max="50"
                                                value="5" required>
                                            <div class="invalid-feedback" id="seats-error"></div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="transmission" class="form-control-label">Transmission</label>
                                            <select class="form-control" id="transmission" name="transmission" required>
                                                <option value="automatic">Automatic</option>
                                                <option value="manual">Manual</option>
                                                <option value="semi-automatic">Semi-Automatic</option>
                                            </select>
                                            <div class="invalid-feedback" id="transmission-error"></div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="fuel_type" class="form-control-label">Fuel Type</label>
                                            <select class="form-control" id="fuel_type" name="fuel_type" required>
                                                <option value="diesel">Diesel</option>
                                                <option value="gasoline">Gasoline</option>
                                                <option value="electric">Electric</option>
                                                <option value="hybrid">Hybrid</option>
                                                <option value="petrol">Petrol</option>
                                            </select>
                                            <div class="invalid-feedback" id="fuel_type-error"></div>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="engine_capacity" class="form-control-label">Engine Capacity</label>
                                            <select class="form-control" id="engine_capacity" name="engine_capacity">
                                                <option value="">Select Engine</option>
                                                <option value="1.0L">1.0L</option>
                                                <option value="1.2L">1.2L</option>
                                                <option value="1.4L">1.4L</option>
                                                <option value="1.6L">1.6L</option>
                                                <option value="1.8L">1.8L</option>
                                                <option value="2.0L">2.0L</option>
                                                <option value="2.2L">2.2L</option>
                                                <option value="2.5L">2.5L</option>
                                                <option value="3.0L">3.0L</option>
                                                <option value="3.5L">3.5L</option>
                                                <option value="4.0L">4.0L</option>
                                                <option value="5.0L">5.0L</option>
                                                <option value="electric">Electric</option>
                                            </select>
                                            <div class="invalid-feedback" id="engine_capacity-error"></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="mileage" class="form-control-label">Mileage (km)</label>
                                        <input type="number" class="form-control" id="mileage" name="mileage" min="0" required>
                                        <div class="invalid-feedback" id="mileage-error"></div>
                                    </div>
                                </div>

                                <!-- Documents Tab -->
                                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                                        <span class="alert-icon"><i class="fas fa-info-circle"></i></span>
                                        <span class="alert-text">All car documents must be kept up to date. You will
                                            receive notifications when documents are about to expire.</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="assurance_number" class="form-control-label">Insurance Number</label>
                                            <input type="text" class="form-control" id="assurance_number"
                                                name="assurance_number" required>
                                            <div class="invalid-feedback" id="assurance_number-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="assurance_company" class="form-control-label">Insurance Company</label>
                                            <input type="text" class="form-control" id="assurance_company"
                                                name="assurance_company" required>
                                            <div class="invalid-feedback" id="assurance_company-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="assurance_expiry_date" class="form-control-label">Insurance Expiry
                                                Date</label>
                                            <input type="date" class="form-control" id="assurance_expiry_date"
                                                name="assurance_expiry_date" required>
                                            <div class="invalid-feedback" id="assurance_expiry_date-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="carte_grise_number" class="form-control-label">Carte Grise
                                                Number</label>
                                            <input type="text" class="form-control" id="carte_grise_number"
                                                name="carte_grise_number" required>
                                            <div class="invalid-feedback" id="carte_grise_number-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="carte_grise_expiry_date" class="form-control-label">Carte Grise Expiry
                                                Date</label>
                                            <input type="date" class="form-control" id="carte_grise_expiry_date"
                                                name="carte_grise_expiry_date">
                                            <div class="invalid-feedback" id="carte_grise_expiry_date-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="vignette_expiry_date" class="form-control-label">Vignette Expiry
                                                Date</label>
                                            <input type="date" class="form-control" id="vignette_expiry_date"
                                                name="vignette_expiry_date" required>
                                            <div class="invalid-feedback" id="vignette_expiry_date-error"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="visite_technique_date" class="form-control-label">Technical Inspection
                                                Date</label>
                                            <input type="date" class="form-control" id="visite_technique_date"
                                                name="visite_technique_date">
                                            <div class="invalid-feedback" id="visite_technique_date-error"></div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="visite_technique_expiry_date" class="form-control-label">Technical
                                                Inspection
                                                Expiry Date</label>
                                            <input type="date" class="form-control" id="visite_technique_expiry_date"
                                                name="visite_technique_expiry_date" required>
                                            <div class="invalid-feedback" id="visite_technique_expiry_date-error"></div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="text-uppercase text-sm">Document Files</h6>
                                    <p class="text-muted text-xs mb-3">Upload copies of your documents. Allowed formats: PDF,
                                        JPG, PNG</p>

                                    <!-- Document Files -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="file_carte_grise" class="form-control-label">Carte Grise
                                                Document</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="file_carte_grise"
                                                    name="file_carte_grise" accept="application/pdf,image/*">
                                                <button type="button" class="btn bg-gradient-primary document-upload-btn"
                                                    id="carte_grise_upload_btn">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="file_carte_grise-error"></div>
                                            <div class="document-actions mt-1">
                                                <a href="#" class="document-file-indicator d-none text-sm"
                                                    id="carte_grise_file_indicator" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> View Document
                                                </a>
                                                <button type="button"
                                                    class="btn btn-link text-danger px-1 py-0 document-delete-btn"
                                                    id="carte_grise_delete_btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="file_assurance" class="form-control-label">Insurance Document</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="file_assurance"
                                                    name="file_assurance" accept="application/pdf,image/*">
                                                <button type="button" class="btn bg-gradient-primary document-upload-btn"
                                                    id="assurance_upload_btn">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="file_assurance-error"></div>
                                            <div class="document-actions mt-1">
                                                <a href="#" class="document-file-indicator d-none text-sm"
                                                    id="assurance_file_indicator" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> View Document
                                                </a>
                                                <button type="button"
                                                    class="btn btn-link text-danger px-1 py-0 document-delete-btn"
                                                    id="assurance_delete_btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="file_vignette" class="form-control-label">Vignette Document</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="file_vignette" name="file_vignette"
                                                    accept="application/pdf,image/*">
                                                <button type="button" class="btn bg-gradient-primary document-upload-btn"
                                                    id="vignette_upload_btn">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="file_vignette-error"></div>
                                            <div class="document-actions mt-1">
                                                <a href="#" class="document-file-indicator d-none text-sm"
                                                    id="vignette_file_indicator" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> View Document
                                                </a>
                                                <button type="button"
                                                    class="btn btn-link text-danger px-1 py-0 document-delete-btn"
                                                    id="vignette_delete_btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label for="file_visite_technique" class="form-control-label">Technical Inspection
                                                Document</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="file_visite_technique"
                                                    name="file_visite_technique" accept="application/pdf,image/*">
                                                <button type="button" class="btn bg-gradient-primary document-upload-btn"
                                                    id="visite_technique_upload_btn">
                                                    <i class="fas fa-upload"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback" id="file_visite_technique-error"></div>
                                            <div class="document-actions mt-1">
                                                <a href="#" class="document-file-indicator d-none text-sm"
                                                    id="visite_technique_file_indicator" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> View Document
                                                </a>
                                                <button type="button"
                                                    class="btn btn-link text-danger px-1 py-0 document-delete-btn"
                                                    id="visite_technique_delete_btn">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button type="button" class="btn bg-gradient-success" id="update_documents_btn">
                                            <i class="fas fa-sync"></i> Update Documents
                                        </button>
                                        <small class="text-muted text-xs ms-2">Click to save document changes without saving the
                                            entire
                                            car form</small>
                                    </div>
                                </div>

                                <!-- Features Tab -->
                                <div class="tab-pane fade" id="features" role="tabpanel" aria-labelledby="features-tab">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_ac"
                                                    name="features[]" value="air conditioning">
                                                <label class="form-check-label" for="feature_ac">Air Conditioning</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_bluetooth"
                                                    name="features[]" value="bluetooth">
                                                <label class="form-check-label" for="feature_bluetooth">Bluetooth</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_cruise"
                                                    name="features[]" value="cruise control">
                                                <label class="form-check-label" for="feature_cruise">Cruise Control</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_audio"
                                                    name="features[]" value="audio input">
                                                <label class="form-check-label" for="feature_audio">Audio Input</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_gps"
                                                    name="features[]" value="gps">
                                                <label class="form-check-label" for="feature_gps">GPS Navigation</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_sunroof"
                                                    name="features[]" value="sunroof">
                                                <label class="form-check-label" for="feature_sunroof">Sunroof</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_heated"
                                                    name="features[]" value="heated seats">
                                                <label class="form-check-label" for="feature_heated">Heated Seats</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_leather"
                                                    name="features[]" value="leather seats">
                                                <label class="form-check-label" for="feature_leather">Leather Seats</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_parking"
                                                    name="features[]" value="parking sensors">
                                                <label class="form-check-label" for="feature_parking">Parking Sensors</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_camera"
                                                    name="features[]" value="rear camera">
                                                <label class="form-check-label" for="feature_camera">Rear Camera</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_usb"
                                                    name="features[]" value="usb port">
                                                <label class="form-check-label" for="feature_usb">USB Port</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_carplay"
                                                    name="features[]" value="apple carplay">
                                                <label class="form-check-label" for="feature_carplay">Apple CarPlay</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_android"
                                                    name="features[]" value="android auto">
                                                <label class="form-check-label" for="feature_android">Android Auto</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_child"
                                                    name="features[]" value="child seat">
                                                <label class="form-check-label" for="feature_child">Child Seat</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="feature_wifi"
                                                    name="features[]" value="wifi">
                                                <label class="form-check-label" for="feature_wifi">WiFi</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Images Tab -->
                                <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
                                    <div class="mb-3">
                                        <label for="main_image" class="form-control-label">Main Image</label>
                                        <input type="file" class="form-control" id="main_image" name="main_image"
                                            accept="image/*">
                                        <div class="invalid-feedback" id="main_image-error"></div>
                                        <div id="main_image_preview" class="mt-2 d-none">
                                            <img src="" alt="Main Image Preview" class="img-fluid shadow-sm rounded"
                                                style="max-height: 200px;">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="additional_images" class="form-control-label">Additional Images</label>
                                        <input type="file" class="form-control" id="additional_images" name="images[]"
                                            accept="image/*" multiple>
                                        <div class="invalid-feedback" id="images-error"></div>
                                        <small class="text-muted text-xs">You can select multiple images</small>
                                    </div>

                                    <div id="gallery_container" class="d-none mt-3">
                                        <h6 class="text-uppercase text-sm">Current Images</h6>
                                        <div id="image_gallery" class="row g-3"></div>
                                        <input type="hidden" id="removed_images" name="removed_images" value="">
                                    </div>
                                </div>

                                <!-- SEO Tab -->
                                <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-control-label">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title" name="meta_title">
                                        <div class="invalid-feedback" id="meta_title-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_description" class="form-control-label">Meta Description</label>
                                        <textarea class="form-control" id="meta_description" name="meta_description"
                                            rows="3"></textarea>
                                        <div class="invalid-feedback" id="meta_description-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-control-label">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                        <div class="invalid-feedback" id="meta_keywords-error"></div>
                                        <small class="text-muted text-xs">Separate keywords with commas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="py-3 text-center">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <p>Are you sure you want to delete this car? This action cannot be undone.</p>
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
        /* Argon-style card and shadow effects */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        /* Make inputs look like Argon's */
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

        .form-control-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Nav pills styling for tabs */
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

        /* Buttons and gradients */
        .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
        }

        /* Image thumbnails for gallery */
        .image-thumbnail {
            position: relative;
            margin-bottom: 15px;
        }

        .image-thumbnail .btn-remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            padding: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .image-thumbnail img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.5rem;
            box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
        }

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .modal-70 {
            max-width: 70%;
            margin: 1.75rem auto;
        }

        /* Ensure modal can scroll properly */
        .modal-70 .modal-content {
            max-height: 85vh;
        }

        .modal-70 .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
            padding: 1.5rem;
        }

        /* Document actions styling */
        .document-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .document-file-indicator {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #5e72e4;
            text-decoration: none;
        }

        /* DataTable styling */
        table.dataTable {
            margin-top: 0 !important;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            border-bottom-width: 1px;
        }

        .table td {
            white-space: nowrap;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 0.875rem;
            color: #8392AB;
            padding: 1rem 1.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            color: white !important;
            border: none;
            border-radius: 0.5rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f6f9fc;
            color: #5e72e4 !important;
            border: 1px solid #f6f9fc;
        }

        /* Alert styling */
        .alert {
            border: 0;
            border-radius: 0.5rem;
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
        }

        /* Fix for CKEditor height */
        .ck-editor__editable_inline {
            min-height: 250px;
        }

        /* Loading overlay for AJAX */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 0.75rem;
        }

        /* Expiry warnings */
        .is-warning {
            border-color: #fb6340;
            background-color: rgba(251, 99, 64, 0.1);
        }

        .expiry-warning {
            font-size: 0.75rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.cars.data') }}",
            storeUrl: "{{ route('admin.cars.store') }}",
            editUrl: "{{ route('admin.cars.edit', ':id') }}",
            updateUrl: "{{ route('admin.cars.update', ':id') }}",
            deleteUrl: "{{ route('admin.cars.destroy', ':id') }}",
            deleteImageUrl: "{{ route('admin.cars.delete-image', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditCars = @json(auth()->guard('admin')->user()->can('edit cars'));
        const canDeleteCars = @json(auth()->guard('admin')->user()->can('delete cars'));
    </script>

    <!-- Include the JS for cars management -->
    <script src="{{asset('admin/js/cars-management.js')}}"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.cars.data') }}",
            storeUrl: "{{ route('admin.cars.store') }}",
            editUrl: "{{ route('admin.cars.edit', ':id') }}",
            updateUrl: "{{ route('admin.cars.update', ':id') }}",
            deleteUrl: "{{ route('admin.cars.destroy', ':id') }}",
            deleteImageUrl: "{{ route('admin.cars.delete-image') }}"
        };

        // Pass permissions data to JavaScript
        const canEditCars = @json(auth()->guard('admin')->user()->can('edit cars'));
        const canDeleteCars = @json(auth()->guard('admin')->user()->can('delete cars'));

        document.addEventListener('DOMContentLoaded', function () {
            // DOM Elements
            const carForm = document.getElementById('carForm');
            const carModal = document.getElementById('carModal');
            const createCarBtn = document.getElementById('createCarBtn');
            const saveBtn = document.getElementById('saveBtn');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const mainImagePreview = document.getElementById('main_image_preview');
            const galleryContainer = document.getElementById('gallery_container');
            const imageGallery = document.getElementById('image_gallery');
            const removedImagesInput = document.getElementById('removed_images');

            // Add asterisks to required field labels
            document.querySelectorAll('#carForm [required]').forEach(element => {
                const labelFor = element.id;
                const label = document.querySelector(`label[for="${labelFor}"]`);
                if (label && !label.innerHTML.includes('*')) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }

                // Real-time validation
                element.addEventListener('blur', function () {
                    validateField(this);
                });

                element.addEventListener('input', function () {
                    this.classList.remove('is-invalid');
                    const errorElement = document.getElementById(`${this.id}-error`);
                    if (errorElement) {
                        errorElement.textContent = '';
                    }
                });
            });

            // Initialize DataTable
            const table = new DataTable('#cars-table', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.dataUrl,
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'image', name: 'image', orderable: false, searchable: false },
                    { data: 'brand_model', name: 'brand_model' },
                    { data: 'brand.name', name: 'brand.name' },
                    { data: 'category.name', name: 'category.name' },
                    { data: 'price', name: 'price' },
                    { data: 'status', name: 'status' },
                    { data: 'bookings_count', name: 'bookings_count' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    paginate: {
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>'
                    }
                }
            });

            // Initialize CKEditor
            let editor;
            ClassicEditor
                .create(document.querySelector('#description'))
                .then(newEditor => {
                    editor = newEditor;
                })
                .catch(error => {
                    console.error('CKEditor error:', error);
                });

            // Setup event listeners
            if (createCarBtn) {
                createCarBtn.addEventListener('click', function () {
                    resetForm();
                    document.getElementById('carModalLabel').textContent = 'Add New Car';
                    document.getElementById('method').value = 'POST';

                    // Reset CKEditor if available
                    if (editor) {
                        editor.setData('');
                    }

                    // Show modal
                    $(carModal).modal('show');
                });
            }

            if (carForm) {
                carForm.addEventListener('submit', handleFormSubmit);
            }

            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function () {
                    const carId = document.getElementById('car_id').value;
                    if (carId) {
                        deleteCarConfirmed(carId);
                    }
                });
            }

            // Main image preview
            document.getElementById('main_image').addEventListener('change', function () {
                previewImage(this, 'main_image_preview');
            });

            // Additional images preview
            document.getElementById('additional_images').addEventListener('change', function () {
                previewAdditionalImages(this);
            });

            // Document update button
            document.getElementById('update_documents_btn').addEventListener('click', function () {
                const carId = document.getElementById('car_id').value;
                if (!carId) {
                    showAlert('Warning', 'Please save the car first before updating documents', 'warning');
                    return;
                }
                updateDocuments(carId);
            });

            // Tab navigation improvement
            document.querySelectorAll('#carTabs .nav-link').forEach(function (tabLink) {
                tabLink.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('data-bs-target');
                    const tabPanes = document.querySelectorAll('#carTabsContent .tab-pane');
                    const navLinks = document.querySelectorAll('#carTabs .nav-link');

                    // Remove active class from all tabs and panes
                    navLinks.forEach(link => link.classList.remove('active'));
                    tabPanes.forEach(pane => pane.classList.remove('active', 'show'));

                    // Add active class to current tab and pane
                    this.classList.add('active');
                    document.querySelector(targetId).classList.add('active', 'show');
                });
            });

            // Event delegation for action buttons
            document.addEventListener('click', function (e) {
                // Edit button
                if (e.target.closest('.edit-btn')) {
                    const button = e.target.closest('.edit-btn');
                    const carId = button.getAttribute('data-id');
                    if (carId) handleEditCar(carId);
                }

                // Delete button
                if (e.target.closest('.delete-btn')) {
                    const button = e.target.closest('.delete-btn');
                    const carId = button.getAttribute('data-id');
                    if (carId) {
                        document.getElementById('car_id').value = carId;
                        $('#deleteModal').modal('show');
                    }
                }

                // Remove image button
                if (e.target.closest('.btn-remove-image')) {
                    const button = e.target.closest('.btn-remove-image');
                    const imageContainer = button.closest('.image-thumbnail');
                    const imageId = imageContainer.getAttribute('data-id');

                    if (imageId) {
                        const currentValue = removedImagesInput.value;
                        removedImagesInput.value = currentValue ? `${currentValue},${imageId}` : imageId;
                        imageContainer.remove();

                        // Hide gallery if empty
                        if (imageGallery.children.length === 0) {
                            galleryContainer.classList.add('d-none');
                        }
                    }
                }
            });

            /**
             * Handle form submission with AJAX
             */
            function handleFormSubmit(e) {
                e.preventDefault();

                // Clear previous validation errors
                clearValidationErrors();

                // Perform client-side validation
                if (!validateForm()) {
                    return;
                }

                // Update CKEditor content before submission
                if (editor) {
                    document.getElementById('description').value = editor.getData();
                }

                // Create FormData
                const formData = new FormData(carForm);

                // Get car ID and determine if this is an edit operation
                const carId = document.getElementById('car_id').value;
                const isEdit = carId && carId !== '';

                // Set URL based on operation
                const url = isEdit ? routes.updateUrl.replace(':id', carId) : routes.storeUrl;

                // Show loading state on button
                const saveBtnText = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

                // Send AJAX request
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
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
                            $(carModal).modal('hide');

                            // Reload table
                            table.ajax.reload();

                            // Show success toast
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message || 'Car saved successfully',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                toast: true,
                                position: 'top-end'
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

                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please check the form for errors.',
                                confirmButtonColor: '#5e72e4'
                            });
                        } else {
                            // Show general error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.data?.message || 'An error occurred while saving the car.',
                                confirmButtonColor: '#5e72e4'
                            });
                        }
                    })
                    .finally(() => {
                        // Reset button state
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = saveBtnText;
                    });
            }

            /**
             * Validate form fields
             */
            function validateForm() {
                let isValid = true;

                // Required fields
                document.querySelectorAll('#carForm [required]').forEach(element => {
                    if (!element.value.trim()) {
                        element.classList.add('is-invalid');
                        const errorElement = document.getElementById(`${element.id}-error`);
                        if (errorElement) {
                            errorElement.textContent = 'This field is required';
                        }
                        isValid = false;
                    }
                });

                // Validate matricule
                const matricule = document.getElementById('matricule');
                if (matricule && matricule.value && !validateMatricule(matricule.value)) {
                    matricule.classList.add('is-invalid');
                    const errorElement = document.getElementById('matricule-error');
                    if (errorElement) {
                        errorElement.textContent = 'Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)';
                    }
                    isValid = false;
                }

                // Date validations
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Validate expiry dates
                const dateFields = [
                    { field: 'assurance_expiry_date', name: 'Insurance expiry date' },
                    { field: 'vignette_expiry_date', name: 'Vignette expiry date' },
                    { field: 'visite_technique_expiry_date', name: 'Technical inspection expiry date' }
                ];

                dateFields.forEach(item => {
                    const field = document.getElementById(item.field);
                    if (field && field.value) {
                        const date = new Date(field.value);
                        if (date < today) {
                            field.classList.add('is-invalid');
                            const errorElement = document.getElementById(`${item.field}-error`);
                            if (errorElement) {
                                errorElement.textContent = `${item.name} must be in the future`;
                            }
                            isValid = false;
                        }
                    }
                });

                // If validation fails, show first tab with errors
                if (!isValid) {
                    const firstErrorField = document.querySelector('#carForm .is-invalid');
                    if (firstErrorField) {
                        const tabPane = firstErrorField.closest('.tab-pane');
                        if (tabPane) {
                            const tabId = tabPane.id;
                            document.querySelector(`#carTabs button[data-bs-target="#${tabId}"]`).click();
                        }
                    }
                }

                return isValid;
            }

            /**
             * Validate a single field
             */
            function validateField(field) {
                // Required validation
                if (field.hasAttribute('required') && !field.value.trim()) {
                    field.classList.add('is-invalid');
                    const errorElement = document.getElementById(`${field.id}-error`);
                    if (errorElement) {
                        errorElement.textContent = 'This field is required';
                    }
                    return false;
                }

                // Matricule validation
                if (field.id === 'matricule' && field.value && !validateMatricule(field.value)) {
                    field.classList.add('is-invalid');
                    const errorElement = document.getElementById('matricule-error');
                    if (errorElement) {
                        errorElement.textContent = 'Invalid format. Should be: numbers-letter-region code (e.g. 12345-A-6 or 12345-أ-6)';
                    }
                    return false;
                }

                // Date validation for expiry dates
                if (['assurance_expiry_date', 'vignette_expiry_date', 'visite_technique_expiry_date'].includes(field.id) && field.value) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const date = new Date(field.value);

                    if (date < today) {
                        field.classList.add('is-invalid');
                        const errorElement = document.getElementById(`${field.id}-error`);
                        if (errorElement) {
                            errorElement.textContent = 'Date must be in the future';
                        }
                        return false;
                    }
                }

                // Field is valid
                field.classList.remove('is-invalid');
                const errorElement = document.getElementById(`${field.id}-error`);
                if (errorElement) {
                    errorElement.textContent = '';
                }
                return true;
            }

            /**
             * Validate Moroccan license plate
             */
            function validateMatricule(value) {
                if (!value) return true;

                // Accept different separator styles
                const normalized = value.replace(/[-|]/g, '');

                // Pattern: digits + letter (Arabic or Latin) + region code
                const moroccanPlateRegex = /^(\d{1, 5})([A-Za-z]|[\u0600-\u06FF])(\d{1, 2})$/u;

                return moroccanPlateRegex.test(normalized);
            }

            /**
             * Format Moroccan license plate
             */
            function formatMatricule(value) {
                if (!value) return value;

                // First normalize by removing all separators
                const normalized = value.replace(/[-|]/g, '');

                // Check if it matches the Moroccan pattern for digits + letter + region
                const moroccanPlateRegex = /^(\d{1, 5})([A-Za-z]|[\u0600-\u06FF])(\d{1, 2})$/u;

                if (moroccanPlateRegex.test(normalized)) {
                    const matches = normalized.match(moroccanPlateRegex);
                    if (matches && matches.length === 4) {
                        const digits = matches[1];
                        const letter = matches[2];
                        const regionCode = matches[3];

                        // Format with hyphens for better readability
                        return `${digits}-${letter}-${regionCode}`;
                    }
                }

                return value;
            }

            /**
             * Handle edit car operation
             */
            function handleEditCar(carId) {
                resetForm();

                // Set ID and method
                document.getElementById('car_id').value = carId;
                document.getElementById('method').value = 'PUT';
                document.getElementById('carModalLabel').textContent = 'Edit Car';

                // Show loading indicator in modal
                const modalBody = document.querySelector('#carModal .modal-body');
                if (modalBody) {
                    const loadingDiv = document.createElement('div');
                    loadingDiv.id = 'loading-overlay';
                    loadingDiv.className = 'loading-overlay';
                    loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
                    modalBody.appendChild(loadingDiv);
                }

                // Show modal
                $(carModal).modal('show');

                // Fetch car data
                fetch(routes.editUrl.replace(':id', carId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Remove loading overlay
                        const overlay = document.getElementById('loading-overlay');
                        if (overlay) overlay.remove();

                        if (data.success && data.car) {
                            const car = data.car;

                            // Fill form with car data
                            fillCarForm(car);

                            // Update CKEditor content
                            if (editor) {
                                editor.setData(car.description || '');
                            }
                        } else {
                            // Show error
                            $(carModal).modal('hide');
                            showAlert('Error', 'Failed to load car data', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Remove loading overlay
                        const overlay = document.getElementById('loading-overlay');
                        if (overlay) overlay.remove();

                        // Show error and hide modal
                        $(carModal).modal('hide');
                        showAlert('Error', 'Failed to load car data', 'error');
                    });
            }

            /**
             * Fill car form with data
             */
            function fillCarForm(car) {
                // Basic info
                document.getElementById('name').value = car.name || '';
                document.getElementById('brand_id').value = car.brand_id || '';
                document.getElementById('model').value = car.model || '';
                document.getElementById('year').value = car.year || '';
                document.getElementById('category_id').value = car.category_id || '';
                document.getElementById('color').value = car.color || '';
                document.getElementById('chassis_number').value = car.chassis_number || '';
                document.getElementById('matricule').value = car.matricule || '';
                document.getElementById('status').value = car.status || 'available';
                document.getElementById('price_per_day').value = car.price_per_day || '';
                document.getElementById('weekly_price').value = car.weekly_price || '';
                document.getElementById('monthly_price').value = car.monthly_price || '';
                document.getElementById('discount_percentage').value = car.discount_percentage || 0;

                // Format dates
                if (car.mise_en_service_date) {
                    document.getElementById('mise_en_service_date').value = formatDate(car.mise_en_service_date);
                }

                // Details
                document.getElementById('seats').value = car.seats || '';
                document.getElementById('transmission').value = car.transmission || '';
                document.getElementById('fuel_type').value = car.fuel_type || '';
                document.getElementById('engine_capacity').value = car.engine_capacity || '';
                document.getElementById('mileage').value = car.mileage || '';

                // SEO
                document.getElementById('meta_title').value = car.meta_title || '';
                document.getElementById('meta_description').value = car.meta_description || '';
                document.getElementById('meta_keywords').value = car.meta_keywords || '';

                // Features
                document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
                    checkbox.checked = car.features && car.features.includes(checkbox.value);
                });

                // Documents
                if (car.documents) {
                    fillDocumentsForm(car.documents);
                }

                // Images
                showCarImages(car);
            }

            /**
             * Fill documents form with data
             */
            function fillDocumentsForm(documents) {
                document.getElementById('assurance_number').value = documents.assurance_number || '';
                document.getElementById('assurance_company').value = documents.assurance_company || '';
                document.getElementById('carte_grise_number').value = documents.carte_grise_number || '';

                // Format dates
                if (documents.assurance_expiry_date) {
                    document.getElementById('assurance_expiry_date').value = formatDate(documents.assurance_expiry_date);
                }
                if (documents.carte_grise_expiry_date) {
                    document.getElementById('carte_grise_expiry_date').value = formatDate(documents.carte_grise_expiry_date);
                }
                if (documents.vignette_expiry_date) {
                    document.getElementById('vignette_expiry_date').value = formatDate(documents.vignette_expiry_date);
                }
                if (documents.visite_technique_date) {
                    document.getElementById('visite_technique_date').value = formatDate(documents.visite_technique_date);
                }
                if (documents.visite_technique_expiry_date) {
                    document.getElementById('visite_technique_expiry_date').value = formatDate(documents.visite_technique_expiry_date);
                }

                // Show document file indicators
                showDocumentFileIndicators(documents);
            }

            /**
             * Show document file indicators
             */
            function showDocumentFileIndicators(documents) {
                if (documents.file_carte_grise) {
                    const indicator = document.getElementById('carte_grise_file_indicator');
                    indicator.classList.remove('d-none');
                    indicator.href = `/storage/${documents.file_carte_grise}`;
                }

                if (documents.file_assurance) {
                    const indicator = document.getElementById('assurance_file_indicator');
                    indicator.classList.remove('d-none');
                    indicator.href = `/storage/${documents.file_assurance}`;
                }

                if (documents.file_visite_technique) {
                    const indicator = document.getElementById('visite_technique_file_indicator');
                    indicator.classList.remove('d-none');
                    indicator.href = `/storage/${documents.file_visite_technique}`;
                }

                if (documents.file_vignette) {
                    const indicator = document.getElementById('vignette_file_indicator');
                    indicator.classList.remove('d-none');
                    indicator.href = `/storage/${documents.file_vignette}`;
                }
            }

            /**
             * Show car images
             */
            function showCarImages(car) {
                // Main image
                if (car.main_image) {
                    const mainImageUrl = car.main_image.startsWith('http')
                        ? car.main_image
                        : `/storage/${car.main_image}`;

                    const preview = document.getElementById('main_image_preview');
                    preview.classList.remove('d-none');
                    preview.querySelector('img').src = mainImageUrl;
                }

                // Gallery images
                if (car.images && car.images.length) {
                    const gallery = document.getElementById('image_gallery');
                    const container = document.getElementById('gallery_container');

                    gallery.innerHTML = '';
                    container.classList.remove('d-none');

                    car.images.forEach(image => {
                        const imagePath = image.image_path || image.path;
                        const imageUrl = imagePath.startsWith('http')
                            ? imagePath
                            : `/storage/${imagePath}`;

                        const imageHtml = `
                    <div class="col-md-4 image-thumbnail" data-id="${image.id}">
                        <img src="${imageUrl}" class="img-fluid rounded" alt="Car Image">
                            <button type="button" class="btn btn-sm btn-remove-image btn-danger">
                                <i class="fas fa-times"></i>
                            </button>
                    </div>
                    `;

                        gallery.insertAdjacentHTML('beforeend', imageHtml);
                    });
                }
            }

            /**
             * Format date for input fields
             */
            function formatDate(dateString) {
                if (!dateString) return '';

                // Check if date already in correct format
                if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                    return dateString;
                }

                // Parse date
                const date = new Date(dateString);
                if (isNaN(date.getTime())) {
                    return '';
                }

                // Format as YYYY-MM-DD
                return date.toISOString().split('T')[0];
            }

            /**
             * Delete car confirmed
             */
            function deleteCarConfirmed(carId) {
                // Show loading state
                const deleteBtn = document.getElementById('confirmDeleteBtn');
                const originalText = deleteBtn.innerHTML;
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

                fetch(routes.deleteUrl.replace(':id', carId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hide modal and reload table
                            $('#deleteModal').modal('hide');
                            table.ajax.reload();

                            // Show success message
                            showAlert('Success', data.message || 'Car deleted successfully', 'success');
                        } else {
                            throw new Error(data.message || 'Failed to delete car');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Show error message
                        showAlert('Error', error.message || 'Failed to delete car', 'error');
                    })
                    .finally(() => {
                        // Reset button state
                        deleteBtn.disabled = false;
                        deleteBtn.innerHTML = originalText;
                    });
            }

            /**
             * Preview main image
             */
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                const previewImg = preview.querySelector('img');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('d-none');
                    }
                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.classList.add('d-none');
                }
            }

            /**
             * Preview additional images
             */
            function previewAdditionalImages(input) {
                if (input.files && input.files.length > 0) {
                    const gallery = document.getElementById('image_gallery');
                    const container = document.getElementById('gallery_container');

                    container.classList.remove('d-none');

                    for (let i = 0; i < input.files.length; i++) {
                        const file = input.files[i];
                        const reader = new FileReader();

                        reader.onload = function (e) {
                            const imageHtml = `
                    <div class="col-md-4 image-thumbnail">
                        <img src="${e.target.result}" class="img-fluid rounded" alt="New Car Image">
                            <small class="text-muted">New image (not yet saved)</small>
                    </div>
                    `;

                            gallery.insertAdjacentHTML('beforeend', imageHtml);
                        };

                        reader.readAsDataURL(file);
                    }
                }
            }

            /**
             * Update documents
             */
            function updateDocuments(carId) {
                // Create FormData for documents only
                const formData = new FormData();

                // Add CSRF token
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

                // Add document fields
                formData.append('assurance_number', document.getElementById('assurance_number').value);
                formData.append('assurance_company', document.getElementById('assurance_company').value);
                formData.append('assurance_expiry_date', document.getElementById('assurance_expiry_date').value);
                formData.append('carte_grise_number', document.getElementById('carte_grise_number').value);
                formData.append('carte_grise_expiry_date', document.getElementById('carte_grise_expiry_date').value);
                formData.append('vignette_expiry_date', document.getElementById('vignette_expiry_date').value);
                formData.append('visite_technique_date', document.getElementById('visite_technique_date').value);
                formData.append('visite_technique_expiry_date', document.getElementById('visite_technique_expiry_date').value);

                // Add document files if selected
                const fileInputs = {
                    'file_carte_grise': document.getElementById('file_carte_grise'),
                    'file_assurance': document.getElementById('file_assurance'),
                    'file_visite_technique': document.getElementById('file_visite_technique'),
                    'file_vignette': document.getElementById('file_vignette')
                };

                for (const [name, input] of Object.entries(fileInputs)) {
                    if (input.files && input.files[0]) {
                        formData.append(name, input.files[0]);
                    }
                }

                // Show loading state
                const updateBtn = document.getElementById('update_documents_btn');
                const originalText = updateBtn.innerHTML;
                updateBtn.disabled = true;
                updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

                // Send request
                fetch(`/admin/cars/${carId}/documents/update`, {
                    method: 'POST',
                    body: formData,
                    headers: {
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
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message || 'Documents updated successfully',
                                confirmButtonColor: '#5e72e4'
                            });

                            // Update document file indicators
                            if (data.documents) {
                                showDocumentFileIndicators(data.documents);
                            }

                            // Clear file inputs
                            for (const input of Object.values(fileInputs)) {
                                input.value = '';
                            }
                        } else {
                            throw new Error(data.message || 'Failed to update documents');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Handle validation errors
                        if (error.status === 422 && error.data && error.data.errors) {
                            displayValidationErrors(error.data.errors);

                            // Show error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: 'Please check the document fields for errors',
                                confirmButtonColor: '#5e72e4'
                            });
                        } else {
                            // Show general error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.data?.message || 'Failed to update documents',
                                confirmButtonColor: '#5e72e4'
                            });
                        }
                    })
                    .finally(() => {
                        // Reset button state
                        updateBtn.disabled = false;
                        updateBtn.innerHTML = originalText;
                    });
            }

            /**
             * Reset form and clear validation
             */
            function resetForm() {
                // Reset form fields
                carForm.reset();

                // Clear validation errors
                clearValidationErrors();

                // Reset image previews
                document.getElementById('main_image_preview').classList.add('d-none');
                document.getElementById('gallery_container').classList.add('d-none');
                document.getElementById('image_gallery').innerHTML = '';
                document.getElementById('removed_images').value = '';

                // Reset document file indicators
                document.querySelectorAll('.document-file-indicator').forEach(element => {
                    element.classList.add('d-none');
                });

                // Set default date values
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('mise_en_service_date').value = today;

                // Set default document expiry dates (1 year from today)
                const oneYearFromNow = new Date();
                oneYearFromNow.setFullYear(oneYearFromNow.getFullYear() + 1);
                const oneYearFromNowString = oneYearFromNow.toISOString().split('T')[0];

                document.getElementById('assurance_expiry_date').value = oneYearFromNowString;
                document.getElementById('vignette_expiry_date').value = oneYearFromNowString;
                document.getElementById('visite_technique_expiry_date').value = oneYearFromNowString;

                // Reset CKEditor if available
                if (editor) {
                    editor.setData('');
                }
            }

            /**
             * Clear validation errors
             */
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                document.querySelectorAll('.invalid-feedback').forEach(element => {
                    element.textContent = '';
                });
            }

            /**
             * Display validation errors
             */
            function displayValidationErrors(errors) {
                for (const field in errors) {
                    const element = document.getElementById(field);
                    const errorElement = document.getElementById(`${field}-error`);

                    if (element) {
                        element.classList.add('is-invalid');
                    }

                    if (errorElement && errors[field][0]) {
                        errorElement.textContent = errors[field][0];
                    }
                }

                // Show the first tab with errors
                const firstErrorElement = document.querySelector('.is-invalid');
                if (firstErrorElement) {
                    const tabPane = firstErrorElement.closest('.tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id;
                        document.querySelector(`#carTabs button[data-bs-target="#${tabId}"]`).click();
                    }
                }
            }

            /**
             * Show alert message
             */
            function showAlert(title, text, icon) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonColor: '#5e72e4',
                    confirmButtonText: 'OK'
                });
            }

            // Make functions available globally
            window.handleEditCar = handleEditCar;
            window.showAlert = showAlert;
        });
    </script>
@endpush
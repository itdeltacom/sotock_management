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
                                        120
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">+5%</span>
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
                                        86
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">+3%</span>
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
                                        25
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-danger text-sm font-weight-bolder">-2%</span>
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
                                        9
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-warning text-sm font-weight-bolder">+5</span> cars
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
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Bookings</th>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize CKEditor
            let editor;

            ClassicEditor
                .create(document.querySelector('#description'))
                .then(newEditor => {
                    editor = newEditor;

                    // Save CKEditor content to form when submitting
                    document.getElementById('carForm').addEventListener('submit', function () {
                        document.getElementById('description').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });

            // Fix Bootstrap modal tab navigation
            document.querySelectorAll('#carTabs .nav-link').forEach(function (tabLink) {
                tabLink.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('data-bs-target');
                    const tabPanes = document.querySelectorAll('#carTabsContent .tab-pane');
                    const navLinks = document.querySelectorAll('#carTabs .nav-link');

                    // Remove active class from all nav links and tab panes
                    navLinks.forEach(link => link.classList.remove('active'));
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });

                    // Add active class to current nav link and tab pane
                    this.classList.add('active');
                    document.querySelector(targetId).classList.add('active', 'show');
                });
            });

            // Modify the original handleEditCar function to set CKEditor content
            const originalHandleEditCar = window.handleEditCar;
            if (typeof originalHandleEditCar === 'function') {
                window.handleEditCar = function (carId) {
                    originalHandleEditCar(carId);

                    // Add additional code to set CKEditor content after modal is shown
                    $('#carModal').on('shown.bs.modal', function () {
                        if (editor && document.getElementById('description')) {
                            const descriptionValue = document.getElementById('description').value;
                            editor.setData(descriptionValue);
                        }
                    });
                };
            }

            // Configure SweetAlert to use Argon style
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Override the showAlert function to use Argon styled alerts
            if (typeof window.showAlert !== 'function') {
                window.showAlert = function (title, text, icon) {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        confirmButtonColor: '#5e72e4',
                        buttonsStyling: true,
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary'
                        }
                    });
                };
            }
        });
    </script>
@endpush

{{-- @extends('admin.layouts.master')

@section('title', 'Cars Management')

@section('content')
<!-- Spinner Start -->
<div id="spinner"
    class="show position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
</div>
<!-- Spinner End -->

<div class="container-fluid py-4">
    <!-- Dashboard Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="bg-light rounded p-4 d-flex align-items-center">
                <div class="ms-3">
                    <p class="mb-2 text-primary">Total Cars</p>
                    <h5 class="mb-0">120</h5>
                    <span class="text-success small pt-1 fw-bold">+5%</span>
                    <span class="text-muted small pt-2">since last month</span>
                </div>
                <i class="fa fa-car fa-3x text-primary ms-auto"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="bg-light rounded p-4 d-flex align-items-center">
                <div class="ms-3">
                    <p class="mb-2 text-primary">Available Cars</p>
                    <h5 class="mb-0">86</h5>
                    <span class="text-success small pt-1 fw-bold">+3%</span>
                    <span class="text-muted small pt-2">since last week</span>
                </div>
                <i class="fa fa-check-circle fa-3x text-success ms-auto"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="bg-light rounded p-4 d-flex align-items-center">
                <div class="ms-3">
                    <p class="mb-2 text-primary">Rented Cars</p>
                    <h5 class="mb-0">25</h5>
                    <span class="text-danger small pt-1 fw-bold">-2%</span>
                    <span class="text-muted small pt-2">since yesterday</span>
                </div>
                <i class="fa fa-key fa-3x text-warning ms-auto"></i>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="bg-light rounded p-4 d-flex align-items-center">
                <div class="ms-3">
                    <p class="mb-2 text-primary">Maintenance</p>
                    <h5 class="mb-0">9</h5>
                    <span class="text-warning small pt-1 fw-bold">+5</span>
                    <span class="text-muted small pt-2">cars need attention</span>
                </div>
                <i class="fa fa-tools fa-3x text-danger ms-auto"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="bg-light rounded p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-primary">Car Management</h4>
                    @can('create cars')
                    <button type="button" class="btn btn-primary" id="createCarBtn">
                        <i class="fas fa-plus me-2"></i>Add New Car
                    </button>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="cars-table" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Bookings</th>
                                @if(auth()->guard('admin')->user()->can('edit cars') ||
                                auth()->guard('admin')->user()->can('delete cars'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Car Modal -->
<div class="modal fade" id="carModal" tabindex="-1" aria-labelledby="carModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-70">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="carModalLabel">Add New Car</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="carForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="method" value="POST">
                <input type="hidden" name="car_id" id="car_id">
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="carTabs" role="tablist">
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

                    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="carTabsContent">
                        <!-- Basic Info Tab -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Car Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback" id="name-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="brand_id" class="form-label">Brand</label>
                                    <select class="form-select" id="brand_id" name="brand_id" required>
                                        <option value="">Select Brand</option>
                                        @foreach(\App\Models\Brand::where('is_active', true)->orderBy('name')->get() as
                                        $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="brand_id-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="model" class="form-label">Model</label>
                                    <input type="text" class="form-control" id="model" name="model" required>
                                    <div class="invalid-feedback" id="model-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="year" class="form-label">Year</label>
                                    <input type="number" class="form-control" id="year" name="year" min="1900"
                                        max="{{ date('Y') + 1 }}" required>
                                    <div class="invalid-feedback" id="year-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Category</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get()
                                        as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="category_id-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control" id="color" name="color">
                                    <div class="invalid-feedback" id="color-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="matricule" class="form-label">Matricule (License Plate)</label>
                                    <input type="text" class="form-control" id="matricule" name="matricule" required>
                                    <div class="invalid-feedback" id="matricule-error"></div>
                                    <small class="form-text text-muted">Examples: 12345-أ-6, 12345-A-6
                                        (numbers-letter-region)</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="chassis_number" class="form-label">Chassis Number</label>
                                    <input type="text" class="form-control" id="chassis_number" name="chassis_number"
                                        required>
                                    <div class="invalid-feedback" id="chassis_number-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="price_per_day" class="form-label">Price/Day (MAD)</label>
                                    <input type="number" class="form-control" id="price_per_day" name="price_per_day"
                                        min="0" step="0.01" required>
                                    <div class="invalid-feedback" id="price_per_day-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="discount_percentage" class="form-label">Discount (%)</label>
                                    <input type="number" class="form-control" id="discount_percentage"
                                        name="discount_percentage" min="0" max="100" step="0.01" value="0">
                                    <div class="invalid-feedback" id="discount_percentage-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="weekly_price" class="form-label">Weekly Price (MAD)</label>
                                    <input type="number" class="form-control" id="weekly_price" name="weekly_price"
                                        min="0" step="0.01">
                                    <div class="invalid-feedback" id="weekly_price-error"></div>
                                    <small class="form-text text-muted">Leave empty for automatic calculation (5
                                        days)</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="monthly_price" class="form-label">Monthly Price (MAD)</label>
                                    <input type="number" class="form-control" id="monthly_price" name="monthly_price"
                                        min="0" step="0.01">
                                    <div class="invalid-feedback" id="monthly_price-error"></div>
                                    <small class="form-text text-muted">Leave empty for automatic calculation (22
                                        days)</small>
                                </div>

                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="available">Available</option>
                                        <option value="rented">Rented</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="unavailable">Unavailable</option>
                                    </select>
                                    <div class="invalid-feedback" id="status-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="mise_en_service_date" class="form-label">Mise en Service Date</label>
                                    <input type="date" class="form-control" id="mise_en_service_date"
                                        name="mise_en_service_date" required>
                                    <div class="invalid-feedback" id="mise_en_service_date-error"></div>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description"
                                        rows="6"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="seats" class="form-label">Seats</label>
                                    <input type="number" class="form-control" id="seats" name="seats" min="1" max="50"
                                        value="5" required>
                                    <div class="invalid-feedback" id="seats-error"></div>
                                </div>

                                <div class="col-md-3">
                                    <label for="transmission" class="form-label">Transmission</label>
                                    <select class="form-select" id="transmission" name="transmission" required>
                                        <option value="automatic">Automatic</option>
                                        <option value="manual">Manual</option>
                                        <option value="semi-automatic">Semi-Automatic</option>
                                    </select>
                                    <div class="invalid-feedback" id="transmission-error"></div>
                                </div>

                                <div class="col-md-3">
                                    <label for="fuel_type" class="form-label">Fuel Type</label>
                                    <select class="form-select" id="fuel_type" name="fuel_type" required>
                                        <option value="diesel">Diesel</option>
                                        <option value="gasoline">Gasoline</option>
                                        <option value="electric">Electric</option>
                                        <option value="hybrid">Hybrid</option>
                                        <option value="petrol">Petrol</option>
                                    </select>
                                    <div class="invalid-feedback" id="fuel_type-error"></div>
                                </div>

                                <div class="col-md-3">
                                    <label for="engine_capacity" class="form-label">Engine Capacity</label>
                                    <select class="form-select" id="engine_capacity" name="engine_capacity">
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

                                <div class="col-12">
                                    <label for="mileage" class="form-label">Mileage (km)</label>
                                    <input type="number" class="form-control" id="mileage" name="mileage" min="0"
                                        required>
                                    <div class="invalid-feedback" id="mileage-error"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> All car documents must be kept up to date. You
                                will
                                receive notifications when documents are about to expire.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="assurance_number" class="form-label">Insurance Number</label>
                                    <input type="text" class="form-control" id="assurance_number"
                                        name="assurance_number" required>
                                    <div class="invalid-feedback" id="assurance_number-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="assurance_company" class="form-label">Insurance Company</label>
                                    <input type="text" class="form-control" id="assurance_company"
                                        name="assurance_company" required>
                                    <div class="invalid-feedback" id="assurance_company-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="assurance_expiry_date" class="form-label">Insurance Expiry Date</label>
                                    <input type="date" class="form-control" id="assurance_expiry_date"
                                        name="assurance_expiry_date" required>
                                    <div class="invalid-feedback" id="assurance_expiry_date-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="carte_grise_number" class="form-label">Carte Grise Number</label>
                                    <input type="text" class="form-control" id="carte_grise_number"
                                        name="carte_grise_number" required>
                                    <div class="invalid-feedback" id="carte_grise_number-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="carte_grise_expiry_date" class="form-label">Carte Grise Expiry
                                        Date</label>
                                    <input type="date" class="form-control" id="carte_grise_expiry_date"
                                        name="carte_grise_expiry_date">
                                    <div class="invalid-feedback" id="carte_grise_expiry_date-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="vignette_expiry_date" class="form-label">Vignette Expiry Date</label>
                                    <input type="date" class="form-control" id="vignette_expiry_date"
                                        name="vignette_expiry_date" required>
                                    <div class="invalid-feedback" id="vignette_expiry_date-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="visite_technique_date" class="form-label">Technical Inspection
                                        Date</label>
                                    <input type="date" class="form-control" id="visite_technique_date"
                                        name="visite_technique_date">
                                    <div class="invalid-feedback" id="visite_technique_date-error"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="visite_technique_expiry_date" class="form-label">Technical Inspection
                                        Expiry Date</label>
                                    <input type="date" class="form-control" id="visite_technique_expiry_date"
                                        name="visite_technique_expiry_date" required>
                                    <div class="invalid-feedback" id="visite_technique_expiry_date-error"></div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h5 class="text-primary">Document Files</h5>
                            <p class="text-muted mb-3">Upload copies of your documents. Allowed formats: PDF, JPG, PNG
                            </p>

                            <!-- Document Files -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="file_carte_grise" class="form-label">Carte Grise Document</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="file_carte_grise"
                                            name="file_carte_grise" accept="application/pdf,image/*">
                                        <button type="button" class="btn btn-primary document-upload-btn"
                                            id="carte_grise_upload_btn">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="file_carte_grise-error"></div>
                                    <div class="document-actions mt-2">
                                        <a href="#" class="document-file-indicator d-none text-primary"
                                            id="carte_grise_file_indicator" target="_blank">
                                            <i class="fas fa-file-pdf me-1"></i> View Document
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger document-delete-btn"
                                            id="carte_grise_delete_btn">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="file_assurance" class="form-label">Insurance Document</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="file_assurance"
                                            name="file_assurance" accept="application/pdf,image/*">
                                        <button type="button" class="btn btn-primary document-upload-btn"
                                            id="assurance_upload_btn">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="file_assurance-error"></div>
                                    <div class="document-actions mt-2">
                                        <a href="#" class="document-file-indicator d-none text-primary"
                                            id="assurance_file_indicator" target="_blank">
                                            <i class="fas fa-file-pdf me-1"></i> View Document
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger document-delete-btn"
                                            id="assurance_delete_btn">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="file_vignette" class="form-label">Vignette Document</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="file_vignette" name="file_vignette"
                                            accept="application/pdf,image/*">
                                        <button type="button" class="btn btn-primary document-upload-btn"
                                            id="vignette_upload_btn">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="file_vignette-error"></div>
                                    <div class="document-actions mt-2">
                                        <a href="#" class="document-file-indicator d-none text-primary"
                                            id="vignette_file_indicator" target="_blank">
                                            <i class="fas fa-file-pdf me-1"></i> View Document
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger document-delete-btn"
                                            id="vignette_delete_btn">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="file_visite_technique" class="form-label">Technical Inspection
                                        Document</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="file_visite_technique"
                                            name="file_visite_technique" accept="application/pdf,image/*">
                                        <button type="button" class="btn btn-primary document-upload-btn"
                                            id="visite_technique_upload_btn">
                                            <i class="fas fa-upload"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="file_visite_technique-error"></div>
                                    <div class="document-actions mt-2">
                                        <a href="#" class="document-file-indicator d-none text-primary"
                                            id="visite_technique_file_indicator" target="_blank">
                                            <i class="fas fa-file-pdf me-1"></i> View Document
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger document-delete-btn"
                                            id="visite_technique_delete_btn">
                                            <i class="fas fa-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="button" class="btn btn-success" id="update_documents_btn">
                                    <i class="fas fa-sync me-2"></i> Update Documents
                                </button>
                                <small class="text-muted ms-2">Click to save document changes without saving the entire
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
                                        <input class="form-check-input" type="checkbox" id="feature_gps"
                                            name="features[]" value="gps">
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
                                <label for="main_image" class="form-label">Main Image</label>
                                <input type="file" class="form-control" id="main_image" name="main_image"
                                    accept="image/*">
                                <div class="invalid-feedback" id="main_image-error"></div>
                                <div id="main_image_preview" class="mt-2 d-none">
                                    <img src="" alt="Main Image Preview" class="img-fluid rounded"
                                        style="max-height: 200px;">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="additional_images" class="form-label">Additional Images</label>
                                <input type="file" class="form-control" id="additional_images" name="images[]"
                                    accept="image/*" multiple>
                                <div class="invalid-feedback" id="images-error"></div>
                                <small class="form-text text-muted">You can select multiple images</small>
                            </div>

                            <div id="gallery_container" class="d-none mt-3">
                                <h5 class="text-primary">Current Images</h5>
                                <div id="image_gallery" class="row g-3"></div>
                                <input type="hidden" id="removed_images" name="removed_images" value="">
                            </div>
                        </div>

                        <!-- SEO Tab -->
                        <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                </div>

                                <div class="col-12">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                </div>

                                <div class="col-12">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <small class="form-text text-muted">Separate keywords with commas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                    <p>Are you sure you want to delete this car? This action cannot be undone.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Custom styling based on the second style (from paste-2.txt) */

    /* Basic Styling */
    body {
        font-family: 'Open Sans', sans-serif;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border-radius: 0.5rem;
        transition: all 0.5s;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    /* Navigation Tabs */
    .nav-tabs .nav-link {
        color: var(--bs-dark);
        font-weight: 600;
        border: none;
        border-bottom: 3px solid transparent;
        transition: 0.5s;
    }

    .nav-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom: 3px solid var(--bs-primary);
        background: none;
    }

    .nav-tabs .nav-link:hover {
        border-color: transparent;
        border-bottom: 3px solid rgba(13, 110, 253, 0.5);
    }

    /* Buttons */
    .btn {
        font-weight: 600;
        transition: .5s;
        border-radius: 0.5rem;
    }

    .btn-primary {
        color: var(--bs-white);
    }

    .btn-primary:hover {
        background: var(--bs-secondary);
        border-color: var(--bs-secondary);
    }

    .btn-light {
        color: var(--bs-dark);
        background: #f8f9fa;
        border-color: #f8f9fa;
    }

    .btn-light:hover {
        color: var(--bs-primary);
        background: #e9ecef;
        border-color: #e9ecef;
    }

    .btn-sm-square {
        width: 34px;
        height: 34px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: normal;
    }

    /* Image thumbnails */
    .image-thumbnail {
        position: relative;
        margin-bottom: 15px;
    }

    .image-thumbnail .btn-remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        background: var(--bs-danger);
        color: white;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        line-height: 25px;
        text-align: center;
        padding: 0;
    }

    .image-thumbnail img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 0.5rem;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        transition: 0.5s;
    }

    .image-thumbnail:hover img {
        transform: scale(1.05);
    }

    /* Modal styling */
    .modal-content {
        border: none;
        border-radius: 0.75rem;
        overflow: hidden;
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
        text-decoration: none;
    }

    /* DataTable styling */
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        padding: 1rem;
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 700;
    }

    .table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    /* Status badges */
    .badge {
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 0.5rem;
    }

    .badge-success {
        background-color: #2dce89;
        color: white;
    }

    .badge-primary {
        background-color: #0d6efd;
        color: white;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-danger {
        background-color: #dc3545;
        color: white;
    }

    .badge-secondary {
        background-color: #6c757d;
        color: white;
    }

    /* Spinner */
    #spinner {
        opacity: 0;
        visibility: hidden;
        transition: opacity .5s ease-out, visibility 0s linear .5s;
        z-index: 99999;
    }

    #spinner.show {
        transition: opacity .5s ease-out, visibility 0s linear 0s;
        visibility: visible;
        opacity: 1;
    }

    /* Card-like panels */
    .bg-light {
        background-color: #f8f9fa !important;
    }

    .rounded {
        border-radius: 0.75rem !important;
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

    /* Improve table responsiveness */
    @media (max-width: 992px) {
        .modal-70 {
            max-width: 95%;
        }

        .table-responsive {
            overflow-x: auto;
        }
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
    const canEditCars = @json(auth() -> guard('admin') -> user() -> can('edit cars'));
    const canDeleteCars = @json(auth() -> guard('admin') -> user() -> can('delete cars'));
</script>

<!-- Include the JS for cars management -->
<script src="{{asset('admin/js/cars-management.js')}}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Spinner
        var spinner = function () {
            setTimeout(function () {
                if ($('#spinner').length > 0) {
                    $('#spinner').removeClass('show');
                }
            }, 1);
        };
        spinner();

        // Initialize CKEditor
        let editor;

        ClassicEditor
            .create(document.querySelector('#description'))
            .then(newEditor => {
                editor = newEditor;

                // Save CKEditor content to form when submitting
                document.getElementById('carForm').addEventListener('submit', function () {
                    document.getElementById('description').value = editor.getData();
                });
            })
            .catch(error => {
                console.error(error);
            });

        // Fix Bootstrap modal tab navigation
        document.querySelectorAll('#carTabs .nav-link').forEach(function (tabLink) {
            tabLink.addEventListener('click', function (e) {
                const targetId = this.getAttribute('data-bs-target');
                const tabPanes = document.querySelectorAll('#carTabsContent .tab-pane');
                const navLinks = document.querySelectorAll('#carTabs .nav-link');

                // Remove active class from all nav links and tab panes
                navLinks.forEach(link => link.classList.remove('active'));
                tabPanes.forEach(pane => {
                    pane.classList.remove('active', 'show');
                });

                // Add active class to current nav link and tab pane
                this.classList.add('active');
                document.querySelector(targetId).classList.add('active', 'show');
            });
        });

        // Modify the original handleEditCar function to set CKEditor content
        const originalHandleEditCar = window.handleEditCar;
        if (typeof originalHandleEditCar === 'function') {
            window.handleEditCar = function (carId) {
                originalHandleEditCar(carId);

                // Add additional code to set CKEditor content after modal is shown
                $('#carModal').on('shown.bs.modal', function () {
                    if (editor && document.getElementById('description')) {
                        const descriptionValue = document.getElementById('description').value;
                        editor.setData(descriptionValue);
                    }
                });
            };
        }

        // Configure SweetAlert to match our style
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Override the showAlert function
        if (typeof window.showAlert !== 'function') {
            window.showAlert = function (title, text, icon) {
                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    confirmButtonColor: '#0d6efd',
                    buttonsStyling: true,
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            };
        }
    });
</script>
@endpush --}}
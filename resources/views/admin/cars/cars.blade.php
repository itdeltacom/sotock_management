@extends('admin.layouts.master')

@section('title', 'Cars Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Car Management</h3>
        <div class="page-actions">
            @can('create cars')
                <button type="button" class="btn btn-primary" id="createCarBtn">
                    <i class="fas fa-plus"></i> Add New Car
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Cars</h5>
        </div>
        <div class="card-body">
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
                            @if(auth()->guard('admin')->user()->can('edit cars') || auth()->guard('admin')->user()->can('delete cars'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
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
                    <input type="hidden" name="car_id" id="car_id">
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="carTabs" role="tablist">
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
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Car Name</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback" id="name-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="brand_id" class="form-label">Brand</label>
                                        <select class="form-select" id="brand_id" name="brand_id" required>
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
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach(\App\Models\Category::where('is_active', true)->orderBy('name')->get() as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback" id="category_id-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="price_per_day" class="form-label">Price/Day ($)</label>
                                        <input type="number" class="form-control" id="price_per_day" name="price_per_day"
                                            min="0" step="0.01" required>
                                        <div class="invalid-feedback" id="price_per_day-error"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="discount_percentage" class="form-label">Discount (%)</label>
                                        <input type="number" class="form-control" id="discount_percentage"
                                            name="discount_percentage" min="0" max="100" step="0.01" value="0">
                                        <div class="invalid-feedback" id="discount_percentage-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="is_available" class="form-label">Availability</label>
                                        <select class="form-select" id="is_available" name="is_available">
                                            <option value="1">Available</option>
                                            <option value="0">Unavailable</option>
                                        </select>
                                        <div class="invalid-feedback" id="is_available-error"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control ckeditor" id="description" name="description"
                                        rows="6"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                </div>
                            </div>

                            <!-- Details Tab -->
                            <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label for="seats" class="form-label">Seats</label>
                                        <input type="number" class="form-control" id="seats" name="seats" min="1" max="50"
                                            value="5" required>
                                        <div class="invalid-feedback" id="seats-error"></div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="transmission" class="form-label">Transmission</label>
                                        <select class="form-select" id="transmission" name="transmission" required>
                                            <option value="automatic">Automatic</option>
                                            <option value="manual">Manual</option>
                                            <option value="semi-automatic">Semi-Automatic</option>
                                        </select>
                                        <div class="invalid-feedback" id="transmission-error"></div>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="fuel_type" class="form-label">Fuel Type</label>
                                        <select class="form-select" id="fuel_type" name="fuel_type" required>
                                            <option value="petrol">Petrol</option>
                                            <option value="diesel">Diesel</option>
                                            <option value="electric">Electric</option>
                                            <option value="hybrid">Hybrid</option>
                                            <option value="lpg">LPG</option>
                                        </select>
                                        <div class="invalid-feedback" id="fuel_type-error"></div>
                                    </div>

                                    <div class="col-md-3 mb-3">
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
                                </div>

                                <div class="mb-3">
                                    <label for="mileage" class="form-label">Mileage (km)</label>
                                    <input type="number" class="form-control" id="mileage" name="mileage" min="0">
                                    <div class="invalid-feedback" id="mileage-error"></div>
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
                                    <label for="main_image" class="form-label">Main Image</label>
                                    <input type="file" class="form-control" id="main_image" name="main_image"
                                        accept="image/*">
                                    <div class="invalid-feedback" id="main_image-error"></div>
                                    <div id="main_image_preview" class="mt-2 d-none">
                                        <img src="" alt="Main Image Preview" class="img-thumbnail"
                                            style="max-height: 200px;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="images" class="form-label">Additional Images</label>
                                    <input type="file" class="form-control" id="additional_images" name="images[]"
                                        accept="image/*" multiple>
                                    <div class="invalid-feedback" id="images-error"></div>
                                    <small class="form-text text-muted">You can select multiple images</small>
                                </div>

                                <div id="gallery_container" class="d-none mt-3">
                                    <h6>Current Images</h6>
                                    <div id="image_gallery" class="row g-3"></div>
                                    <input type="hidden" id="removed_images" name="removed_images" value="">
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <small class="form-text text-muted">Separate keywords with commas</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this car? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .image-thumbnail {
            position: relative;
            margin-bottom: 15px;
        }

        .image-thumbnail .btn-remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 255, 255, 0.7);
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
        }

        .ck-editor__editable_inline {
            min-height: 250px;
        }

        /* Improved modal styling with better scrolling */
        @media (min-width: 992px) {
            .modal-70 {
                max-width: 70%;
                width: 70%;
            }
        }

        /* Ensure modal fits well on smaller screens */
        @media (max-width: 991px) {
            .modal-70 {
                max-width: 90%;
                width: 90%;
            }
        }

        /* Fix for the scrolling issue */
        .modal-70 .modal-content {
            max-height: 85vh;
            /* Limit height to 85% of viewport height */
        }

        .modal-70 .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
            /* Adjust for header and footer */
            padding-bottom: 20px;
            /* Add some bottom padding for better scrolling */
        }

        /* Ensure tab content is properly contained */
        .modal-70 .tab-content {
            overflow: visible;
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
        });
    </script>
@endpush
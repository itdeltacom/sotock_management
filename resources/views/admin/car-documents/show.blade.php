@extends('admin.layouts.master')

@section('title', 'Car Documents')

@section('content')
    <div class="container-fluid py-4">
        <div class="page-header">
            <h3 class="page-title">Vehicle Documents: {{ $car->brand_name }} {{ $car->name }}</h3>
            <div class="page-actions">
                <a href="{{ route('admin.cars.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Cars
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between">
                            <h6>Documents for {{ $car->brand_name }} {{ $car->name }} ({{ $car->matricule }})</h6>
                            <button class="btn btn-sm btn-primary" id="updateAllDocumentsBtn">
                                <i class="fas fa-sync"></i> Update All Documents
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Car Basic Info -->
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Vehicle Info</h5>
                                        <div class="d-flex align-items-center mb-3">
                                            @if($car->main_image)
                                                <img src="{{ Storage::url($car->main_image) }}" alt="{{ $car->name }}"
                                                    class="me-3"
                                                    style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                                            @else
                                                <div class="bg-light text-center me-3"
                                                    style="width: 80px; height: 60px; line-height: 60px; border-radius: 4px;">
                                                    <i class="fas fa-car fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $car->brand_name }} {{ $car->name }}</h6>
                                                <p class="text-sm text-muted mb-0">{{ $car->matricule }}</p>
                                                <p class="text-sm text-muted mb-0">{{ $car->year }} ·
                                                    {{ ucfirst($car->fuel_type) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <p class="mb-1"><strong>Status:</strong> <span
                                                    class="badge bg-{{ $car->status == 'available' ? 'success' : ($car->status == 'maintenance' ? 'warning' : 'danger') }}">{{ ucfirst($car->status) }}</span>
                                            </p>
                                            <p class="mb-1"><strong>Category:</strong> {{ $car->category->name ?? 'N/A' }}
                                            </p>
                                            <p class="mb-1"><strong>Mise en Service:</strong>
                                                {{ $car->mise_en_service_date ? date('d/m/Y', strtotime($car->mise_en_service_date)) : 'N/A' }}
                                            </p>
                                            <p class="mb-0"><strong>Mileage:</strong> {{ number_format($car->mileage) }} km
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                    Document</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Number</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Expiry Date</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Status</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    File</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Insurance Document -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Insurance</h6>
                                                            <p class="text-xs text-secondary mb-0">
                                                                {{ $documents->assurance_company ?? 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->assurance_number ?? 'N/A' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->assurance_expiry_date ? date('d/m/Y', strtotime($documents->assurance_expiry_date)) : 'N/A' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = 'valid';
                                                        $daysLeft = null;
                                                        if ($documents && $documents->assurance_expiry_date) {
                                                            $today = \Carbon\Carbon::today();
                                                            $expiryDate = \Carbon\Carbon::parse($documents->assurance_expiry_date);
                                                            $daysLeft = $today->diffInDays($expiryDate, false);

                                                            if ($daysLeft < 0) {
                                                                $status = 'expired';
                                                            } elseif ($daysLeft <= 30) {
                                                                $status = 'expiring';
                                                            }
                                                        }
                                                    @endphp

                                                    @if($status === 'expired')
                                                        <span class="document-status-expired">Expired {{ abs($daysLeft) }} days
                                                            ago</span>
                                                    @elseif($status === 'expiring')
                                                        <span class="document-status-expires-soon">Expires in {{ $daysLeft }}
                                                            days</span>
                                                    @else
                                                        <span class="document-status-valid">Valid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($documents && $documents->file_assurance)
                                                        <a href="{{ Storage::url($documents->file_assurance) }}" target="_blank"
                                                            class="btn btn-link btn-sm text-secondary mb-0">
                                                            <i class="fas fa-file-pdf me-2"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-secondary">No file</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm update-document-btn"
                                                        data-doc-type="assurance" data-car-id="{{ $car->id }}"
                                                        data-doc-number="{{ $documents->assurance_number ?? '' }}"
                                                        data-doc-expiry-date="{{ $documents->assurance_expiry_date ? date('Y-m-d', strtotime($documents->assurance_expiry_date)) : '' }}"
                                                        data-doc-file-path="{{ $documents->file_assurance ?? '' }}">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Carte Grise Document -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Carte Grise</h6>
                                                            <p class="text-xs text-secondary mb-0">Vehicle Registration</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->carte_grise_number ?? 'N/A' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->carte_grise_expiry_date ? date('d/m/Y', strtotime($documents->carte_grise_expiry_date)) : 'N/A' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = 'valid';
                                                        $daysLeft = null;
                                                        if ($documents && $documents->carte_grise_expiry_date) {
                                                            $today = \Carbon\Carbon::today();
                                                            $expiryDate = \Carbon\Carbon::parse($documents->carte_grise_expiry_date);
                                                            $daysLeft = $today->diffInDays($expiryDate, false);

                                                            if ($daysLeft < 0) {
                                                                $status = 'expired';
                                                            } elseif ($daysLeft <= 30) {
                                                                $status = 'expiring';
                                                            }
                                                        }
                                                    @endphp

                                                    @if($status === 'expired')
                                                        <span class="document-status-expired">Expired {{ abs($daysLeft) }} days
                                                            ago</span>
                                                    @elseif($status === 'expiring')
                                                        <span class="document-status-expires-soon">Expires in {{ $daysLeft }}
                                                            days</span>
                                                    @else
                                                        <span class="document-status-valid">Valid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($documents && $documents->file_carte_grise)
                                                        <a href="{{ Storage::url($documents->file_carte_grise) }}"
                                                            target="_blank" class="btn btn-link btn-sm text-secondary mb-0">
                                                            <i class="fas fa-file-pdf me-2"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-secondary">No file</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm update-document-btn"
                                                        data-doc-type="carte_grise" data-car-id="{{ $car->id }}"
                                                        data-doc-number="{{ $documents->carte_grise_number ?? '' }}"
                                                        data-doc-expiry-date="{{ $documents->carte_grise_expiry_date ? date('Y-m-d', strtotime($documents->carte_grise_expiry_date)) : '' }}"
                                                        data-doc-file-path="{{ $documents->file_carte_grise ?? '' }}">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Vignette Document -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Vignette</h6>
                                                            <p class="text-xs text-secondary mb-0">Road Tax</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">-</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->vignette_expiry_date ? date('d/m/Y', strtotime($documents->vignette_expiry_date)) : 'N/A' }}
                                                    </p>
                                                </td>
                                                <td>
                                                    @php
                                                        $status = 'valid';
                                                        $daysLeft = null;
                                                        if ($documents && $documents->vignette_expiry_date) {
                                                            $today = \Carbon\Carbon::today();
                                                            $expiryDate = \Carbon\Carbon::parse($documents->vignette_expiry_date);
                                                            $daysLeft = $today->diffInDays($expiryDate, false);

                                                            if ($daysLeft < 0) {
                                                                $status = 'expired';
                                                            } elseif ($daysLeft <= 30) {
                                                                $status = 'expiring';
                                                            }
                                                        }
                                                    @endphp

                                                    @if($status === 'expired')
                                                        <span class="document-status-expired">Expired {{ abs($daysLeft) }} days
                                                            ago</span>
                                                    @elseif($status === 'expiring')
                                                        <span class="document-status-expires-soon">Expires in {{ $daysLeft }}
                                                            days</span>
                                                    @else
                                                        <span class="document-status-valid">Valid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($documents && $documents->file_vignette)
                                                        <a href="{{ Storage::url($documents->file_vignette) }}" target="_blank"
                                                            class="btn btn-link btn-sm text-secondary mb-0">
                                                            <i class="fas fa-file-pdf me-2"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-secondary">No file</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm update-document-btn"
                                                        data-doc-type="vignette" data-car-id="{{ $car->id }}"
                                                        data-doc-expiry-date="{{ $documents->vignette_expiry_date ? date('Y-m-d', strtotime($documents->vignette_expiry_date)) : '' }}"
                                                        data-doc-file-path="{{ $documents->file_vignette ?? '' }}">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Technical Inspection Document -->
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">Technical Inspection</h6>
                                                            <p class="text-xs text-secondary mb-0">Visite Technique</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">-</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">
                                                        {{ $documents->visite_technique_expiry_date ? date('d/m/Y', strtotime($documents->visite_technique_expiry_date)) : 'N/A' }}
                                                    </p>
                                                    @if($documents && $documents->visite_technique_date)
                                                        <p class="text-xs text-secondary mb-0">Last check:
                                                            {{ date('d/m/Y', strtotime($documents->visite_technique_date)) }}
                                                        </p>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $status = 'valid';
                                                        $daysLeft = null;
                                                        if ($documents && $documents->visite_technique_expiry_date) {
                                                            $today = \Carbon\Carbon::today();
                                                            $expiryDate = \Carbon\Carbon::parse($documents->visite_technique_expiry_date);
                                                            $daysLeft = $today->diffInDays($expiryDate, false);

                                                            if ($daysLeft < 0) {
                                                                $status = 'expired';
                                                            } elseif ($daysLeft <= 30) {
                                                                $status = 'expiring';
                                                            }
                                                        }
                                                    @endphp

                                                    @if($status === 'expired')
                                                        <span class="document-status-expired">Expired {{ abs($daysLeft) }} days
                                                            ago</span>
                                                    @elseif($status === 'expiring')
                                                        <span class="document-status-expires-soon">Expires in {{ $daysLeft }}
                                                            days</span>
                                                    @else
                                                        <span class="document-status-valid">Valid</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($documents && $documents->file_visite_technique)
                                                        <a href="{{ Storage::url($documents->file_visite_technique) }}"
                                                            target="_blank" class="btn btn-link btn-sm text-secondary mb-0">
                                                            <i class="fas fa-file-pdf me-2"></i> View
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-secondary">No file</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary btn-sm update-document-btn"
                                                        data-doc-type="visite_technique" data-car-id="{{ $car->id }}"
                                                        data-doc-expiry-date="{{ $documents->visite_technique_expiry_date ? date('Y-m-d', strtotime($documents->visite_technique_expiry_date)) : '' }}"
                                                        data-doc-file-path="{{ $documents->file_visite_technique ?? '' }}">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Update Modal -->
        <div class="modal fade" id="updateDocumentModal" tabindex="-1" aria-labelledby="updateDocumentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateDocumentModalLabel">Update Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="updateDocumentForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" value="POST">
                        <input type="hidden" name="car_id" id="doc_car_id">
                        <input type="hidden" name="document_type" id="doc_type">

                        <div class="modal-body">
                            <!-- Insurance specific fields -->
                            <div id="assurance_fields" class="document-specific-fields">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="assurance_number" class="form-label">Insurance Number</label>
                                        <input type="text" class="form-control" id="assurance_number"
                                            name="assurance_number" required>
                                        <div class="invalid-feedback" id="assurance_number-error"></div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="assurance_company" class="form-label">Insurance Company</label>
                                        <input type="text" class="form-control" id="assurance_company"
                                            name="assurance_company" required>
                                        <div class="invalid-feedback" id="assurance_company-error"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Carte Grise specific fields -->
                            <div id="carte_grise_fields" class="document-specific-fields">
                                <div class="mb-3">
                                    <label for="carte_grise_number" class="form-label">Carte Grise Number</label>
                                    <input type="text" class="form-control" id="carte_grise_number"
                                        name="carte_grise_number" required>
                                    <div class="invalid-feedback" id="carte_grise_number-error"></div>
                                </div>
                            </div>

                            <!-- Common fields for all document types -->
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                                <div class="invalid-feedback" id="expiry_date-error"></div>
                            </div>

                            <!-- Technical Inspection specific fields -->
                            <div id="visite_technique_fields" class="document-specific-fields">
                                <div class="mb-3">
                                    <label for="inspection_date" class="form-label">Inspection Date</label>
                                    <input type="date" class="form-control" id="inspection_date"
                                        name="visite_technique_date">
                                    <div class="invalid-feedback" id="inspection_date-error"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="document_file" class="form-label">Document File</label>
                                <input type="file" class="form-control" id="document_file" name="document_file"
                                    accept="application/pdf,image/*">
                                <div class="invalid-feedback" id="document_file-error"></div>
                                <div id="current_doc_file" class="mt-2">
                                    <span class="text-sm">Current file: </span>
                                    <a href="#" id="current_doc_link" target="_blank">View current document</a>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="updateDocBtn">Update Document</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* Expiring Documents Highlight Styles */
        .document-status-expired {
            background-color: #fee2e2;
            color: #ef4444;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 6px;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            animation: pulse 2s infinite;
        }

        .document-status-expires-soon {
            background-color: #fff7cd;
            color: #ff8800;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 6px;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            animation: pulse 2s infinite;
        }

        .document-status-valid {
            background-color: #d1fae5;
            color: #047857;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 6px;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        /* Highlight entire row for expiring documents */
        tr.expiring-document {
            background-color: #fff7ed;
        }

        tr.expiring-document td {
            border-left: 3px solid #ff8800;
        }

        tr.expired-document {
            background-color: #fef2f2;
        }

        tr.expired-document td {
            border-left: 3px solid #ef4444;
        }

        /* Pulse animation for attention */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            70% {
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Make document names stand out */
        .card-title {
            color: #1e293b;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Highlight the update buttons */
        .update-document-btn {
            transition: all 0.2s ease;
        }

        .update-document-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Make the "Update All" button more prominent */
        #updateAllDocumentsBtn {
            background-color: #f59e0b;
            border-color: #f59e0b;
            font-weight: 600;
        }

        #updateAllDocumentsBtn:hover {
            background-color: #d97706;
            border-color: #d97706;
        }

        /* Status badge enhancements */
        .badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 600;
        }

        /* File links styling */
        .btn-link.text-secondary {
            color: #4b5563 !important;
            text-decoration: none;
        }

        .btn-link.text-secondary:hover {
            color: #1e40af !important;
            text-decoration: underline;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Document routes
            const documentRoutes = {
                updateUrl: "{{ route('admin.cars.documents.update', $car->id) }}",
                showUrl: "{{ route('admin.cars.documents.show', $car->id) }}",
                uploadUrl: "{{ route('admin.cars.documents.upload', $car->id) }}",
            };

            // Show appropriate fields based on document type
            $('.update-document-btn').on('click', function () {
                const docType = $(this).data('doc-type');
                $('#doc_car_id').val($(this).data('car-id'));
                $('#doc_type').val(docType);

                // Reset form
                $('#updateDocumentForm')[0].reset();
                $('#updateDocumentForm .is-invalid').removeClass('is-invalid');

                // Hide all document specific fields first
                $('.document-specific-fields').hide();

                // Show fields specific to the document type
                $(`#${docType}_fields`).show();

                // Set common fields
                $('#expiry_date').val($(this).data('doc-expiry-date'));

                // Set document-specific fields
                if (docType === 'assurance') {
                    $('#assurance_number').val($(this).data('doc-number'));
                    $('#assurance_company').val('{{ $documents->assurance_company ?? "" }}');
                } else if (docType === 'carte_grise') {
                    $('#carte_grise_number').val($(this).data('doc-number'));
                } else if (docType === 'visite_technique') {
                    $('#inspection_date').val('{{ $documents->visite_technique_date ? date("Y-m-d", strtotime($documents->visite_technique_date)) : "" }}');
                }

                // Set document file link if exists
                const docFilePath = $(this).data('doc-file-path');
                if (docFilePath) {
                    $('#current_doc_file').show();
                    $('#current_doc_link').attr('href', `/storage/${docFilePath}`);
                } else {
                    $('#current_doc_file').hide();
                }

                // Set modal title
                const docTypeNames = {
                    'assurance': 'Insurance',
                    'carte_grise': 'Carte Grise',
                    'vignette': 'Vignette',
                    'visite_technique': 'Technical Inspection'
                };
                $('#updateDocumentModalLabel').text(`Update ${docTypeNames[docType] || docType}`);

                // Show modal
                $('#updateDocumentModal').modal('show');
            });

            // Form submission
            $('#updateDocumentForm').on('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const docType = $('#doc_type').val();

                // Add the correct field names based on document type
                if (docType === 'assurance') {
                    formData.append('assurance_expiry_date', $('#expiry_date').val());
                    formData.append('file_assurance', $('#document_file')[0].files[0] || null);
                } else if (docType === 'carte_grise') {
                    formData.append('carte_grise_expiry_date', $('#expiry_date').val());
                    formData.append('file_carte_grise', $('#document_file')[0].files[0] || null);
                } else if (docType === 'vignette') {
                    formData.append('vignette_expiry_date', $('#expiry_date').val());
                    formData.append('file_vignette', $('#document_file')[0].files[0] || null);
                } else if (docType === 'visite_technique') {
                    formData.append('visite_technique_expiry_date', $('#expiry_date').val());
                    formData.append('file_visite_technique', $('#document_file')[0].files[0] || null);
                }

                // Show loading state
                const submitBtn = $('#updateDocBtn');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

                // Submit form
                $.ajax({
                    url: documentRoutes.updateUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#updateDocumentModal').modal('hide');
                            Swal.fire({
                                title: 'Success',
                                text: response.message || 'Document updated successfully',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                            }).then(() => {
                                // Reload page to show updated documents
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to update document',
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
                            });
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'An error occurred while updating the document';

                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON?.errors || {};

                            // Display validation errors
                            for (const field in errors) {
                                const input = $(`#${field}`);
                                if (input.length) {
                                    input.addClass('is-invalid');
                                    $(`#${field}-error`).text(errors[field][0]);
                                }
                            }

                            errorMessage = 'Please check the form for errors';
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#3085d6',
                        });
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text('Update Document');
                    }
                });
            });

            // Update all documents button
            $('#updateAllDocumentsBtn').on('click', function () {
                Swal.fire({
                    title: 'Update all documents?',
                    text: 'This will open the car edit form where you can update all documents at once',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, edit car'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('admin.cars.index') }}";
                    }
                });
            });
        });
    </script>
@endpush
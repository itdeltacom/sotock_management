@extends('admin.layouts.master')

@section('title', 'Expiring Documents')

@section('content')
    <div class="container-fluid py-4">
        <div class="page-header">
            <h3 class="page-title">Expiring Vehicle Documents</h3>
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
                        <h6>Documents expiring within 30 days</h6>
                        <p class="text-sm text-muted">
                            This table shows all vehicles with documents that will expire soon or have already expired.
                            Make sure to update these documents to avoid legal issues.
                        </p>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="expiring-documents-table">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Vehicle</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Matricule</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Documents</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="doc_number" class="form-label">Document Number</label>
                                    <input type="text" class="form-control" id="doc_number" name="document_number" required>
                                    <div class="invalid-feedback" id="doc_number-error"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="doc_expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="doc_expiry_date" name="expiry_date"
                                        required>
                                    <div class="invalid-feedback" id="doc_expiry_date-error"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="doc_file" class="form-label">Document File</label>
                                <input type="file" class="form-control" id="doc_file" name="document_file"
                                    accept="application/pdf,image/*">
                                <div class="invalid-feedback" id="doc_file-error"></div>
                                <div id="current_doc_file" class="mt-2 d-none">
                                    <span class="text-sm">Current file: </span>
                                    <a href="#" id="current_doc_link" target="_blank">View current document</a>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="doc_notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="doc_notes" name="notes" rows="3"></textarea>
                                <div class="invalid-feedback" id="doc_notes-error"></div>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Expiring Documents Page Styles - Argon-inspired */

        /* Card Styling */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h6 {
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #344767;
        }

        .card-header p.text-sm {
            color: #8392AB;
            font-size: 0.75rem;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            color: #8392AB;
            border-bottom: 1px solid #E9ECEF;
        }

        .table td {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

        /* Document Status Badges */
        .document-status-expired {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
            color: white;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 0.5rem;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .document-status-expires-soon {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
            color: white;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 0.5rem;
            display: inline-block;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Form Inputs */
        .form-control {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .btn-secondary {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border: none;
        }

        /* Modal Styling */
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

        /* DataTables Styling */
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

        /* Loading Overlay */
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

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .page-header h3 {
            margin-bottom: 0;
            font-size: 1.25rem;
            color: #344767;
        }

        .page-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
    </style>
@endpush


@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Document routes
        const documentRoutes = {
            dataUrl: "{{ route('admin.cars.documents.expiring.data') }}",
            updateUrl: "{{ route('admin.cars.documents.update', ':id') }}",
            viewUrl: "{{ route('admin.cars.documents.show', ':id') }}",
            carUrl: "{{ route('admin.cars.index') }}",
        };

        $(document).ready(function () {
            // Initialize DataTable
            const table = $('#expiring-documents-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: documentRoutes.dataUrl,
                },
                columns: [
                    {
                        data: 'car_details',
                        name: 'car_details'
                    },
                    {
                        data: 'car.matricule',
                        name: 'car.matricule',
                        defaultContent: 'N/A'
                    },
                    {
                        data: 'expiring_documents',
                        name: 'expiring_documents',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        render: function (data, type, row) {
                            // Check if any document has already expired
                            if (row.expiring_documents.includes('Expired')) {
                                return '<span class="document-status-expired">Expired</span>';
                            }
                            return '<span class="document-status-expires-soon">Expiring soon</span>';
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[0, 'asc']],
                pageLength: 25,
            });

            // Update document modal
            $(document).on('click', '.update-document-btn', function () {
                const carId = $(this).data('car-id');
                const docType = $(this).data('doc-type');
                const docNumber = $(this).data('doc-number');
                const docExpiryDate = $(this).data('doc-expiry-date');
                const docFilePath = $(this).data('doc-file-path');

                $('#doc_car_id').val(carId);
                $('#doc_type').val(docType);
                $('#doc_number').val(docNumber);
                $('#doc_expiry_date').val(docExpiryDate);

                if (docFilePath) {
                    $('#current_doc_file').removeClass('d-none');
                    $('#current_doc_link').attr('href', `/storage/${docFilePath}`);
                } else {
                    $('#current_doc_file').addClass('d-none');
                }

                $('#updateDocumentModalLabel').text(`Update ${getDocumentTypeName(docType)}`);
                $('#updateDocumentModal').modal('show');
            });

            // Handle document update submission
            $('#updateDocumentForm').on('submit', function (e) {
                e.preventDefault();

                const carId = $('#doc_car_id').val();
                const formData = new FormData(this);

                // Set loading state
                const submitBtn = $('#updateDocBtn');
                const originalBtnText = submitBtn.text();
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

                $.ajax({
                    url: documentRoutes.updateUrl.replace(':id', carId),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            $('#updateDocumentModal').modal('hide');
                            Swal.fire({
                                title: 'Success',
                                text: response.message || 'Document updated successfully.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                            });
                            table.ajax.reload();
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to update document.',
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error('Error updating document:', xhr);

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
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON?.message || 'An error occurred while updating the document.',
                                icon: 'error',
                                confirmButtonColor: '#3085d6',
                            });
                        }
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).text(originalBtnText);
                    }
                });
            });

            // Helper function to get document type name
            function getDocumentTypeName(docType) {
                const docTypes = {
                    'carte_grise': 'Carte Grise',
                    'assurance': 'Insurance',
                    'visite_technique': 'Technical Inspection',
                    'vignette': 'Vignette'
                };

                return docTypes[docType] || docType;
            }
        });
    </script>
@endpush
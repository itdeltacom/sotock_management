@extends('admin.layouts.master')

@section('title', 'Edit Newsletter')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Edit Newsletter</h3>
        <div class="page-actions">
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Newsletters
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Newsletter Details</h5>
        </div>
        <div class="card-body">
            <form id="newsletterForm" method="POST" action="{{ route('admin.newsletters.update', $newsletter->id) }}"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject"
                                value="{{ $newsletter->subject }}" required>
                            <div class="invalid-feedback" id="subject-error"></div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea id="content" name="content"
                                class="form-control">{!! $newsletter->content !!}</textarea>
                            <div class="invalid-feedback" id="content-error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="attachment" class="form-label">Attachment</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">Max file size: 10MB</div>
                            <div class="invalid-feedback" id="attachment-error"></div>

                            @if($newsletter->attachment)
                                <div class="mt-2">
                                    <p>Current attachment: <a href="{{ Storage::url($newsletter->attachment) }}"
                                            target="_blank">{{ basename($newsletter->attachment) }}</a></p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="schedule_for" class="form-label">Schedule For</label>
                            <input type="datetime-local" class="form-control" id="schedule_for" name="schedule_for"
                                value="{{ $newsletter->scheduled_for ? $newsletter->scheduled_for->format('Y-m-d\TH:i') : '' }}">
                            <div class="form-text">Leave empty to save as draft</div>
                            <div class="invalid-feedback" id="schedule_for-error"></div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_now" name="send_now" value="1">
                            <label class="form-check-label" for="send_now">
                                Send immediately after saving
                            </label>
                        </div>
                    </div>

                    <div class="col-md-12 mt-4">
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="saveBtn">Update Newsletter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* CKEditor minimum height */
        .ck-editor__editable {
            min-height: 300px !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize CKEditor
            let editor;
            ClassicEditor
                .create(document.querySelector('#content'), {
                    toolbar: [
                        'heading', '|',
                        'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', 'mediaEmbed', '|',
                        'undo', 'redo'
                    ]
                })
                .then(newEditor => {
                    editor = newEditor;
                })
                .catch(error => {
                    console.error(error);
                });

            // Form submission
            $('#newsletterForm').on('submit', function (e) {
                e.preventDefault();

                // Create form data
                const formData = new FormData(this);

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        // Clear previous errors
                        $('.is-invalid').removeClass('is-invalid');
                        $('.invalid-feedback').text('');

                        // Disable submit button
                        $('#saveBtn').prop('disabled', true)
                            .html('<i class="fas fa-spinner fa-spin"></i> Updating...');
                    },
                    success: function (response) {
                        // Show success message
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        // Redirect to newsletters index
                        if (response.redirect) {
                            setTimeout(function () {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;

                            // Display errors
                            $.each(errors, function (field, messages) {
                                const errorField = $('#' + field);
                                errorField.addClass('is-invalid');
                                $('#' + field + '-error').text(messages[0]);
                            });
                        } else {
                            // Generic error
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to update newsletter. Please try again.',
                                icon: 'error',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    complete: function () {
                        // Re-enable submit button
                        $('#saveBtn').prop('disabled', false)
                            .text('Update Newsletter');
                    }
                });
            });
        });
    </script>
@endpush
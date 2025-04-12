@extends('admin.layouts.master')

@section('title', 'Create Newsletter')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Create Newsletter</h3>
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
            <form id="newsletterForm" method="POST" action="{{ route('admin.newsletters.store') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                            <div class="invalid-feedback" id="subject-error"></div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="mewsletter_content" class="form-label">Content <span
                                    class="text-danger">*</span></label>
                            <textarea id="mewsletter_content" name="content" class="form-control"></textarea>
                            <div class="invalid-feedback" id="mewsletter_content-error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="attachment" class="form-label">Attachment</label>
                            <input type="file" class="form-control" id="attachment" name="attachment">
                            <div class="form-text">Max file size: 10MB</div>
                            <div class="invalid-feedback" id="attachment-error"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="schedule_for" class="form-label">Schedule For</label>
                            <input type="datetime-local" class="form-control" id="schedule_for" name="schedule_for">
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
                            <button type="submit" class="btn btn-primary" id="saveBtn">Save Newsletter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -0.75rem;
            margin-left: -0.75rem;
            width: 100%;
        }

        .col-md-12 {
            flex: 0 0 100%;
            max-width: 100%;
            width: 100%;
            position: relative;
            padding-right: 0.75rem;
            padding-left: 0.75rem;
        }

        .ck-editor {
            width: 100% !important;
            max-width: 100% !important;
        }

        .ck-editor__editable {
            width: 100% !important;
            min-height: 300px !important;
            z-index: 1;
            opacity: 1 !important;
            background-color: #fff !important;
        }

        /* Force proper form layout */
        #newsletterForm {
            width: 100%;
            display: block;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        'use strict';

        document.addEventListener('DOMContentLoaded', function () {
            // Cache DOM elements
            const newsletterForm = document.getElementById('newsletterForm');
            const saveBtn = document.getElementById('saveBtn');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('mewsletter_content');
            let editor; // Will hold CKEditor instance

            // Initialize components
            initCKEditor();
            initAttachmentHandling();
            initDateTimePicker();
            initSendNowHandler();

            // Set up form submission
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', handleFormSubmit);
            }

            /**
             * Initialize CKEditor
             */
            function initCKEditor() {
                if (typeof ClassicEditor !== 'undefined' && document.querySelector('#mewsletter_content')) {
                    ClassicEditor
                        .create(document.querySelector('#mewsletter_content'), {
                            toolbar: [
                                'heading', '|',
                                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'blockQuote', 'insertTable', 'mediaEmbed', '|',
                                'undo', 'redo'
                            ],
                            simpleUpload: {
                                uploadUrl: '/admin/newsletters/upload-image',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                }
                            }
                        })
                        .then(newEditor => {
                            editor = newEditor;

                            // Set min-height for better UX
                            const editorElement = document.querySelector('.ck-editor__editable');
                            if (editorElement) {
                                editorElement.style.minHeight = '300px';
                            }
                        })
                        .catch(error => {
                            console.error('Error initializing CKEditor:', error);
                        });
                }
            }

            /**
             * Initialize attachment handling
             */
            function initAttachmentHandling() {
                const attachmentInput = document.getElementById('attachment');
                if (!attachmentInput) return;

                // Setup existing attachment preview if editing
                const currentAttachment = document.getElementById('current-attachment');
                if (currentAttachment) {
                    const attachmentUrl = currentAttachment.dataset.url;
                    const previewDiv = document.createElement('div');
                    previewDiv.id = 'attachment-preview';
                    previewDiv.className = 'mt-2';
                    previewDiv.innerHTML = `
                                <a href="${attachmentUrl}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-paperclip"></i> View Current Attachment
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="remove-attachment">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            `;
                    attachmentInput.parentNode.appendChild(previewDiv);

                    // Setup remove button event
                    const removeBtn = document.getElementById('remove-attachment');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function () {
                            if (confirm('Are you sure you want to remove the current attachment?')) {
                                // Add hidden input to indicate attachment removal
                                if (!document.getElementById('remove_attachment')) {
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = 'remove_attachment';
                                    hiddenInput.id = 'remove_attachment';
                                    hiddenInput.value = '1';
                                    newsletterForm.appendChild(hiddenInput);
                                }
                                // Remove preview
                                const preview = document.getElementById('attachment-preview');
                                if (preview) preview.remove();
                                // Clear file input
                                attachmentInput.value = '';
                            }
                        });
                    }
                }

                // Handle new attachment preview
                attachmentInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (!file) {
                        const preview = document.getElementById('attachment-preview');
                        if (preview && !document.getElementById('current-attachment')) {
                            preview.remove();
                        }
                        return;
                    }

                    const fileType = file.type;
                    const validImageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                    // Remove old preview if exists
                    const oldPreview = document.getElementById('attachment-preview');
                    if (oldPreview) oldPreview.remove();

                    // Create new preview
                    const previewDiv = document.createElement('div');
                    previewDiv.id = 'attachment-preview';
                    previewDiv.className = 'mt-2';

                    if (validImageTypes.includes(fileType)) {
                        // Image preview
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            previewDiv.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">`;
                            attachmentInput.parentNode.appendChild(previewDiv);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        // Generic file preview
                        previewDiv.innerHTML = `<span class="badge bg-primary">${file.name}</span>`;
                        attachmentInput.parentNode.appendChild(previewDiv);
                    }
                });
            }

            /**
             * Initialize datetime picker if available
             */
            function initDateTimePicker() {
                const schedulePicker = document.getElementById('schedule_for');
                if (!schedulePicker) return;

                if (typeof $ !== 'undefined' && $.fn.datetimepicker) {
                    $('#schedule_for').datetimepicker({
                        format: 'YYYY-MM-DD HH:mm',
                        icons: {
                            time: 'fas fa-clock',
                            date: 'fas fa-calendar',
                            up: 'fas fa-chevron-up',
                            down: 'fas fa-chevron-down',
                            previous: 'fas fa-chevron-left',
                            next: 'fas fa-chevron-right',
                            today: 'fas fa-calendar-check',
                            clear: 'fas fa-trash',
                            close: 'fas fa-times'
                        },
                        minDate: moment().add(5, 'minutes'),
                        useCurrent: false
                    });
                }
            }

            /**
             * Initialize send now checkbox handler
             */
            function initSendNowHandler() {
                const sendNowCheckbox = document.getElementById('send_now');
                const scheduleInput = document.getElementById('schedule_for');
                if (!sendNowCheckbox || !scheduleInput) return;

                sendNowCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        scheduleInput.disabled = true;
                        scheduleInput.value = '';
                    } else {
                        scheduleInput.disabled = false;
                    }
                });
            }

            /**
             * Handle form submission
             */
            function handleFormSubmit(e) {
                e.preventDefault();

                // Reset validation errors
                clearValidationErrors();

                // Get form data
                const formData = new FormData(e.target);

                // Add CKEditor content if editor is initialized
                if (editor) {
                    formData.set('content', editor.getData());
                }

                // Check if this is an edit form by looking for hidden input with name _method
                const isEdit = formData.has('_method') && formData.get('_method') === 'PUT';

                // Add method override for edit forms if not already present
                if (document.querySelector('input[name="_method"][value="PUT"]') && !formData.has('_method')) {
                    formData.append('_method', 'PUT');
                }

                // Show loading state
                if (saveBtn) {
                    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    saveBtn.disabled = true;
                }

                // Send request
                fetch(newsletterForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
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
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message || 'Newsletter saved successfully',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } else {
                                alert(data.message || 'Newsletter saved successfully');
                            }

                            // Redirect if provided
                            if (data.redirect) {
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 1000);
                            }
                        } else {
                            throw new Error(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);

                        // Handle validation errors
                        if (error.status === 422 && error.data && error.data.errors) {
                            displayValidationErrors(error.data.errors);

                            // Show error notification
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validation Error',
                                    text: 'Please check the form for errors',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000
                                });
                            } else {
                                alert('Please check the form for errors');
                            }
                        } else {
                            // Show general error notification
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.data?.message || 'An error occurred while saving the newsletter',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 5000
                                });
                            } else {
                                alert(error.data?.message || 'An error occurred while saving the newsletter');
                            }
                        }
                    })
                    .finally(() => {
                        // Reset button state
                        if (saveBtn) {
                            saveBtn.innerHTML = isEdit ? 'Update Newsletter' : 'Save Newsletter';
                            saveBtn.disabled = false;
                        }
                    });
            }

            /**
             * Clear validation errors
             */
            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });

                document.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });
            }

            /**
             * Display validation errors
             */
            function displayValidationErrors(errors) {
                clearValidationErrors();

                Object.keys(errors).forEach(field => {
                    const input = document.getElementById(field);
                    const errorEl = document.getElementById(`${field}-error`);

                    if (input) {
                        input.classList.add('is-invalid');
                    }

                    if (errorEl && errors[field][0]) {
                        errorEl.textContent = errors[field][0];
                    }
                });
            }
        });

        // Add CSS styles to ensure proper display of CKEditor
        (function () {
            // Only add these styles if they don't already exist
            if (!document.getElementById('newsletter-editor-styles')) {
                const styleEl = document.createElement('style');
                styleEl.id = 'newsletter-editor-styles';
                styleEl.innerHTML = `
                            /* CKEditor display */
                            .ck-editor__editable {
                                min-height: 300px !important;
                                z-index: 1;
                                opacity: 1 !important;
                                background-color: #fff !important;
                            }

                            .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
                                border-color: #ced4da !important;
                            }

                            /* Form elements spacing */
                            .form-group,
                            .mb-3 {
                                margin-bottom: 1.5rem;
                            }

                            /* Attachment preview */
                            #attachment-preview img {
                                max-height: 200px;
                                width: auto;
                                border: 1px solid #dee2e6;
                                border-radius: 0.25rem;
                            }
                        `;

                document.head.appendChild(styleEl);
            }
        })();
    </script>
@endpush
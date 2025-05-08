/**
 * Testimonial Management JavaScript
 */
$(function () {
    "use strict";

    // Initialize DataTable
    let dataTable = $('#testimonials-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            data: function (d) {
                d.status = $('#statusFilter').val();
                d.rating = $('#ratingFilter').val();
                d.is_featured = $('#featuredFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'image', name: 'image', orderable: false, searchable: false },
            { data: 'user_name', name: 'user_name' },
            { data: 'user_title', name: 'user_title' },
            { data: 'rating_stars', name: 'rating', orderable: true, searchable: false },
            { data: 'status', name: 'is_approved', orderable: true, searchable: false },
            { data: 'featured', name: 'is_featured', orderable: true, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            paginate: {
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        drawCallback: function() {
            // Reattach event handlers after each redraw
            attachEventHandlers();
        }
    });

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });

    // Reset filter
    $('#resetFilterBtn').on('click', function() {
        $('#filterForm')[0].reset();
        dataTable.draw();
    });

    // Create Testimonial Button
    $('#createTestimonialBtn').on('click', function() {
        resetForm();
        $('#testimonialModalLabel').text('Add New Testimonial');
        $('#testimonialModal').modal('show');
    });

    // Save Button (Create or Update)
    $('#testimonialForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form and prepare data
        const form = $(this);
        const formData = new FormData(form[0]);
        
        // Extract boolean values from checkboxes
        formData.set('is_approved', $('#is_approved').is(':checked') ? 'true' : 'false');
        formData.set('is_featured', $('#is_featured').is(':checked') ? 'true' : 'false');
        
        // Determine if this is a create or update operation
        const testimonialId = $('#testimonial_id').val();
        let url = testimonialId ? routes.updateUrl.replace(':id', testimonialId) : routes.storeUrl;
        let method = testimonialId ? 'POST' : 'POST';
        
        // Add _method field for PUT if updating
        if (testimonialId) {
            formData.append('_method', 'PUT');
        }
        
        // Submit the form via AJAX
        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                // Clear previous errors
                $('.invalid-feedback').hide();
                $('.is-invalid').removeClass('is-invalid');
                
                // Disable the submit button
                $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(response) {
                // Display success message
                toastr.success(response.message);
                
                // Close the modal
                $('#testimonialModal').modal('hide');
                
                // Refresh the DataTable
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    
                    // Display validation errors
                    $.each(errors, function(field, messages) {
                        const errorSpan = $('#' + field + '-error');
                        errorSpan.text(messages[0]);
                        errorSpan.show();
                        $('[name="' + field + '"]').addClass('is-invalid');
                    });
                } else {
                    // Display generic error message
                    toastr.error('An error occurred. Please try again.');
                }
            },
            complete: function() {
                // Re-enable the submit button
                $('#saveBtn').prop('disabled', false).text('Save');
            }
        });
    });

    // Attach event handlers for dynamic elements
    function attachEventHandlers() {
        // Edit button
        $('.btn-edit').off('click').on('click', function() {
            const testimonialId = $(this).data('id');
            editTestimonial(testimonialId);
        });
        
        // Delete button
        $('.btn-delete').off('click').on('click', function() {
            const testimonialId = $(this).data('id');
            const testimonialName = $(this).data('name');
            confirmDelete(testimonialId, testimonialName);
        });
        
        // Toggle featured button
        $('.btn-feature').off('click').on('click', function() {
            const testimonialId = $(this).data('id');
            toggleFeatured(testimonialId);
        });
        
        // Toggle approval button
        $('.btn-approve').off('click').on('click', function() {
            const testimonialId = $(this).data('id');
            toggleApproval(testimonialId);
        });
    }

    // Reset form to initial state
    function resetForm() {
        $('#testimonialForm')[0].reset();
        $('#testimonial_id').val('');
        $('.invalid-feedback').hide();
        $('.is-invalid').removeClass('is-invalid');
        $('#image-preview').addClass('d-none');
        $('#rating5').prop('checked', true); // Default to 5 stars
        $('#is_approved').prop('checked', true); // Default to approved
        $('#is_featured').prop('checked', false); // Default to not featured
    }

    // Load testimonial data for editing
    function editTestimonial(testimonialId) {
        $.ajax({
            url: routes.editUrl.replace(':id', testimonialId),
            method: 'GET',
            beforeSend: function() {
                resetForm();
                $('#testimonialModalLabel').text('Edit Testimonial');
            },
            success: function(response) {
                const testimonial = response.testimonial;
                
                // Populate form fields
                $('#testimonial_id').val(testimonial.id);
                $('#user_name').val(testimonial.user_name);
                $('#user_title').val(testimonial.user_title);
                $('#user_email').val(testimonial.user_email);
                $('#content').val(testimonial.content);
                $('#order').val(testimonial.order);
                
                // Set rating
                $(`#rating${testimonial.rating}`).prop('checked', true);
                
                // Set checkboxes
                $('#is_approved').prop('checked', testimonial.is_approved);
                $('#is_featured').prop('checked', testimonial.is_featured);
                
                // Show image preview if exists
                if (testimonial.image_url) {
                    $('#image-preview').removeClass('d-none');
                    $('#image-preview img').attr('src', testimonial.image_url);
                }
                
                // Show the modal
                $('#testimonialModal').modal('show');
            },
            error: function() {
                toastr.error('Failed to load testimonial data.');
            }
        });
    }

    // Toggle featured status
    function toggleFeatured(testimonialId) {
        $.ajax({
            url: routes.toggleFeaturedUrl.replace(':id', testimonialId),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                dataTable.ajax.reload(null, false);
            },
            error: function() {
                toastr.error('Failed to update featured status.');
            }
        });
    }

    // Toggle approval status
    function toggleApproval(testimonialId) {
        $.ajax({
            url: routes.toggleApprovalUrl.replace(':id', testimonialId),
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                dataTable.ajax.reload(null, false);
            },
            error: function() {
                toastr.error('Failed to update approval status.');
            }
        });
    }

    // Confirm deletion
    function confirmDelete(testimonialId, testimonialName) {
        $('#delete-testimonial-name').text(testimonialName);
        
        $('#confirmDeleteBtn').off('click').on('click', function() {
            deleteTestimonial(testimonialId);
        });
        
        $('#deleteModal').modal('show');
    }

    // Delete testimonial
    function deleteTestimonial(testimonialId) {
        $.ajax({
            url: routes.deleteUrl.replace(':id', testimonialId),
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#confirmDeleteBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                toastr.success(response.message);
                dataTable.ajax.reload();
            },
            error: function() {
                toastr.error('Failed to delete testimonial.');
            },
            complete: function() {
                $('#confirmDeleteBtn').prop('disabled', false).text('Delete');
            }
        });
    }

    // Preview uploaded image
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').removeClass('d-none');
                $('#image-preview img').attr('src', e.target.result);
            }
            reader.readAsDataURL(file);
        } else {
            $('#image-preview').addClass('d-none');
        }
    });
});
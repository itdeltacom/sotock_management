'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const reviewsTable = document.getElementById('reviews-table');
    const reviewForm = document.getElementById('reviewForm');
    const reviewModal = document.getElementById('reviewModal');
    const createReviewBtn = document.getElementById('createReviewBtn');
    const saveBtn = document.getElementById('saveBtn');
    const carSelect = document.getElementById('car_id');
    const userSelect = document.getElementById('user_id');
    const guestInfoDiv = document.getElementById('guest-info');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let table; // Will hold DataTable instance

    // Initialize DataTable
    initDataTable();

    // Initialize Select2 for user selection
    initSelect2();

    // Set up event listeners
    if (createReviewBtn) {
        createReviewBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('reviewModalLabel').textContent = 'Add New Review';
            showModal();
        });
    }

    if (reviewForm) {
        reviewForm.addEventListener('submit', handleFormSubmit);
    }

    // Toggle guest info visibility based on user selection
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            toggleGuestInfo();
        });
    }

    // Filter form
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (table) table.ajax.reload();
        });
    }

    // Reset filters button
    const resetFilterBtn = document.getElementById('resetFilterBtn');
    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function() {
            document.querySelectorAll('#filterForm select').forEach(select => {
                select.value = '';
            });
            if (table) table.ajax.reload();
        });
    }

    // Event delegation for action buttons
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const reviewId = button.getAttribute('data-id');
            if (!canEditReviews) {
                Swal.fire('Permission Denied', 'You do not have permission to edit reviews.', 'warning');
                return;
            }
            if (reviewId) handleEditReview(reviewId);
        }

        // Delete button
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const reviewId = button.getAttribute('data-id');
            if (!canDeleteReviews) {
                Swal.fire('Permission Denied', 'You do not have permission to delete reviews.', 'warning');
                return;
            }
            if (reviewId) {
                document.getElementById('confirmDeleteBtn').setAttribute('data-id', reviewId);
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            }
        }

        // Toggle approval button
        if (e.target.closest('.btn-toggle-approval')) {
            const button = e.target.closest('.btn-toggle-approval');
            const reviewId = button.getAttribute('data-id');
            if (!canEditReviews) {
                Swal.fire('Permission Denied', 'You do not have permission to change review approval status.', 'warning');
                return;
            }
            if (reviewId) toggleApprovalStatus(reviewId, button);
        }
    });

    // Delete confirmation button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const reviewId = this.getAttribute('data-id');
            deleteReview(reviewId);
        });
    }

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        if (reviewsTable && typeof $.fn !== 'undefined' && $.fn.DataTable) {
            table = $('#reviews-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.dataUrl,
                    type: 'GET',
                    data: function (d) {
                        d.car_id = $('#carFilter').val();
                        d.approval_status = $('#approvalFilter').val();
                        d.rating = $('#ratingFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'car_name', name: 'car_name' },
                    { data: 'reviewer', name: 'reviewer' },
                    { data: 'star_rating', name: 'rating', orderable: true },
                    { 
                        data: 'comment', 
                        name: 'comment',
                        render: function(data) {
                            return '<div class="comment-cell" title="' + escapeHtml(data) + '">' + escapeHtml(data) + '</div>';
                        }
                    },
                    { data: 'approval_status', name: 'is_approved' },
                    { data: 'created_date', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true
            });
        }
    }

    /**
     * Initialize Select2 for user selection with AJAX loading
     */
    function initSelect2() {
        if (typeof $.fn !== 'undefined' && $.fn.select2 && userSelect) {
            $(userSelect).select2({
                theme: 'bootstrap-5',
                placeholder: 'Search for user...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#reviewModal'),
                ajax: {
                    url: routes.getUsersUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name + ' (' + item.email + ')',
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });

            // Event when a user is selected
            $(userSelect).on('change', function() {
                toggleGuestInfo();
            });
        }
    }

    /**
     * Toggle guest info visibility based on user selection
     */
    function toggleGuestInfo() {
        const userId = userSelect.value;
        
        if (userId) {
            // A user is selected, hide guest info
            if (guestInfoDiv) {
                guestInfoDiv.style.display = 'none';
                
                // Remove required validation from guest fields
                document.querySelectorAll('#guest-info input').forEach(input => {
                    input.removeAttribute('required');
                });
            }
        } else {
            // No user selected, show guest info
            if (guestInfoDiv) {
                guestInfoDiv.style.display = 'block';
                
                // Add required validation to guest fields
                document.querySelectorAll('#guest-info input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            }
        }
    }

    /**
     * Show modal
     */
    function showModal() {
        const modal = new bootstrap.Modal(reviewModal);
        modal.show();
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
        
        // Determine if this is an edit or create operation
        const reviewId = document.getElementById('review_id').value;
        const isEdit = reviewId && reviewId !== '';
        
        // Ensure is_approved is included properly
        const isApproved = document.getElementById('is_approved').checked;
        formData.set('is_approved', isApproved ? 1 : 0);
        
        // For PUT requests, Laravel needs _method
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', reviewId) : routes.storeUrl;
        
        // Show loading state
        if (saveBtn) {
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            saveBtn.disabled = true;
        }
        
        // Send request
        fetch(url, {
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
                // Hide modal
                const modal = bootstrap.Modal.getInstance(reviewModal);
                if (modal) modal.hide();
                
                // Reload DataTable
                if (table) table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Review saved successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                throw new Error(data.message || 'An error occurred while saving the review');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Handle validation errors
            if (error.status === 422 && error.data && error.data.errors) {
                displayValidationErrors(error.data.errors);
                
                // Show error notification
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
                // Show general error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.data?.message || 'An error occurred while saving the review',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            }
        })
        .finally(() => {
            // Reset button state
            if (saveBtn) {
                saveBtn.innerHTML = 'Save';
                saveBtn.disabled = false;
            }
        });
    }

    /**
     * Handle edit review
     */
    function handleEditReview(reviewId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('review_id').value = reviewId;
        document.getElementById('reviewModalLabel').textContent = 'Edit Review';
        
        // Show loading overlay
        const modalBody = reviewModal.querySelector('.modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-overlay';
            loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
            loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 1050;';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            modalBody.style.position = 'relative';
            modalBody.appendChild(loadingDiv);
        }
        
        // Show modal
        showModal();
        
        // Fetch review data
        fetch(routes.editUrl.replace(':id', reviewId), {
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
            
            if (data.success && data.review) {
                const review = data.review;
                
                // Set form values
                document.getElementById('car_id').value = review.car_id || '';
                document.getElementById('comment').value = review.comment || '';
                
                // Set rating
                const ratingRadio = document.getElementById(`rating-${Math.round(review.rating)}`);
                if (ratingRadio) ratingRadio.checked = true;
                
                // Set approved checkbox
                document.getElementById('is_approved').checked = review.is_approved;
                
                // Handle user/guest info
                if (review.user_id) {
                    // This is a user review
                    const newOption = new Option(
                        review.user ? `${review.user.name} (${review.user.email})` : 'Unknown User', 
                        review.user_id, 
                        true, 
                        true
                    );
                    
                    if (typeof $.fn !== 'undefined' && $.fn.select2) {
                        $(userSelect).empty().append(newOption).trigger('change');
                    } else {
                        userSelect.innerHTML = '';
                        userSelect.appendChild(newOption);
                        userSelect.value = review.user_id;
                    }
                    
                    // Hide guest info fields
                    if (guestInfoDiv) {
                        guestInfoDiv.style.display = 'none';
                    }
                } else {
                    // This is a guest review
                    if (typeof $.fn !== 'undefined' && $.fn.select2) {
                        $(userSelect).val(null).trigger('change');
                    } else {
                        userSelect.value = '';
                    }
                    
                    // Show and fill guest info fields
                    if (guestInfoDiv) {
                        guestInfoDiv.style.display = 'block';
                        document.getElementById('reviewer_name').value = review.reviewer_name || '';
                        document.getElementById('reviewer_email').value = review.reviewer_email || '';
                    }
                }
                
                // Check if user can edit car_id
                if (!review.user_id) {
                    // Guest review, enable car selection
                    carSelect.disabled = false;
                } else {
                    // User review, disable car selection to maintain integrity
                    carSelect.disabled = true;
                }
                
                toggleGuestInfo();
            } else {
                // Hide modal on error
                const modal = bootstrap.Modal.getInstance(reviewModal);
                if (modal) modal.hide();
                
                Swal.fire('Error', 'Failed to load review data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Remove loading overlay
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
            
            // Hide modal on error
            const modal = bootstrap.Modal.getInstance(reviewModal);
            if (modal) modal.hide();
            
            Swal.fire('Error', 'Failed to load review data', 'error');
        });
    }
    
    /**
     * Toggle approval status
     */
    function toggleApprovalStatus(reviewId, button) {
        fetch(routes.toggleApprovalUrl.replace(':id', reviewId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                // Update button
                if (data.is_approved) {
                    button.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                    button.setAttribute('title', 'Disapprove');
                } else {
                    button.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                    button.setAttribute('title', 'Approve');
                }
                
                // Reload table
                if (table) table.ajax.reload(null, false);
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to update approval status',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        });
    }
    
    /**
     * Delete review
     */
    function deleteReview(reviewId) {
        fetch(routes.deleteUrl.replace(':id', reviewId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide delete modal
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                if (deleteModal) deleteModal.hide();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Review deleted successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                // Reload table
                if (table) table.ajax.reload();
            } else {
                throw new Error(data.message || 'An error occurred');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Hide delete modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            if (deleteModal) deleteModal.hide();
            
            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to delete review',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
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
    
    /**
     * Reset form
     */
    function resetForm() {
        if (reviewForm) {
            reviewForm.reset();
        }
        
        document.getElementById('review_id').value = '';
        
        // Reset rating
        document.querySelectorAll('input[name="rating"]').forEach(radio => {
            radio.checked = false;
        });
        
        // Reset Select2 user field
        if (typeof $.fn !== 'undefined' && $.fn.select2 && userSelect) {
            $(userSelect).val(null).trigger('change');
        }
        
        // Show guest info fields
        if (guestInfoDiv) {
            guestInfoDiv.style.display = 'block';
        }
        
        // Enable car selection
        if (carSelect) {
            carSelect.disabled = false;
        }
        
        // Clear validation errors
        clearValidationErrors();
    }
    
    /**
     * Helper function to escape HTML for safety
     */
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});

// Add CSS styles for review modal to ensure proper display
(function() {
    // Only add these styles if they don't already exist
    if (!document.getElementById('review-modal-styles')) {
        const styleEl = document.createElement('style');
        styleEl.id = 'review-modal-styles';
        styleEl.innerHTML = `
            /* Comment cell styling */
            .comment-cell {
                max-width: 300px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            /* Star rating styling additions */
            .star-rating {
                margin-top: 0.5rem;
            }
            
            .star-rating label {
                transition: all 0.2s ease;
            }
            
            .star-rating label:hover {
                transform: scale(1.2);
            }
            
            /* Form validation feedback */
            .invalid-feedback {
                display: none;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            
            .was-validated .form-control:invalid,
            .form-control.is-invalid {
                border-color: #dc3545;
                padding-right: calc(1.5em + 0.75rem);
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            
            .was-validated .form-control:invalid:focus,
            .form-control.is-invalid:focus {
                border-color: #dc3545;
                box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
            }
            
            .was-validated .form-control:invalid ~ .invalid-feedback,
            .form-control.is-invalid ~ .invalid-feedback,
            .was-validated .form-select:invalid ~ .invalid-feedback,
            .form-select.is-invalid ~ .invalid-feedback {
                display: block;
            }
            
            /* Loading overlay */
            #loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(255, 255, 255, 0.8);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1050;
            }
        `;
        
        document.head.appendChild(styleEl);
    }
})();
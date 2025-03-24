'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const commentsTable = document.getElementById('comments-table');
    const commentForm = document.getElementById('commentForm');
    const commentModal = document.getElementById('commentModal');
    const viewCommentModal = document.getElementById('viewCommentModal');
    const createCommentBtn = document.getElementById('createCommentBtn');
    const saveBtn = document.getElementById('saveBtn');
    const filterForm = document.getElementById('filterForm');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const postIdSelect = document.getElementById('post_id');
    const parentIdSelect = document.getElementById('parent_id');
    const viewEditBtn = document.getElementById('viewEditBtn');
    const commentAsAdminCheckbox = document.getElementById('comment_as_admin');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Store current filters
    let currentFilters = {
        post_id: '',
        status: '',
        parent: ''
    };
    
    // Initialize DataTable
    let table = new DataTable('#comments-table', {
        processing: true,
        serverSide: true,
        ajax: {
            url: routes.dataUrl,
            type: 'GET',
            data: function (d) {
                d.post_id = currentFilters.post_id;
                d.status = currentFilters.status;
                d.parent = currentFilters.parent;
                return d;
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type', name: 'parent_id' },
            { data: 'author', name: 'name' },
            { 
                data: 'content_excerpt', 
                name: 'content',
                className: 'content-cell'
            },
            { data: 'post_title', name: 'post.title' },
            { data: 'status', name: 'is_approved' },
            { data: 'date', name: 'created_at' },
            { data: 'approval_actions', name: 'approval_actions', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Set up event listeners
    if (createCommentBtn) {
        createCommentBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('commentModalLabel').textContent = 'Add New Comment';
            
            // Show modal using Bootstrap 5 modal
            const bsModal = new bootstrap.Modal(commentModal);
            bsModal.show();
        });
    }
    
    if (commentForm) {
        commentForm.addEventListener('submit', handleFormSubmit);
    }
    
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
    
    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener('click', function() {
            document.getElementById('filter_post').value = '';
            document.getElementById('filter_status').value = '';
            document.getElementById('filter_type').value = '';
            applyFilters();
        });
    }
    
    if (postIdSelect) {
        postIdSelect.addEventListener('change', function() {
            loadCommentsForPost(this.value);
        });
    }
    
    if (commentAsAdminCheckbox) {
        commentAsAdminCheckbox.addEventListener('change', function() {
            if (this.checked) {
                document.getElementById('name').value = adminUser.name;
                document.getElementById('email').value = adminUser.email;
                
                // Disable fields if using admin info
                document.getElementById('name').readOnly = true;
                document.getElementById('email').readOnly = true;
            } else {
                // Only clear and enable if editing a new comment
                if (!document.getElementById('comment_id').value) {
                    document.getElementById('name').value = '';
                    document.getElementById('email').value = '';
                }
                
                // Enable fields when not using admin info
                document.getElementById('name').readOnly = false;
                document.getElementById('email').readOnly = false;
            }
        });
    }
    
    if (viewEditBtn) {
        viewEditBtn.addEventListener('click', function() {
            const commentId = this.getAttribute('data-id');
            if (commentId) {
                // Close view modal
                const viewModalInstance = bootstrap.Modal.getInstance(viewCommentModal);
                if (viewModalInstance) viewModalInstance.hide();
                
                // Open edit modal with a small delay to avoid visual glitches
                setTimeout(() => {
                    handleEditComment(commentId);
                }, 300);
            }
        });
    }
    
    // Handle action buttons with event delegation
    document.addEventListener('click', function(e) {
        // View button
        if (e.target.closest('.btn-view')) {
            const button = e.target.closest('.btn-view');
            const commentId = button.getAttribute('data-id');
            if (commentId) handleViewComment(commentId);
        }
        
        // Edit button
        if (e.target.closest('.btn-edit')) {
            // Check permission
            if (typeof canEditComments !== 'undefined' && !canEditComments) {
                Swal.fire('Permission Denied', 'You do not have permission to edit comments.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-edit');
            const commentId = button.getAttribute('data-id');
            if (commentId) handleEditComment(commentId);
        }
        
        // Delete button
        if (e.target.closest('.btn-delete')) {
            // Check permission
            if (typeof canDeleteComments !== 'undefined' && !canDeleteComments) {
                Swal.fire('Permission Denied', 'You do not have permission to delete comments.', 'warning');
                return;
            }
            
            const button = e.target.closest('.btn-delete');
            const commentId = button.getAttribute('data-id');
            const commentName = button.getAttribute('data-name') || 'this comment';
            if (commentId) handleDeleteComment(commentId, commentName);
        }
        
        // Approve button
        if (e.target.closest('.btn-approve')) {
            const button = e.target.closest('.btn-approve');
            const commentId = button.getAttribute('data-id');
            if (commentId) handleApproveComment(commentId);
        }
        
        // Reject button
        if (e.target.closest('.btn-reject')) {
            const button = e.target.closest('.btn-reject');
            const commentId = button.getAttribute('data-id');
            if (commentId) handleRejectComment(commentId);
        }
    });
    
    /**
     * Apply filters to the DataTable
     */
    function applyFilters() {
        currentFilters = {
            post_id: document.getElementById('filter_post').value,
            status: document.getElementById('filter_status').value,
            parent: document.getElementById('filter_type').value
        };
        
        table.ajax.reload();
    }
    
    /**
     * Load comments for a specific post (for parent dropdown)
     */
    function loadCommentsForPost(postId) {
        // Clear the dropdown
        parentIdSelect.innerHTML = '<option value="">None (New Comment)</option>';
        
        if (!postId) return;
        
        fetch(routes.getCommentsUrl.replace(':id', postId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.comments.length > 0) {
                data.comments.forEach(comment => {
                    const option = document.createElement('option');
                    option.value = comment.id;
                    option.textContent = `${comment.name}: ${comment.content_excerpt}`;
                    parentIdSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
        });
    }
    
    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        
        // Reset validation UI
        clearValidationErrors();
        
        // Get form data
        const formData = new FormData(e.target);
        
        // Add extra fields based on admin checkbox
        if (document.getElementById('comment_as_admin').checked) {
            formData.append('comment_as_admin', '1');
        }
        
        // Get comment ID and determine if this is an edit operation
        const commentId = document.getElementById('comment_id').value;
        const isEdit = commentId && commentId !== '';
        
        // For PUT requests, Laravel doesn't process FormData the same way as POST
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', commentId) : routes.storeUrl;
        
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
                const bsModal = bootstrap.Modal.getInstance(commentModal);
                if (bsModal) bsModal.hide();
                
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Comment saved successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
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
                
                // Show notification
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
                // Show error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.data?.message || 'An error occurred',
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
     * Handle view comment
     */
    function handleViewComment(commentId) {
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(viewCommentModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#viewCommentModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'view-loading-overlay';
            loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
            loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 10;';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            // Add loading overlay
            modalBody.style.position = 'relative';
            modalBody.appendChild(loadingDiv);
        }
        
        // Fetch comment data
        fetch(routes.showUrl.replace(':id', commentId), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading overlay
            const overlay = document.getElementById('view-loading-overlay');
            if (overlay) overlay.remove();
            
            if (data.success && data.comment) {
                const comment = data.comment;
                
                // Set author info
                document.getElementById('view-author').textContent = comment.name;
                document.getElementById('view-email').textContent = comment.email;
                
                // Set avatar (using Gravatar)
                const emailHash = md5(comment.email.toLowerCase().trim());
                document.getElementById('view-avatar').src = `https://www.gravatar.com/avatar/${emailHash}?s=50&d=mp`;
                
                // Set website if available
                const websiteEl = document.getElementById('view-website');
                if (comment.website) {
                    websiteEl.innerHTML = `<a href="${comment.website}" target="_blank">${comment.website}</a>`;
                    websiteEl.classList.remove('d-none');
                } else {
                    websiteEl.innerHTML = '';
                    websiteEl.classList.add('d-none');
                }
                
                // Set content
                document.getElementById('view-content').innerHTML = comment.content;
                
                // Set metadata
                document.getElementById('view-date').textContent = new Date(comment.created_at).toLocaleString();
                document.getElementById('view-ip').textContent = comment.ip_address || 'N/A';
                document.getElementById('view-user-agent').textContent = comment.user_agent || 'N/A';
                
                // Set post
                if (comment.post) {
                    document.getElementById('view-post').innerHTML = `Post: <a href="#" target="_blank">${comment.post.title}</a>`;
                } else {
                    document.getElementById('view-post').textContent = 'Post: Unknown';
                }
                
                // Set type badge
                const typeEl = document.getElementById('view-type');
                if (comment.parent_id) {
                    typeEl.textContent = 'Reply';
                    typeEl.className = 'badge bg-secondary me-2';
                } else {
                    typeEl.textContent = 'Comment';
                    typeEl.className = 'badge bg-primary me-2';
                }
                
                // Set status badge
                const statusEl = document.getElementById('view-status');
                if (comment.is_approved) {
                    statusEl.textContent = 'Approved';
                    statusEl.className = 'badge bg-success me-2';
                } else {
                    statusEl.textContent = 'Pending';
                    statusEl.className = 'badge bg-warning me-2';
                }
                
                // Handle parent comment if this is a reply
                const parentContainer = document.getElementById('view-parent-container');
                if (comment.parent) {
                    const parent = comment.parent;
                    
                    // Set parent info
                    document.getElementById('view-parent-author').textContent = parent.name;
                    document.getElementById('view-parent-content').innerHTML = parent.content;
                    document.getElementById('view-parent-date').textContent = new Date(parent.created_at).toLocaleString();
                    
                    // Set parent avatar
                    const parentEmailHash = md5(parent.email.toLowerCase().trim());
                    document.getElementById('view-parent-avatar').src = `https://www.gravatar.com/avatar/${parentEmailHash}?s=40&d=mp`;
                    
                    // Show parent container
                    parentContainer.classList.remove('d-none');
                } else {
                    // Hide parent container
                    parentContainer.classList.add('d-none');
                }
                
                // Set approval buttons
                const approvalButtons = document.getElementById('view-approval-buttons');
                if (canEditComments) {
                    if (comment.is_approved) {
                        approvalButtons.innerHTML = `
                            <button type="button" class="btn btn-warning btn-sm btn-reject" data-id="${comment.id}">
                                <i class="fas fa-ban"></i> Reject
                            </button>
                        `;
                    } else {
                        approvalButtons.innerHTML = `
                            <button type="button" class="btn btn-success btn-sm btn-approve" data-id="${comment.id}">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        `;
                    }
                } else {
                    approvalButtons.innerHTML = '';
                }
                
                // Set edit button data attribute
                viewEditBtn.setAttribute('data-id', comment.id);
                
                // Show/hide edit button based on permissions
                if (canEditComments) {
                    viewEditBtn.classList.remove('d-none');
                } else {
                    viewEditBtn.classList.add('d-none');
                }
            } else {
                const bsModal = bootstrap.Modal.getInstance(viewCommentModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Error', 'Failed to load comment data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(viewCommentModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load comment data', 'error');
        });
    }
    
    /**
     * Handle edit comment
     */
    function handleEditComment(commentId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('comment_id').value = commentId;
        document.getElementById('commentModalLabel').textContent = 'Edit Comment';
        
        // Show modal with loading overlay
        const bsModal = new bootstrap.Modal(commentModal);
        bsModal.show();
        
        const modalBody = document.querySelector('#commentModal .modal-body');
        if (modalBody) {
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-overlay';
            loadingDiv.className = 'position-absolute bg-white d-flex justify-content-center align-items-center';
            loadingDiv.style.cssText = 'left: 0; top: 0; right: 0; bottom: 0; z-index: 10;';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary"></div>';
            
            // Add loading overlay
            modalBody.style.position = 'relative';
            modalBody.appendChild(loadingDiv);
        }
        
        // Fetch comment data
        fetch(routes.showUrl.replace(':id', commentId), {
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
            
            if (data.success && data.comment) {
                const comment = data.comment;
                
                // Set form fields
                document.getElementById('post_id').value = comment.post_id || '';
                document.getElementById('parent_id').value = comment.parent_id || '';
                document.getElementById('name').value = comment.name || '';
                document.getElementById('email').value = comment.email || '';
                document.getElementById('website').value = comment.website || '';
                document.getElementById('content').value = comment.content || '';
                document.getElementById('is_approved').value = comment.is_approved ? '1' : '0';
                
                // Load comments for post to populate parent dropdown
                loadCommentsForPost(comment.post_id);
                
                // Determine if this was posted by admin
                const isAdminComment = (comment.name === adminUser.name && comment.email === adminUser.email);
                document.getElementById('comment_as_admin').checked = isAdminComment;
                
                // Set readonly attributes for name/email if admin comment
                document.getElementById('name').readOnly = isAdminComment;
                document.getElementById('email').readOnly = isAdminComment;
            } else {
                const bsModal = bootstrap.Modal.getInstance(commentModal);
                if (bsModal) bsModal.hide();
                Swal.fire('Error', 'Failed to load comment data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const bsModal = bootstrap.Modal.getInstance(commentModal);
            if (bsModal) bsModal.hide();
            Swal.fire('Error', 'Failed to load comment data', 'error');
        });
    }
    
    /**
     * Handle delete comment
     */
    function handleDeleteComment(commentId, commentName) {
        Swal.fire({
            title: 'Confirm Delete',
            html: `Are you sure you want to delete this comment?<br><br>
                  <span class="text-danger font-weight-bold">This action cannot be undone! Any replies to this comment will also be deleted.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Delete',
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Deleting...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch(routes.destroyUrl.replace(':id', commentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        table.ajax.reload();
                        Swal.fire('Deleted!', data.message || 'Comment deleted successfully', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to delete comment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', error.message || 'Failed to delete comment', 'error');
                });
            }
        });
    }
    
    /**
     * Handle approve comment
     */
    function handleApproveComment(commentId) {
        fetch(routes.approveUrl.replace(':id', commentId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Comment approved successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                // If viewing the comment, close the modal to refresh data
                const viewModalInstance = bootstrap.Modal.getInstance(viewCommentModal);
                if (viewModalInstance) viewModalInstance.hide();
            } else {
                throw new Error(data.message || 'Failed to approve comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Failed to approve comment', 'error');
        });
    }
    
    /**
     * Handle reject comment
     */
    function handleRejectComment(commentId) {
        fetch(routes.rejectUrl.replace(':id', commentId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload table
                table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Comment rejected successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                
                // If viewing the comment, close the modal to refresh data
                const viewModalInstance = bootstrap.Modal.getInstance(viewCommentModal);
                if (viewModalInstance) viewModalInstance.hide();
            } else {
                throw new Error(data.message || 'Failed to reject comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', error.message || 'Failed to reject comment', 'error');
        });
    }
    
    /**
     * Clear validation errors
     */
    function clearValidationErrors() {
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
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
            
            if (input) input.classList.add('is-invalid');
            
            if (errorEl && errors[field][0]) {
                errorEl.textContent = errors[field][0];
                errorEl.style.display = 'block';
            }
        });
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (commentForm) {
            commentForm.reset();
            document.getElementById('comment_id').value = '';
        }
        
        // Reset parent comments dropdown
        parentIdSelect.innerHTML = '<option value="">None (New Comment)</option>';
        
        // Reset admin checkbox and field states
        document.getElementById('comment_as_admin').checked = false;
        document.getElementById('name').readOnly = false;
        document.getElementById('email').readOnly = false;
        
        clearValidationErrors();
    }
    
    /**
     * MD5 function for Gravatar URLs
     */
    function md5(string) {
        // This is a simple placeholder MD5 implementation for the demo
        // In production, use a proper MD5 library or pre-compute the hashes
        return string ? string.split('').reduce((a, b) => {
            a = ((a << 5) - a) + b.charCodeAt(0);
            return a & a;
        }, 0).toString(16) : '';
    }
});
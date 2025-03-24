'use strict';

document.addEventListener('DOMContentLoaded', function () {
    // Cache DOM elements
    const blogsTable = document.getElementById('blogs-table');
    const blogForm = document.getElementById('blogForm');
    const blogModal = document.getElementById('blogModal');
    const createBlogBtn = document.getElementById('createBlogBtn');
    const saveBtn = document.getElementById('saveBtn');
    const generateSlugBtn = document.getElementById('generateSlugBtn');
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let editor; // Will hold CKEditor instance
    let table; // Will hold DataTable instance

    // Initialize DataTable
    initDataTable();

    // Set up event listeners
    if (createBlogBtn) {
        createBlogBtn.addEventListener('click', function() {
            resetForm();
            document.getElementById('blogModalLabel').textContent = 'Add New Blog Post';
            showModal();
        });
    }

    if (blogForm) {
        blogForm.addEventListener('submit', handleFormSubmit);
    }

    if (titleInput) {
        titleInput.addEventListener('blur', function() {
            // Auto-generate slug when title loses focus and slug is empty
            if (slugInput && !slugInput.value.trim()) {
                generateSlug();
            }
        });
    }

    if (generateSlugBtn) {
        generateSlugBtn.addEventListener('click', generateSlug);
    }

    if (slugInput) {
        slugInput.addEventListener('input', validateSlug);
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

    // Handle file input preview for images
    const featuredImageInput = document.getElementById('featured_image');
    if (featuredImageInput) {
        featuredImageInput.addEventListener('change', function() {
            handleImagePreview(this, 'featured-image-preview');
        });
    }

    const socialImageInput = document.getElementById('social_image');
    if (socialImageInput) {
        socialImageInput.addEventListener('change', function() {
            handleImagePreview(this, 'social-image-preview');
        });
    }

    // Event delegation for action buttons
    document.addEventListener('click', function(e) {
        // Edit button
        if (e.target.closest('.btn-edit')) {
            const button = e.target.closest('.btn-edit');
            const postId = button.getAttribute('data-id');
            if (!canEditBlogPosts) {
                Swal.fire('Permission Denied', 'You do not have permission to edit blog posts.', 'warning');
                return;
            }
            if (postId) handleEditPost(postId);
        }

        // Delete button
        if (e.target.closest('.btn-delete')) {
            const button = e.target.closest('.btn-delete');
            const postId = button.getAttribute('data-id');
            const postTitle = button.getAttribute('data-title');
            if (!canDeleteBlogPosts) {
                Swal.fire('Permission Denied', 'You do not have permission to delete blog posts.', 'warning');
                return;
            }
            if (postId) {
                document.getElementById('delete-post-title').textContent = postTitle;
                document.getElementById('confirmDeleteBtn').setAttribute('data-id', postId);
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            }
        }

        // Feature/unfeature button
        if (e.target.closest('.btn-feature')) {
            const button = e.target.closest('.btn-feature');
            const postId = button.getAttribute('data-id');
            if (!canEditBlogPosts) {
                Swal.fire('Permission Denied', 'You do not have permission to feature/unfeature blog posts.', 'warning');
                return;
            }
            if (postId) toggleFeatured(postId, button);
        }

        // Publish/unpublish button
        if (e.target.closest('.btn-publish')) {
            const button = e.target.closest('.btn-publish');
            const postId = button.getAttribute('data-id');
            if (!canEditBlogPosts) {
                Swal.fire('Permission Denied', 'You do not have permission to publish/unpublish blog posts.', 'warning');
                return;
            }
            if (postId) togglePublished(postId, button);
        }

        // Tab navigation
        if (e.target.closest('#blogTabs .nav-link')) {
            e.preventDefault();
            const tabLink = e.target.closest('#blogTabs .nav-link');
            activateTab(tabLink.getAttribute('data-bs-target')?.substring(1));
        }
    });

    // Delete confirmation button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-id');
            deletePost(postId);
        });
    }

    // Setup social media preview updates
    setupSocialPreviews();

    /**
     * Initialize CKEditor
     */
    function initCKEditor() {
        if (typeof ClassicEditor !== 'undefined' && document.querySelector('#editor')) {
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'],
                    simpleUpload: {
                        uploadUrl: routes.uploadImageUrl,
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
     * Initialize Select2 for tags
     */
    function initSelect2() {
        try {
            if (typeof $.fn !== 'undefined' && $.fn.select2) {
                $('#tags').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select tags',
                    width: '100%',
                    dropdownParent: $('#blogModal')
                });
            }
        } catch (error) {
            console.error('Error initializing Select2:', error);
        }
    }

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        if (blogsTable && typeof $.fn !== 'undefined' && $.fn.DataTable) {
            table = $('#blogs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: routes.dataUrl,
                    type: 'GET',
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                        d.category_id = $('#categoryFilter').val();
                        d.is_featured = $('#featuredFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'image', name: 'image', orderable: false, searchable: false },
                    { data: 'title', name: 'title' },
                    { data: 'category_name', name: 'category_name' },
                    { data: 'author_name', name: 'author_name' },
                    { data: 'status', name: 'is_published' },
                    { data: 'published_date', name: 'published_at' },
                    { data: 'comment_count', name: 'comment_count', searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                responsive: true
            });
        }
    }

    /**
     * Show modal and initialize components
     */
    function showModal() {
        // Show modal using Bootstrap
        const modal = new bootstrap.Modal(blogModal);
        modal.show();
        
        // Once modal is shown, initialize components
        blogModal.addEventListener('shown.bs.modal', modalShownHandler, { once: true });
    }

    /**
     * Handle modal shown event
     */
    function modalShownHandler() {
        // Initialize CKEditor if not already initialized
        if (!editor) {
            initCKEditor();
        }
        
        // Initialize Select2
        initSelect2();
        
        // Fix tab issues
        fixTabDisplay();
        
        // Update social previews
        updateSocialPreviews();
    }

    /**
     * Fix tab display issues
     */
    function fixTabDisplay() {
        // First, activate basic-info tab
        activateTab('basic-info');
        
        // Fix the CKEditor visibility
        setTimeout(() => {
            if (editor) {
                try {
                    editor.ui.update();
                } catch (error) {
                    console.log('Failed to update editor UI:', error);
                    
                    // Alternative approach
                    const editorEl = document.querySelector('.ck-editor__editable');
                    if (editorEl) {
                        editorEl.style.display = 'block';
                        editorEl.style.visibility = 'visible';
                        editorEl.style.opacity = 1;
                    }
                }
            }
        }, 100);
    }

    /**
     * Activate a specific tab
     */
    function activateTab(tabId) {
        // Deactivate all tabs
        document.querySelectorAll('#blogTabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
        });
        
        document.querySelectorAll('#blogTabContent .tab-pane').forEach(pane => {
            pane.classList.remove('active', 'show');
            pane.style.display = 'none';
        });
        
        // Activate the selected tab
        const tabLink = document.querySelector(`#blogTabs .nav-link[data-bs-target="#${tabId}"]`);
        if (tabLink) {
            tabLink.classList.add('active');
            tabLink.setAttribute('aria-selected', 'true');
        }
        
        const tabPane = document.getElementById(tabId);
        if (tabPane) {
            tabPane.classList.add('active', 'show');
            tabPane.style.display = 'block';
            
            // If basic-info tab, refresh CKEditor
            if (tabId === 'basic-info' && editor) {
                setTimeout(() => {
                    try {
                        editor.ui.update();
                    } catch (error) {
                        console.log('Failed to update editor UI:', error);
                    }
                }, 50);
            }
        }
    }

    /**
     * Setup social media preview functionality
     */
    function setupSocialPreviews() {
        const inputs = [
            document.getElementById('title'),
            document.getElementById('meta_title'),
            document.getElementById('meta_description'),
            document.getElementById('facebook_description'),
            document.getElementById('twitter_description')
        ];
        
        // Add input event listeners to each field
        inputs.forEach(input => {
            if (input) {
                input.addEventListener('input', updateSocialPreviews);
            }
        });
    }

    /**
     * Update social preview content
     */
    function updateSocialPreviews() {
        const title = document.getElementById('meta_title')?.value || document.getElementById('title')?.value || '';
        const metaDesc = document.getElementById('meta_description')?.value || '';
        const fbDesc = document.getElementById('facebook_description')?.value || metaDesc;
        const twDesc = document.getElementById('twitter_description')?.value || metaDesc;
        
        const fbTitle = document.getElementById('fb-title');
        const fbDescription = document.getElementById('fb-description');
        const twTitle = document.getElementById('tw-title');
        const twDescription = document.getElementById('tw-description');
        
        if (fbTitle) fbTitle.textContent = title;
        if (fbDescription) fbDescription.textContent = fbDesc;
        if (twTitle) twTitle.textContent = title;
        if (twDescription) twDescription.textContent = twDesc;
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
        
        // Properly handle boolean values for checkboxes
        // Add checkbox values explicitly as booleans
        formData.set('is_published', document.getElementById('is_published').checked ? 'true' : 'false');
        formData.set('is_featured', document.getElementById('is_featured').checked ? 'true' : 'false');
        formData.set('allow_comments', document.getElementById('allow_comments').checked ? 'true' : 'false');
        
        // Determine if this is an edit or create operation
        const postId = document.getElementById('post_id').value;
        const isEdit = postId && postId !== '';
        
        // For PUT requests, Laravel needs _method
        if (isEdit) {
            formData.append('_method', 'PUT');
        }
        
        // Set up request URL
        const url = isEdit ? routes.updateUrl.replace(':id', postId) : routes.storeUrl;
        
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
                const modal = bootstrap.Modal.getInstance(blogModal);
                if (modal) modal.hide();
                
                // Reload DataTable
                if (table) table.ajax.reload();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Blog post saved successfully',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                throw new Error(data.message || 'An error occurred while saving the blog post');
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
                    text: error.data?.message || 'An error occurred while saving the blog post',
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
     * Generate slug from title
     */
    function generateSlug() {
        const title = document.getElementById('title').value;
        
        if (!title) {
            const slugMessageEl = document.getElementById('slug-message');
            if (slugMessageEl) {
                slugMessageEl.innerHTML = '<span class="text-danger">Please enter a title first</span>';
            }
            return;
        }
        
        fetch(routes.generateSlugUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ title: title })
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('slug').value = data.slug;
            validateSlug();
        })
        .catch(error => {
            console.error('Error generating slug:', error);
        });
    }

    /**
     * Validate slug
     */
    function validateSlug() {
        const slug = document.getElementById('slug').value;
        const postId = document.getElementById('post_id').value;
        
        if (!slug) {
            const slugMessageEl = document.getElementById('slug-message');
            if (slugMessageEl) slugMessageEl.innerHTML = '';
            return;
        }
        
        fetch(routes.validateSlugUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ slug: slug, post_id: postId })
        })
        .then(response => response.json())
        .then(data => {
            const slugInput = document.getElementById('slug');
            const slugMessageEl = document.getElementById('slug-message');
            
            if (data.valid) {
                if (slugMessageEl) slugMessageEl.innerHTML = '<span class="text-success">' + data.message + '</span>';
                if (slugInput) slugInput.classList.remove('is-invalid');
            } else {
                if (slugMessageEl) slugMessageEl.innerHTML = '<span class="text-danger">' + data.message + '</span>';
                if (slugInput) slugInput.classList.add('is-invalid');
            }
        })
        .catch(error => {
            console.error('Error validating slug:', error);
        });
    }

    /**
     * Handle image preview
     */
    function handleImagePreview(inputElement, previewDivId) {
        const previewDiv = document.getElementById(previewDivId);
        if (!previewDiv) return;
        
        const previewImg = previewDiv.querySelector('img');
        if (!previewImg) return;
        
        if (inputElement.files && inputElement.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewDiv.classList.remove('d-none');
            };
            
            reader.readAsDataURL(inputElement.files[0]);
        } else {
            previewDiv.classList.add('d-none');
        }
    }

    /**
     * Handle edit post
     */
    function handleEditPost(postId) {
        resetForm();
        
        // Set ID and title
        document.getElementById('post_id').value = postId;
        document.getElementById('blogModalLabel').textContent = 'Edit Blog Post';
        
        // Show loading overlay
        const modalBody = blogModal.querySelector('.modal-body');
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
        
        // Fetch post data
        fetch(routes.editUrl.replace(':id', postId), {
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
            
            if (data.success && data.post) {
                const post = data.post;
                
                // Set basic form values
                document.getElementById('title').value = post.title || '';
                document.getElementById('slug').value = post.slug || '';
                document.getElementById('category_id').value = post.category_id || '';
                document.getElementById('excerpt').value = post.excerpt || '';
                
                // Format date-time
                if (post.published_at) {
                    const publishedAt = post.published_at.slice(0, 16); // Format: YYYY-MM-DDThh:mm
                    document.getElementById('published_at').value = publishedAt;
                } else {
                    document.getElementById('published_at').value = '';
                }
                
                // Set checkboxes
                document.getElementById('is_published').checked = post.is_published ? true : false;
                document.getElementById('is_featured').checked = post.is_featured ? true : false;
                document.getElementById('allow_comments').checked = post.allow_comments ? true : false;
                
                // Set SEO fields
                document.getElementById('meta_title').value = post.meta_title || '';
                document.getElementById('meta_description').value = post.meta_description || '';
                document.getElementById('meta_keywords').value = post.meta_keywords || '';
                document.getElementById('canonical_url').value = post.canonical_url || '';
                
                // Set social media fields
                document.getElementById('facebook_description').value = post.facebook_description || '';
                document.getElementById('twitter_description').value = post.twitter_description || '';
                
                // Set tags using Select2
                if (post.tags && post.tags.length > 0 && typeof $.fn !== 'undefined' && $.fn.select2) {
                    const tagIds = post.tags.map(tag => tag.id);
                    $('#tags').val(tagIds).trigger('change');
                }
                
                // Set content in CKEditor
                if (editor) {
                    editor.setData(post.content || '');
                }
                
                // Show featured image preview if exists
                const featuredPreviewDiv = document.getElementById('featured-image-preview');
                const featuredPreviewImg = featuredPreviewDiv?.querySelector('img');
                if (featuredPreviewDiv && featuredPreviewImg && post.featured_image_url) {
                    featuredPreviewImg.src = post.featured_image_url;
                    featuredPreviewDiv.classList.remove('d-none');
                }
                
                // Show social image preview if exists
                const socialPreviewDiv = document.getElementById('social-image-preview');
                const socialPreviewImg = socialPreviewDiv?.querySelector('img');
                if (socialPreviewDiv && socialPreviewImg && post.social_image_url) {
                    socialPreviewImg.src = post.social_image_url;
                    socialPreviewDiv.classList.remove('d-none');
                }
                
                // Update social previews
                updateSocialPreviews();
            } else {
                // Hide modal on error
                const modal = bootstrap.Modal.getInstance(blogModal);
                if (modal) modal.hide();
                
                Swal.fire('Error', 'Failed to load blog post data', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Remove loading overlay
            const overlay = document.getElementById('loading-overlay');
            if (overlay) overlay.remove();
            
            // Hide modal on error
            const modal = bootstrap.Modal.getInstance(blogModal);
            if (modal) modal.hide();
            
            Swal.fire('Error', 'Failed to load blog post data', 'error');
        });
    }
    
    /**
     * Toggle featured status
     */
    function toggleFeatured(postId, button) {
        fetch(routes.toggleFeaturedUrl.replace(':id', postId), {
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
                if (data.is_featured) {
                    button.innerHTML = '<i class="fas fa-star text-warning"></i>';
                    button.setAttribute('data-featured', '1');
                    button.setAttribute('title', 'Remove from featured');
                } else {
                    button.innerHTML = '<i class="far fa-star"></i>';
                    button.setAttribute('data-featured', '0');
                    button.setAttribute('title', 'Add to featured');
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
                text: error.message || 'Failed to update featured status',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        });
    }
    
    /**
     * Toggle published status
     */
    function togglePublished(postId, button) {
        fetch(routes.togglePublishedUrl.replace(':id', postId), {
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
                if (data.is_published) {
                    button.innerHTML = '<i class="fas fa-eye"></i>';
                    button.setAttribute('data-published', '1');
                    button.setAttribute('title', 'Unpublish');
                } else {
                    button.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    button.setAttribute('data-published', '0');
                    button.setAttribute('title', 'Publish');
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
                text: error.message || 'Failed to update published status',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        });
    }
    
    /**
     * Delete post
     */
    function deletePost(postId) {
        fetch(routes.deleteUrl.replace(':id', postId), {
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
                    text: data.message || 'Blog post deleted successfully',
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
                text: error.message || 'Failed to delete blog post',
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
        
        const slugMessageEl = document.getElementById('slug-message');
        if (slugMessageEl) slugMessageEl.innerHTML = '';
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
        
        // If there's an error in the content tab fields, switch to basic-info tab
        if (errors.content || errors.title || errors.category_id) {
            activateTab('basic-info');
        } else if (errors.featured_image || errors.social_image) {
            activateTab('images');
        } else if (errors.meta_title || errors.meta_description || errors.meta_keywords || errors.canonical_url) {
            activateTab('seo');
        } else if (errors.facebook_description || errors.twitter_description) {
            activateTab('social');
        }
    }
    
    /**
     * Reset form
     */
    function resetForm() {
        if (blogForm) {
            blogForm.reset();
        }
        
        document.getElementById('post_id').value = '';
        
        // Reset image previews
        const featuredPreviewDiv = document.getElementById('featured-image-preview');
        const socialPreviewDiv = document.getElementById('social-image-preview');
        
        if (featuredPreviewDiv) featuredPreviewDiv.classList.add('d-none');
        if (socialPreviewDiv) socialPreviewDiv.classList.add('d-none');
        
        // Reset select2 tags
        if (typeof $.fn !== 'undefined' && $.fn.select2) {
            $('#tags').val(null).trigger('change');
        }
        
        // Reset CKEditor
        if (editor) {
            editor.setData('');
        }
        
        // Clear validation errors
        clearValidationErrors();
        
        // Update social previews
        updateSocialPreviews();
    }
});

// Add CSS styles for blog modal to ensure proper display
(function() {
    // Only add these styles if they don't already exist
    if (!document.getElementById('blog-modal-styles')) {
        const styleEl = document.createElement('style');
        styleEl.id = 'blog-modal-styles';
        styleEl.innerHTML = `
            /* Critical fix for tab display issues */
            #blogTabContent > .tab-pane {
                display: none !important;
            }
            
            #blogTabContent > .tab-pane.active.show {
                display: block !important;
            }
            
            /* Modal size enhancements */
            .modal-dialog.modal-xl {
                max-width: 90%;
                width: 90%;
                margin: 1.75rem auto;
            }
            
            /* Modal content styling */
            .modal-body {
                padding: 1.5rem;
                max-height: calc(100vh - 200px);
                overflow-y: auto;
            }
            
            /* Fix CKEditor display */
            .ck-editor__editable {
                min-height: 300px !important;
                z-index: 1;
                opacity: 1 !important;
                background-color: #fff !important;
            }
            
            .ck.ck-editor__main > .ck-editor__editable:not(.ck-focused) {
                border-color: #ced4da !important;
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
            
            /* Tab content area */
            .tab-content {
                padding: 1.25rem;
                border: 1px solid #dee2e6;
                border-top: 0;
                border-radius: 0 0 0.25rem 0.25rem;
                background-color: #fff;
            }
            
            /* Form elements spacing */
            .tab-pane .form-group,
            .tab-pane .mb-3 {
                margin-bottom: 1.5rem;
            }
            
            /* Fix the modal backdrop */
            .modal-backdrop {
                opacity: 0.5 !important;
            }
            
            /* Ensure modal is on top */
            .modal {
                z-index: 1050 !important;
            }
            
            /* Fix Select2 in modals */
            .select2-container--open {
                z-index: 1060 !important;
            }
        `;
        
        document.head.appendChild(styleEl);
    }
})();
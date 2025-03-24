@extends('admin.layouts.master')

@section('title', 'Blog Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Blog Management</h3>
        <div class="page-actions">
            @can('create blog posts')
                <button type="button" class="btn btn-primary" id="createBlogBtn">
                    <i class="fas fa-plus"></i> Add New Post
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Posts</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter" name="status">
                        <option value="">All Statuses</option>
                        <option value="published">Published</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select class="form-select" id="categoryFilter" name="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="featuredFilter" class="form-label">Featured</label>
                    <select class="form-select" id="featuredFilter" name="is_featured">
                        <option value="">All Posts</option>
                        <option value="1">Featured Only</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Apply
                    </button>
                    <button type="button" id="resetFilterBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Blog Posts</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="blogs-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Published Date</th>
                            <th>Comments</th>
                            @if(auth()->guard('admin')->user()->can('edit blog posts') || auth()->guard('admin')->user()->can('delete blog posts'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Blog Modal -->
    <div class="modal fade" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="blogModalLabel">Add New Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="blogForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="post_id" id="post_id">
                    <div class="modal-body">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs" id="blogTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab"
                                    data-bs-target="#basic-info" type="button" role="tab" aria-controls="basic-info"
                                    aria-selected="true">Basic Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="images-tab" data-bs-toggle="tab" data-bs-target="#images"
                                    type="button" role="tab" aria-controls="images" aria-selected="false">Images</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo"
                                    type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social"
                                    type="button" role="tab" aria-controls="social" aria-selected="false">Social
                                    Media</button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="blogTabContent">
                            <!-- Basic Info Tab (formerly Content Tab) -->
                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel"
                                aria-labelledby="basic-info-tab">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                            <div class="invalid-feedback" id="title-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="slug" class="form-label">Slug</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="slug" name="slug">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    id="generateSlugBtn">Generate</button>
                                            </div>
                                            <div class="invalid-feedback" id="slug-error"></div>
                                            <div class="form-text" id="slug-message"></div>
                                            <small class="text-muted">Slug will be generated automatically from title if
                                                left empty</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="editor" class="form-label">Content <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" id="editor" name="content"></textarea>
                                            <div class="invalid-feedback" id="content-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="excerpt" class="form-label">Excerpt</label>
                                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
                                            <div class="invalid-feedback" id="excerpt-error"></div>
                                            <div class="form-text">A short summary of the post (max 500 characters)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="category_id-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tags" class="form-label">Tags</label>
                                            <select class="form-select" id="tags" name="tags[]" multiple>
                                                @foreach($tags as $tag)
                                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="tags-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="published_at" class="form-label">Publish Date</label>
                                            <input type="datetime-local" class="form-control" id="published_at"
                                                name="published_at">
                                            <div class="invalid-feedback" id="published_at-error"></div>
                                            <div class="form-text">Leave blank to publish immediately</div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_published"
                                                    name="is_published" checked>
                                                <label class="form-check-label" for="is_published">Published</label>
                                            </div>
                                            <div class="invalid-feedback" id="is_published-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_featured"
                                                    name="is_featured">
                                                <label class="form-check-label" for="is_featured">Featured Post</label>
                                            </div>
                                            <div class="invalid-feedback" id="is_featured-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="allow_comments"
                                                    name="allow_comments" checked>
                                                <label class="form-check-label" for="allow_comments">Allow Comments</label>
                                            </div>
                                            <div class="invalid-feedback" id="allow_comments-error"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Images Tab -->
                            <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="featured_image" class="form-label">Featured Image</label>
                                            <input type="file" class="form-control" id="featured_image"
                                                name="featured_image" accept="image/*">
                                            <div class="invalid-feedback" id="featured_image-error"></div>
                                            <div class="form-text">Recommended size: 1200 x 630 pixels</div>
                                        </div>
                                        <div id="featured-image-preview" class="mt-2 d-none">
                                            <img src="" alt="Featured Image" class="img-thumbnail"
                                                style="max-height: 200px;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="social_image" class="form-label">Social Media Image</label>
                                            <input type="file" class="form-control" id="social_image" name="social_image"
                                                accept="image/*">
                                            <div class="invalid-feedback" id="social_image-error"></div>
                                            <div class="form-text">Recommended size: 1200 x 630 pixels. If not provided,
                                                featured image will be used.</div>
                                        </div>
                                        <div id="social-image-preview" class="mt-2 d-none">
                                            <img src="" alt="Social Image" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                    <div class="form-text">If left blank, the post title will be used</div>
                                </div>
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                    <div class="form-text">If left blank, the excerpt will be used</div>
                                </div>
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <div class="form-text">Separate keywords with commas</div>
                                </div>
                                <div class="mb-3">
                                    <label for="canonical_url" class="form-label">Canonical URL</label>
                                    <input type="url" class="form-control" id="canonical_url" name="canonical_url">
                                    <div class="invalid-feedback" id="canonical_url-error"></div>
                                    <div class="form-text">Leave blank to use the default URL</div>
                                </div>
                            </div>

                            <!-- Social Media Tab -->
                            <div class="tab-pane fade" id="social" role="tabpanel" aria-labelledby="social-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="facebook_description" class="form-label">Facebook
                                                Description</label>
                                            <textarea class="form-control" id="facebook_description"
                                                name="facebook_description" rows="3"></textarea>
                                            <div class="invalid-feedback" id="facebook_description-error"></div>
                                            <div class="form-text">If left blank, the meta description will be used</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="twitter_description" class="form-label">Twitter Description</label>
                                            <textarea class="form-control" id="twitter_description"
                                                name="twitter_description" rows="3"></textarea>
                                            <div class="invalid-feedback" id="twitter_description-error"></div>
                                            <div class="form-text">If left blank, the meta description will be used</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Preview</label>
                                            <div class="social-preview bg-light p-3 rounded">
                                                <div class="facebook-preview mb-4">
                                                    <h6><i class="fab fa-facebook text-primary"></i> Facebook Preview</h6>
                                                    <div class="border rounded p-3 bg-white">
                                                        <div id="fb-title" class="fw-bold">Your post title will appear here
                                                        </div>
                                                        <div id="fb-description" class="small text-muted mt-2">Your Facebook
                                                            description will appear here</div>
                                                    </div>
                                                </div>
                                                <div class="twitter-preview">
                                                    <h6><i class="fab fa-twitter text-info"></i> Twitter Preview</h6>
                                                    <div class="border rounded p-3 bg-white">
                                                        <div id="tw-title" class="fw-bold">Your post title will appear here
                                                        </div>
                                                        <div id="tw-description" class="small text-muted mt-2">Your Twitter
                                                            description will appear here</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                    <p>Are you sure you want to delete the blog post "<strong id="delete-post-title"></strong>"?</p>
                    <p class="text-danger">This action cannot be undone and will remove all associated comments.</p>
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
    <style>
        /* Critical fixes for modal display */
        #blogModal {
            z-index: 1050 !important;
        }

        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal-content {
            opacity: 1 !important;
            background-color: #fff !important;
        }

        /* Critical fix for tab display issue */
        #blogTabContent>.tab-pane {
            display: none !important;
        }

        #blogTabContent>.tab-pane.active.show {
            display: block !important;
        }

        /* Modal styling */
        .modal-dialog.modal-xl {
            max-width: 90%;
            width: 90%;
            margin: 1.75rem auto;
        }

        /* Tab content area */
        .tab-content {
            border: 1px solid #dee2e6;
            border-top: 0;
            padding: 1.25rem;
            background-color: #fff;
            border-bottom-right-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }

        /* Image previews */
        #featured-image-preview img,
        #social-image-preview img {
            max-height: 200px;
            width: auto;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        /* CKEditor minimum height */
        .ck-editor__editable {
            min-height: 300px !important;
            z-index: 1;
        }

        /* Social preview styling */
        .social-preview {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 1rem;
        }

        /* Loading overlay */
        #loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            z-index: 1050;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Select2 adjustments for modal */
        .select2-container--open {
            z-index: 1060 !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.blog-posts.data') }}",
            storeUrl: "{{ route('admin.blog-posts.store') }}",
            editUrl: "{{ route('admin.blog-posts.edit', ':id') }}",
            updateUrl: "{{ route('admin.blog-posts.update', ':id') }}",
            deleteUrl: "{{ route('admin.blog-posts.destroy', ':id') }}",
            toggleFeaturedUrl: "{{ route('admin.blog-posts.toggle-featured', ':id') }}",
            togglePublishedUrl: "{{ route('admin.blog-posts.toggle-published', ':id') }}",
            validateSlugUrl: "{{ route('admin.blog-posts.validate-slug') }}",
            generateSlugUrl: "{{ route('admin.blog-posts.generate-slug') }}",
            uploadImageUrl: "{{ route('admin.blog-posts.upload-image') }}"
        };

        // Pass permissions data to JavaScript
        const canEditBlogPosts = @json(auth()->guard('admin')->user()->can('edit blog posts'));
        const canDeleteBlogPosts = @json(auth()->guard('admin')->user()->can('delete blog posts'));
    </script>

    <!-- Include your blog management JS file -->
    <script src="{{ asset('admin/js/blog-posts-management.js') }}"></script>

    <!-- Modal fix script -->
    <script>
        // Additional emergency fixes for modal display issues
        $(document).ready(function () {
            // Fix for modal content not showing
            $('#blogModal').on('shown.bs.modal', function () {
                // Ensure the modal is visible
                $(this).css({
                    'display': 'block',
                    'opacity': 1
                });

                $(this).find('.modal-content').css({
                    'opacity': 1,
                    'display': 'block'
                });

                // Force basic-info tab to be active
                $('#blogTabs .nav-link').removeClass('active');
                $('#basic-info-tab').addClass('active').attr('aria-selected', 'true');

                $('#blogTabContent .tab-pane').removeClass('active show').css('display', 'none');
                $('#basic-info').addClass('active show').css('display', 'block');

                // Force CKEditor to be visible
                setTimeout(function () {
                    const editorElement = document.querySelector('.ck-editor__editable');
                    if (editorElement) {
                        editorElement.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; min-height: 300px !important;';
                    }

                    // Refresh CKEditor if it exists
                    if (typeof editor !== 'undefined') {
                        try {
                            editor.ui.update();
                        } catch (e) {
                            console.log('CKEditor update failed:', e);
                        }
                    }
                }, 100);
            });
        });
    </script>
@endpush
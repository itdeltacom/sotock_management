@extends('admin.layouts.master')

@section('title', 'Blog Management')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Blog Management</h6>
                            </div>
                            <div class="col-6 text-end">
                                @can('create blog posts')
                                    <button type="button" class="btn bg-gradient-primary" id="createBlogBtn">
                                        <i class="fas fa-plus"></i> Add New Post
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="card-header pb-0 p-3">
                        <form id="filterForm" class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="statusFilter" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="published">Published</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="categoryFilter" name="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select form-select-sm" id="featuredFilter" name="is_featured">
                                    <option value="">All Posts</option>
                                    <option value="1">Featured Only</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-sm bg-gradient-info me-2">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <button type="button" id="resetFilterBtn" class="btn btn-sm bg-gradient-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="blogs-table" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Published Date</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Comments</th>
                                        @if(auth()->guard('admin')->user()->can('edit blog posts') || auth()->guard('admin')->user()->can('delete blog posts'))
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Blog Modal -->
    <div class="modal fade" id="blogModal" tabindex="-1" aria-labelledby="blogModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
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
                        <ul class="nav nav-pills mb-3" id="blogTabs" role="tablist">
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
                                            <label for="title" class="form-control-label">Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="title" name="title" required>
                                            <div class="invalid-feedback" id="title-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="slug" class="form-control-label">Slug</label>
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
                                            <label for="editor" class="form-control-label">Content <span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" id="editor" name="content"></textarea>
                                            <div class="invalid-feedback" id="content-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="excerpt" class="form-control-label">Excerpt</label>
                                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
                                            <div class="invalid-feedback" id="excerpt-error"></div>
                                            <div class="form-text">A short summary of the post (max 500 characters)</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-control-label">Category <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control" id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="category_id-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tags" class="form-control-label">Tags</label>
                                            <select class="form-control" id="tags" name="tags[]" multiple>
                                                @foreach($tags as $tag)
                                                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="invalid-feedback" id="tags-error"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="published_at" class="form-control-label">Publish Date</label>
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
                                            <label for="featured_image" class="form-control-label">Featured Image</label>
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
                                            <label for="social_image" class="form-control-label">Social Media Image</label>
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
                                    <label for="meta_title" class="form-control-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                    <div class="form-text">If left blank, the post title will be used</div>
                                </div>
                                <div class="mb-3">
                                    <label for="meta_description" class="form-control-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                    <div class="form-text">If left blank, the excerpt will be used</div>
                                </div>
                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-control-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <div class="form-text">Separate keywords with commas</div>
                                </div>
                                <div class="mb-3">
                                    <label for="canonical_url" class="form-control-label">Canonical URL</label>
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
                                            <label for="facebook_description" class="form-control-label">Facebook
                                                Description</label>
                                            <textarea class="form-control" id="facebook_description"
                                                name="facebook_description" rows="3"></textarea>
                                            <div class="invalid-feedback" id="facebook_description-error"></div>
                                            <div class="form-text">If left blank, the meta description will be used</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="twitter_description" class="form-control-label">Twitter Description</label>
                                            <textarea class="form-control" id="twitter_description"
                                                name="twitter_description" rows="3"></textarea>
                                            <div class="invalid-feedback" id="twitter_description-error"></div>
                                            <div class="form-text">If left blank, the meta description will be used</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-control-label">Preview</label>
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn bg-gradient-primary" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="py-3 text-center">
                        <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                        <p>Are you sure you want to delete the blog post "<strong id="delete-post-title"></strong>"?</p>
                        <p class="text-danger">This action cannot be undone and will remove all associated comments.</p>
                        <div id="delete-warning" class="text-danger mt-3"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bg-gradient-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            padding: 1.5rem;
        }

        .form-control,
        .form-select {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.4;
            border-radius: 0.5rem;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-control-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        /* Nav pills styling for tabs */
        .nav-pills .nav-link {
            color: #344767;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
        }

        .nav-pills .nav-link.active {
            color: #fff;
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        /* Buttons and gradients */
        .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(310deg, #2dce89 0%, #2dcecc 100%);
        }

        .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(310deg, #fb6340 0%, #fbb140 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(310deg, #11cdef 0%, #1171ef 100%);
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(310deg, #627594, #8097bf);
        }

        /* Modal styling */
        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 85vh;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
            max-height: calc(85vh - 130px);
        }

        /* DataTable styling */
        table.dataTable {
            margin-top: 0 !important;
        }

        .table thead th {
            padding: 0.75rem 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-size: 0.65rem;
            font-weight: 700;
            border-bottom-width: 1px;
        }

        .table td {
            white-space: nowrap;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
        }

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
        
        /* Image Previews */
        #featured-image-preview img,
        #social-image-preview img {
            max-height: 200px;
            width: auto;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Social Preview */
        .social-preview {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .social-preview h6 {
            color: #8392AB;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .social-preview .fb-preview,
        .social-preview .twitter-preview {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Form Switches */
        .form-check-input:checked {
            background-color: #5e72e4;
            border-color: #5e72e4;
        }

        /* Loading Overlay */
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
            border-radius: 0.75rem;
        }

        /* Select2 Styling */
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-color: #d2d6da;
        }

        .select2-container--bootstrap-5 .select2-selection:focus {
            border-color: #5e72e4;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }
        
        /* CKEditor styling */
        .ck-editor__editable_inline {
            min-height: 300px !important;
            border-radius: 0 0 0.5rem 0.5rem !important;
            border-color: #d2d6da !important;
        }
        
        .ck-toolbar {
            border-radius: 0.5rem 0.5rem 0 0 !important;
            border-color: #d2d6da !important;
        }
        
        .ck.ck-editor__main > .ck-editor__editable:focus {
            border-color: #5e72e4 !important;
            box-shadow: 0 3px 9px rgba(50, 50, 9, 0), 3px 4px 8px rgba(94, 114, 228, 0.1) !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/38.0.1/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configure SweetAlert to use Argon style
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            // Override showAlert function if needed
            if (typeof window.showAlert !== 'function') {
                window.showAlert = function (title, text, icon) {
                    Toast.fire({
                        icon: icon,
                        title: title,
                        text: text
                    });
                };
            }
            
            // Initialize Select2
            if ($.fn.select2) {
                $('#tags').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select tags',
                    allowClear: true,
                    width: '100%'
                });
                
                $('#category_id').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Select category',
                    width: '100%'
                });
            }
            
            // Initialize CKEditor
            let editor;
            if (ClassicEditor) {
                ClassicEditor
                    .create(document.querySelector('#editor'), {
                        toolbar: [
                            'heading', '|', 
                            'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 
                            'blockQuote', 'insertTable', 'imageUpload', '|', 
                            'undo', 'redo'
                        ],
                        image: {
                            upload: {
                                types: ['jpeg', 'png', 'gif', 'jpg', 'webp'],
                                url: routes.uploadImageUrl
                            },
                            toolbar: [
                                'imageStyle:alignLeft', 'imageStyle:full', 'imageStyle:alignRight',
                                '|',
                                'imageTextAlternative'
                            ]
                        }
                    })
                    .then(newEditor => {
                        editor = newEditor;
                        window.editor = editor;
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });
            }
            
            // Image preview functionality
            document.getElementById('featured_image')?.addEventListener('change', function() {
                previewImage(this, 'featured-image-preview');
            });
            
            document.getElementById('social_image')?.addEventListener('change', function() {
                previewImage(this, 'social-image-preview');
            });
            
            // Generate slug button
            document.getElementById('generateSlugBtn')?.addEventListener('click', function() {
                const title = document.getElementById('title').value;
                if (title) {
                    fetch(routes.generateSlugUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                        },
                        body: JSON.stringify({ title: title })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('slug').value = data.slug;
                            document.getElementById('slug-message').textContent = 'Slug generated successfully';
                            document.getElementById('slug-message').classList.add('text-success');
                            document.getElementById('slug-message').classList.remove('text-danger');
                        } else {
                            document.getElementById('slug-message').textContent = 'Failed to generate slug';
                            document.getElementById('slug-message').classList.add('text-danger');
                            document.getElementById('slug-message').classList.remove('text-success');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('slug-message').textContent = 'Failed to generate slug';
                        document.getElementById('slug-message').classList.add('text-danger');
                        document.getElementById('slug-message').classList.remove('text-success');
                    });
                } else {
                    document.getElementById('slug-message').textContent = 'Please enter a title first';
                    document.getElementById('slug-message').classList.add('text-danger');
                    document.getElementById('slug-message').classList.remove('text-success');
                }
            });
            
            // Reset filter button
            document.getElementById('resetFilterBtn')?.addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                document.getElementById('filterForm').dispatchEvent(new Event('submit'));
            });
            
            // Social preview functionality
            const titleInput = document.getElementById('title');
            const fbTitleElement = document.getElementById('fb-title');
            const twTitleElement = document.getElementById('tw-title');
            const fbDescriptionElement = document.getElementById('fb-description');
            const twDescriptionElement = document.getElementById('tw-description');
            const metaDescriptionInput = document.getElementById('meta_description');
            const facebookDescriptionInput = document.getElementById('facebook_description');
            const twitterDescriptionInput = document.getElementById('twitter_description');
            
            if (titleInput && fbTitleElement && twTitleElement) {
                titleInput.addEventListener('input', function() {
                    fbTitleElement.textContent = this.value || 'Your post title will appear here';
                    twTitleElement.textContent = this.value || 'Your post title will appear here';
                });
            }
            
            if (metaDescriptionInput && fbDescriptionElement && twDescriptionElement) {
                metaDescriptionInput.addEventListener('input', function() {
                    if (!facebookDescriptionInput.value.trim()) {
                        fbDescriptionElement.textContent = this.value || 'Your Facebook description will appear here';
                    }
                    if (!twitterDescriptionInput.value.trim()) {
                        twDescriptionElement.textContent = this.value || 'Your Twitter description will appear here';
                    }
                });
            }
            
            if (facebookDescriptionInput && fbDescriptionElement) {
                facebookDescriptionInput.addEventListener('input', function() {
                    fbDescriptionElement.textContent = this.value || (metaDescriptionInput.value ? metaDescriptionInput.value : 'Your Facebook description will appear here');
                });
            }
            
            if (twitterDescriptionInput && twDescriptionElement) {
                twitterDescriptionInput.addEventListener('input', function() {
                    twDescriptionElement.textContent = this.value || (metaDescriptionInput.value ? metaDescriptionInput.value : 'Your Twitter description will appear here');
                });
            }
            
            // Fix for modal display issues
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

                $('#blogTabContent .tab-pane').removeClass('active show');
                $('#basic-info').addClass('active show');

                // Force CKEditor to be visible
                setTimeout(function () {
                    const editorElement = document.querySelector('.ck-editor__editable');
                    if (editorElement) {
                        editorElement.style.cssText = 'display: block !important; visibility: visible !important; opacity: 1 !important; min-height: 300px !important;';
                    }

                    // Refresh CKEditor if it exists
                    if (typeof editor !== 'undefined' && editor) {
                        try {
                            editor.ui.update();
                        } catch (e) {
                            console.error('CKEditor update failed:', e);
                        }
                    }
                }, 100);
            });
            
            // Fix tab navigation in modal
            $('#blogTabs .nav-link').on('click', function() {
                const target = $(this).data('bs-target');
                
                // Remove active class from all tabs and panes
                $('#blogTabs .nav-link').removeClass('active').attr('aria-selected', 'false');
                $('#blogTabContent .tab-pane').removeClass('active show');
                
                // Add active class to clicked tab and corresponding pane
                $(this).addClass('active').attr('aria-selected', 'true');
                $(target).addClass('active show');
            });
            
            /**
             * Preview image helper function
             */
            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                if (!preview) return;
                
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = preview.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                            preview.classList.remove('d-none');
                        }
                    }
                    
                    reader.readAsDataURL(input.files[0]);
                } else {
                    preview.classList.add('d-none');
                }
            }
        });
    </script>
@endpush
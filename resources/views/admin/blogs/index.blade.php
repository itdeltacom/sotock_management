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
        /* Blog Management Styles - Argon-inspired */

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
            font-weight: 600;
        }

        .page-actions .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Card Styling */
        .card {
            box-shadow: 0 20px 27px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }

        .card .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-header h5 {
            margin-bottom: 0;
            font-size: 1rem;
            color: #344767;
            font-weight: 600;
        }

        /* Form Styling */
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
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0), 3px 4px 8px rgba(94, 114, 228, 0.1);
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #8392AB;
        }

        .form-text {
            font-size: 0.75rem;
            color: #8392AB;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .btn-outline-secondary {
            border-color: #67748e;
            color: #67748e;
            transition: all 0.15s ease;
        }

        .btn-outline-secondary:hover {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border-color: transparent;
        }

        .btn-secondary {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border: none;
        }

        .btn-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
            border: none;
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
            vertical-align: middle;
        }

        .table td {
            padding: 0.75rem 1.5rem;
            font-size: 0.875rem;
            vertical-align: middle;
            border-bottom: 1px solid #E9ECEF;
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

        /* Tab Styling */
        .nav-tabs .nav-link {
            color: #344767;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
            transition: all 0.15s ease;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
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

        .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100% - 3.5rem);
        }

        .modal-content {
            border: 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            align-items: center;
        }

        .modal-header .btn-close {
            background: transparent;
            opacity: 0.5;
            margin: -0.5rem -0.5rem -0.5rem auto;
            padding: 0.5rem;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
            background-color: rgba(0, 0, 0, 0.1);
        }

        .modal-title {
            font-weight: 600;
            color: #344767;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        /* Button styles in modal footer */
        .modal-footer .btn-secondary {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border: none;
        }

        .modal-footer .btn-outline-secondary {
            border-color: #67748e;
            color: #67748e;
            transition: all 0.15s ease;
        }

        .modal-footer .btn-outline-secondary:hover {
            background: linear-gradient(310deg, #67748e 0%, #344767 100%);
            color: white;
            border-color: transparent;
        }

        .modal-footer .btn-primary,
        .modal-footer .bg-gradient-primary {
            background: linear-gradient(310deg, #5e72e4 0%, #825ee4 100%);
            border: none;
            box-shadow: 0 3px 5px -1px rgba(94, 114, 228, 0.2), 0 2px 3px -1px rgba(94, 114, 228, 0.1);
        }

        .modal-footer .btn-danger,
        .modal-footer .bg-gradient-danger {
            background: linear-gradient(310deg, #f5365c 0%, #f56036 100%);
            border: none;
        }

        /* Delete Modal Special Styling */
        #deleteModal .modal-body {
            text-align: center;
        }

        #deleteModal .modal-body i {
            color: #fb6340;
            margin-bottom: 1rem;
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
@extends('admin.layouts.master')

@section('title', 'Blog Tags Management')

@section('content')
    <div class="page-header">
        <h3 class="page-title">Blog Tags Management</h3>
        <div class="page-actions">
            @can('create blog tags')
                <button type="button" class="btn btn-primary" id="createTagBtn">
                    <i class="fas fa-plus"></i> Add New Tag
                </button>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">All Tags</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tags-table" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Posts</th>
                            <th>Status</th>
                            @if(auth()->guard('admin')->user()->can('edit blog tags') || auth()->guard('admin')->user()->can('delete blog tags'))
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Tag Modal -->
    <div class="modal fade" id="tagModal" tabindex="-1" aria-labelledby="tagModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tagModalLabel">Add New Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="tagForm">
                    @csrf
                    <input type="hidden" name="tag_id" id="tag_id">
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="tagTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic"
                                    type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo"
                                    type="button" role="tab" aria-controls="seo" aria-selected="false">SEO</button>
                            </li>
                        </ul>

                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="tagTabsContent">
                            <!-- Basic Info Tab -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tag Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <div class="invalid-feedback" id="name-error"></div>
                                    <small class="form-text text-muted">The name is how the tag appears on your
                                        site.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control ckeditor" id="description" name="description"
                                        rows="4"></textarea>
                                    <div class="invalid-feedback" id="description-error"></div>
                                    <small class="form-text text-muted">The description is not prominent by default;
                                        however, some themes may show it.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Status</label>
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="is_active-error"></div>
                                </div>
                            </div>

                            <!-- SEO Tab -->
                            <div class="tab-pane fade" id="seo" role="tabpanel" aria-labelledby="seo-tab">
                                <div class="mb-3">
                                    <label for="meta_title" class="form-label">Meta Title</label>
                                    <input type="text" class="form-control" id="meta_title" name="meta_title">
                                    <div class="invalid-feedback" id="meta_title-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description"
                                        rows="3"></textarea>
                                    <div class="invalid-feedback" id="meta_description-error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords">
                                    <div class="invalid-feedback" id="meta_keywords-error"></div>
                                    <small class="form-text text-muted">Separate keywords with commas</small>
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
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .ck-editor__editable_inline {
            min-height: 200px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>

    <script>
        // Routes for AJAX calls
        const routes = {
            dataUrl: "{{ route('admin.blog-tags.data') }}",
            storeUrl: "{{ route('admin.blog-tags.store') }}",
            editUrl: "{{ route('admin.blog-tags.edit', ':id') }}",
            updateUrl: "{{ route('admin.blog-tags.update', ':id') }}",
            destroyUrl: "{{ route('admin.blog-tags.destroy', ':id') }}"
        };

        // Pass permissions data to JavaScript
        const canEditTags = @json(auth()->guard('admin')->user()->can('edit blog tags'));
        const canDeleteTags = @json(auth()->guard('admin')->user()->can('delete blog tags'));
    </script>

    <!-- Include the JS for tags management -->
    <script src="{{ asset('admin/js/blog-tags-management.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize CKEditor
            let editor;

            ClassicEditor
                .create(document.querySelector('#description'))
                .then(newEditor => {
                    editor = newEditor;
                    window.editor = editor; // Make it globally available

                    // Save CKEditor content to form when submitting
                    document.getElementById('tagForm').addEventListener('submit', function () {
                        document.getElementById('description').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error(error);
                });

            // Fix Bootstrap modal tab navigation
            document.querySelectorAll('#tagTabs .nav-link').forEach(function (tabLink) {
                tabLink.addEventListener('click', function (e) {
                    const targetId = this.getAttribute('data-bs-target');
                    const tabPanes = document.querySelectorAll('#tagTabsContent .tab-pane');
                    const navLinks = document.querySelectorAll('#tagTabs .nav-link');

                    // Remove active class from all nav links and tab panes
                    navLinks.forEach(link => link.classList.remove('active'));
                    tabPanes.forEach(pane => {
                        pane.classList.remove('active', 'show');
                    });

                    // Add active class to current nav link and tab pane
                    this.classList.add('active');
                    document.querySelector(targetId).classList.add('active', 'show');
                });
            });

            // Modify the original handleEditTag function to set CKEditor content
            const originalHandleEditTag = window.handleEditTag;
            if (typeof originalHandleEditTag === 'function') {
                window.handleEditTag = function (tagId) {
                    originalHandleEditTag(tagId);

                    // Add additional code to set CKEditor content after modal is shown
                    $('#tagModal').on('shown.bs.modal', function () {
                        if (editor && document.getElementById('description')) {
                            const descriptionValue = document.getElementById('description').value;
                            editor.setData(descriptionValue);
                        }
                    });
                };
            }
        });
    </script>
@endpush
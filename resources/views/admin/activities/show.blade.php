@extends('admin.layouts.master')

@section('title', 'Activity Log')@section('content')<div class="page-header">
        <h3 class="page-title">Activity Log</h3>
        <div class="page-actions">
            @can('manage settings')
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearActivityModal">
                    <i class="fas fa-trash"></i> Clear All Activities
                </button>
            @endcan
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">Filter Activities</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3" method="GET" action="{{ route('admin.activities.index') }}">
                <div class="col-md-3">
                    <label for="type" class="form-label">Activity Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                        value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                        value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="user_id" class="form-label">Admin User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        @foreach($adminUsers as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.activities.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Activity Records</h5>
        </div>
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Browser</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('M d, Y H:i:s') }}</td>
                                    <td>
                                        <span class="badge bg-{{ getActivityTypeBadge($activity->type) }}">
                                            {{ ucfirst($activity->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($activity->user)
                                            {{ $activity->user->name }}
                                        @else
                                            <span class="text-muted">System</span>
                                        @endif
                                    </td>
                                    <td>{{ $activity->ip_address }}</td>
                                    <td>
                                        @if(isset($activity->properties['location']))
                                            {{ $activity->properties['location'] }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($activity->properties['user_agent']))
                                            <span title="{{ $activity->properties['user_agent'] }}">
                                                {{ formatUserAgent($activity->properties['user_agent']) }}
                                            </span>
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($activity->description, 80) }}</td>
                                    <td>
                                        <a href="{{ route('admin.activities.show', $activity) }}" class="btn btn-sm btn-info me-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('manage settings')
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                                data-id="{{ $activity->id }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $activities->links() }}
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i> No activity records found.
                </div>
            @endif
        </div>
    </div>

    <!-- Clear Activity Log Confirmation Modal -->
    <div class="modal fade" id="clearActivityModal" tabindex="-1" aria-labelledby="clearActivityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clearActivityModalLabel">Clear All Activity Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning!</strong> This action cannot be undone.
                    </div>
                    <p>Are you sure you want to clear all activity logs from the system?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.activities.clear') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Clear All Records</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete Activity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this activity record?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .activity-type-badge {
            min-width: 80px;
            display: inline-block;
            text-align: center;
        }
    </style>
@endpush

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set up delete form action
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = "{{ route('admin.activities.destroy', '') }}/" + id;
                });
            }

            // Date range validation
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            if (startDateInput && endDateInput) {
                endDateInput.addEventListener('change', function() {
                    if (startDateInput.value && endDateInput.value) {
                        if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                            alert('End date cannot be earlier than start date');
                            endDateInput.value = '';
                        }
                    }
                });

                startDateInput.addEventListener('change', function() {
                    if (startDateInput.value && endDateInput.value) {
                        if (new Date(endDateInput.value) < new Date(startDateInput.value)) {
                            alert('Start date cannot be later than end date');
                            startDateInput.value = '';
                        }
                    }
                });
            }
        });
    </script>
@endpush

@php
    /**
     * Get the badge color for activity types
     */
    function getActivityTypeBadge($type)
    {
        $badges = [
            'login' => 'success',
            'logout' => 'secondary',
            'create' => 'primary',
            'update' => 'info',
            'delete' => 'danger',
            'admin' => 'warning',
            'error' => 'danger',
            'security' => 'dark',
        ];

        return $badges[$type] ?? 'secondary';
    }

    /**
     * Format user agent string to show browser and OS
     */
    function formatUserAgent($userAgent)
    {
        // Browser detection
        $browser = 'Unknown';
        if (strpos($userAgent, 'Chrome') && !strpos($userAgent, 'Chromium')) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') && !strpos($userAgent, 'Chrome')) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge')) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'MSIE') || strpos($userAgent, 'Trident/')) {
            $browser = 'Internet Explorer';
        }

        // OS detection
        $os = 'Unknown';
        if (strpos($userAgent, 'Windows')) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Macintosh')) {
            $os = 'Mac OS';
        } elseif (strpos($userAgent, 'Linux')) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android')) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iPhone') || strpos($userAgent, 'iPad')) {
            $os = 'iOS';
        }

        return "$browser on $os";
    }
@endphp
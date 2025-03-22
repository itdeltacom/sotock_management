@extends('admin.layouts.master')

@section('title', 'Activity Log')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h3 class="page-title">Activity Log</h3>
    <div class="page-actions">
        <a href="#" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#filterModal">
            <i class="fas fa-filter"></i> Filter
        </a>
        <form method="POST" action="{{ route('admin.activities.clear') }}" class="d-inline" id="clear-activities-form">
            @csrf
            <button type="button" class="btn btn-sm btn-danger" onclick="confirmClearActivities()">
                <i class="fas fa-trash"></i> Clear All
            </button>
        </form>
    </div>
</div>

<!-- Activities Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-container">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Activity</th>
                        <th>User</th>
                        <th>Date & Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>
                            @switch($activity->type)
                                @case('booking')
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-book"></i> Booking
                                    </span>
                                    @break
                                @case('customer')
                                    <span class="badge badge-info">
                                        <i class="fas fa-user"></i> Customer
                                    </span>
                                    @break
                                @case('vehicle')
                                    <span class="badge badge-primary">
                                        <i class="fas fa-car"></i> Vehicle
                                    </span>
                                    @break
                                @case('admin')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-user-shield"></i> Admin
                                    </span>
                                    @break
                                @case('error')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-exclamation-circle"></i> Error
                                    </span>
                                    @break
                                @default
                                    <span class="badge badge-light">
                                        <i class="fas fa-info-circle"></i> {{ ucfirst($activity->type) }}
                                    </span>
                            @endswitch
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $activity->title }}</div>
                            <div class="text-muted small">{{ Str::limit($activity->description, 100) }}</div>
                        </td>
                        <td>
                            @if($activity->user)
                                {{ $activity->user->name }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $activity->created_at->format('M d, Y') }}</div>
                            <div class="text-muted small">{{ $activity->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="table-action-buttons">
                                <a href="{{ route('admin.activities.show', $activity->id) }}" class="btn btn-sm btn-icon btn-light" data-toggle="tooltip" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.activities.destroy', $activity->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-icon btn-light text-danger delete-btn" data-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">No activity records found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($activities->hasPages())
    <div class="card-footer">
        {{ $activities->links() }}
    </div>
    @endif
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Activities</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.activities.index') }}" method="GET">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="type">Activity Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($activityTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All Users</option>
                            @foreach($adminUsers as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <a href="{{ route('admin.activities.index') }}" class="btn btn-light">Clear Filters</a>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm delete for a single activity
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            confirmAction('Delete Activity', 'Are you sure you want to delete this activity record?', function() {
                form.submit();
            });
        });
    });
    
    // Confirm clear all activities
    function confirmClearActivities() {
        confirmAction(
            'Clear All Activities', 
            'Are you sure you want to clear all activity logs? This action cannot be undone.', 
            function() {
                document.getElementById('clear-activities-form').submit();
            }
        );
    }
</script>
@endpush
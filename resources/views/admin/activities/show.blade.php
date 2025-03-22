@extends('admin.layouts.master')

@section('title', 'Activity Details')

@section('content')
<!-- Page Header -->
<div class="page-header">
    <h3 class="page-title">Activity Details</h3>
    <div class="page-actions">
        <a href="{{ route('admin.activities.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Activities
        </a>
        <form action="{{ route('admin.activities.destroy', $activity->id) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-danger delete-btn">
                <i class="fas fa-trash"></i> Delete
            </button>
        </form>
    </div>
</div>

<!-- Activity Details Card -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            @switch($activity->type)
                @case('booking')
                    <i class="fas fa-book"></i>
                    @break
                @case('customer')
                    <i class="fas fa-user"></i>
                    @break
                @case('vehicle')
                    <i class="fas fa-car"></i>
                    @break
                @case('admin')
                    <i class="fas fa-user-shield"></i>
                    @break
                @case('error')
                    <i class="fas fa-exclamation-circle"></i>
                    @break
                @default
                    <i class="fas fa-info-circle"></i>
            @endswitch
            {{ $activity->title }}
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 150px;">Activity Type</th>
                        <td>
                            <span class="badge badge-{{ $activity->type == 'error' ? 'danger' : 'primary' }}">
                                {{ ucfirst($activity->type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Date & Time</th>
                        <td>{{ $activity->created_at->format('M d, Y h:i:s A') }}</td>
                    </tr>
                    <tr>
                        <th>Performed By</th>
                        <td>
                            @if($activity->user)
                                <div class="d-flex align-items-center">
                                    @if($activity->user_type == 'App\\Models\\Admin')
                                        <span class="mr-2 badge badge-warning">Admin</span>
                                    @else
                                        <span class="mr-2 badge badge-info">{{ class_basename($activity->user_type) }}</span>
                                    @endif
                                    {{ $activity->user->name }}
                                </div>
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $activity->ip_address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <table class="table table-bordered">
                    @if($activity->subject_id)
                    <tr>
                        <th style="width: 150px;">Related To</th>
                        <td>
                            <span class="badge badge-secondary">
                                {{ class_basename($activity->subject_type) }}
                            </span>
                            
                            @if($activity->subject)
                                @if($activity->subject_type == 'App\\Models\\Booking')
                                    <a href="{{ route('admin.bookings.show', $activity->subject_id) }}">
                                        #{{ $activity->subject->booking_number ?? $activity->subject_id }}
                                    </a>
                                @elseif($activity->subject_type == 'App\\Models\\Customer')
                                    <a href="{{ route('admin.customers.show', $activity->subject_id) }}">
                                        {{ $activity->subject->name ?? $activity->subject_id }}
                                    </a>
                                @elseif($activity->subject_type == 'App\\Models\\Vehicle')
                                    <a href="{{ route('admin.vehicles.show', $activity->subject_id) }}">
                                        {{ $activity->subject->model ?? $activity->subject_id }}
                                    </a>
                                @elseif($activity->subject_type == 'App\\Models\\Admin')
                                    <a href="{{ route('admin.admins.show', $activity->subject_id) }}">
                                        {{ $activity->subject->name ?? $activity->subject_id }}
                                    </a>
                                @else
                                    ID: {{ $activity->subject_id }}
                                @endif
                            @else
                                ID: {{ $activity->subject_id }} (Deleted)
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        
        <div class="mt-4">
            <h6 class="font-weight-bold">Description</h6>
            <div class="p-3 bg-light rounded">
                {{ $activity->description }}
            </div>
        </div>
        
        @if(!empty($activity->properties))
        <div class="mt-4">
            <h6 class="font-weight-bold">Additional Details</h6>
            <div class="p-3 bg-light rounded">
                <pre class="mb-0"><code>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirm delete
    document.querySelector('.delete-btn').addEventListener('click', function() {
        const form = this.closest('form');
        confirmAction('Delete Activity', 'Are you sure you want to delete this activity record?', function() {
            form.submit();
        });
    });
</script>
@endpush
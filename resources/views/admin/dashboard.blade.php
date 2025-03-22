@extends('admin.layouts.master')

@section('title', 'Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <h3 class="page-title">Dashboard</h3>
        <div class="page-actions">
            <span class="badge badge-primary">
                <i class="fas fa-calendar-alt"></i> {{ date('F d, Y') }}
            </span>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="dashboard-grid">
        <!-- Total Bookings Card -->
        <div class="stat-card stat-card-primary">
            <div class="stat-card-top">
                <div class="stat-card-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-title">Total Bookings</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-subtitle">
                        This month
                        <span class="stat-card-badge badge-secondary">
                            <i class="fas fa-minus"></i> 0%
                        </span>
                    </div>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.bookings.index') }}" class="stat-card-link">View Details <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="stat-card stat-card-secondary">
            <div class="stat-card-top">
                <div class="stat-card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-title">Total Revenue</div>
                    <div class="stat-card-value">$0.00</div>
                    <div class="stat-card-subtitle">
                        This month
                        <span class="stat-card-badge badge-secondary">
                            <i class="fas fa-minus"></i> 0%
                        </span>
                    </div>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.reports.revenue') }}" class="stat-card-link">View Reports <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Total Vehicles Card -->
        <div class="stat-card stat-card-success">
            <div class="stat-card-top">
                <div class="stat-card-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-title">Total Vehicles</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-subtitle">
                        <span>Available: 0</span>
                    </div>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.vehicles.index') }}" class="stat-card-link">Manage Fleet <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Active Customers Card -->
        <div class="stat-card stat-card-info">
            <div class="stat-card-top">
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-content">
                    <div class="stat-card-title">Active Customers</div>
                    <div class="stat-card-value">0</div>
                    <div class="stat-card-subtitle">
                        <span class="stat-card-badge badge-success">
                            <i class="fas fa-user-plus"></i> 0 new
                        </span>
                    </div>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('admin.customers.index') }}" class="stat-card-link">View Details <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-md-8">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Revenue Trends</h5>
                    <div class="chart-controls">
                        <select id="revenue-chart-period" class="form-select form-select-sm">
                            <option value="week">Weekly</option>
                            <option value="month" selected>Monthly</option>
                            <option value="year">Yearly</option>
                        </select>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="chart-container">
                <div class="chart-header">
                    <h5 class="chart-title">Booking Sources</h5>
                </div>
                <div class="chart-body">
                    <canvas id="bookingSourcesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings & Activity Feed -->
    <div class="row">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Bookings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking #</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentBookings) && count($recentBookings) > 0)
                                    @foreach($recentBookings as $booking)
                                        <tr>
                                            <td><a
                                                    href="{{ route('admin.bookings.show', $booking->id) }}">#{{ $booking->booking_number }}</a>
                                            </td>
                                            <td>{{ $booking->customer_name }}</td>
                                            <td>{{ $booking->vehicle_name }}</td>
                                            <td>
                                                @if($booking->status == 'confirmed')
                                                    <span class="badge badge-success">Confirmed</span>
                                                @elseif($booking->status == 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif($booking->status == 'cancelled')
                                                    <span class="badge badge-danger">Cancelled</span>
                                                @elseif($booking->status == 'completed')
                                                    <span class="badge badge-primary">Completed</span>
                                                @endif
                                            </td>
                                            <td>${{ number_format($booking->total_amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center">No recent bookings found</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary">View All
                        Bookings</a>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div class="activity-feed">
                        @if(isset($recentActivities) && count($recentActivities) > 0)
                            @foreach($recentActivities as $activity)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        @if($activity->type == 'booking')
                                            <i class="fas fa-book"></i>
                                        @elseif($activity->type == 'customer')
                                            <i class="fas fa-user"></i>
                                        @elseif($activity->type == 'vehicle')
                                            <i class="fas fa-car"></i>
                                        @elseif($activity->type == 'admin')
                                            <i class="fas fa-user-shield"></i>
                                        @else
                                            <i class="fas fa-bell"></i>
                                        @endif
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">{{ $activity->title }}</div>
                                        <div class="activity-time">{{ $activity->created_at->diffForHumans() }}</div>
                                        <div class="activity-text">{{ $activity->description }}</div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <p class="text-muted">No recent activities found</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.activities.index') }}" class="btn btn-sm btn-outline-primary">View All
                        Activities</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Chart
        document.addEventListener('DOMContentLoaded', function () {
            // Default empty data for charts
            const defaultMonthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const defaultChartData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            const defaultSourceLabels = ['Website', 'Phone', 'Walk-in', 'Partner', 'Other'];
            const defaultSourceData = [0, 0, 0, 0, 0];

            // Initialize Revenue Chart
            const revenueChartEl = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueChartEl, {
                type: 'line',
                data: {
                    labels: defaultMonthLabels,
                    datasets: [{
                        label: 'Revenue',
                        backgroundColor: 'rgba(0, 160, 227, 0.1)',
                        borderColor: '#00A0E3',
                        borderWidth: 2,
                        pointBackgroundColor: '#00A0E3',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#00A0E3',
                        data: defaultChartData,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '$' + value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '$' + context.raw;
                                }
                            }
                        }
                    }
                }
            });

            // Booking Sources Chart
            const bookingSourcesChartEl = document.getElementById('bookingSourcesChart').getContext('2d');
            const bookingSourcesChart = new Chart(bookingSourcesChartEl, {
                type: 'doughnut',
                data: {
                    labels: defaultSourceLabels,
                    datasets: [{
                        data: defaultSourceData,
                        backgroundColor: [
                            '#00A0E3',
                            '#FF9D2E',
                            '#2ECC71',
                            '#F39C12',
                            '#E74C3C'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '70%'
                }
            });

            // Handle chart period change
            document.getElementById('revenue-chart-period').addEventListener('change', function (e) {
                const period = e.target.value;

                // In a real implementation, this would fetch data from the server
                // Since the backend data is commented out, we'll just update with dummy data

                let labels = [];
                let data = [];

                if (period === 'week') {
                    labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                    data = [0, 0, 0, 0, 0, 0, 0];
                } else if (period === 'month') {
                    labels = defaultMonthLabels;
                    data = defaultChartData;
                } else if (period === 'year') {
                    const currentYear = new Date().getFullYear();
                    labels = [currentYear - 4, currentYear - 3, currentYear - 2, currentYear - 1, currentYear];
                    data = [0, 0, 0, 0, 0];
                }

                revenueChart.data.labels = labels;
                revenueChart.data.datasets[0].data = data;
                revenueChart.update();

                // In production, this would be the API call:
                /*
                fetch(`/admin/dashboard/chart-data?period=${period}`)
                    .then(response => response.json())
                    .then(data => {
                        revenueChart.data.labels = data.labels;
                        revenueChart.data.datasets[0].data = data.data;
                        revenueChart.update();
                    });
                */
            });
        });
    </script>
@endpush
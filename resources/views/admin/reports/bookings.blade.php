@extends('admin.layouts.master')

@section('title', 'Bookings Report')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Bookings Report</h6>
                            </div>

                            <!-- Bookings Chart -->
                            <div class="row px-3">
                                <div class="col-12">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0">
                                            <h6>Bookings Over Time</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="chart">
                                                <canvas id="booking-chart" class="chart-canvas" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Charts -->
                            <div class="row px-3">
                                <div class="col-lg-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0">
                                            <h6>Booking Status Breakdown</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="chart">
                                                <canvas id="status-chart" class="chart-canvas" height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="card mb-4">
                                        <div class="card-header pb-0">
                                            <h6>Booking Time Distribution</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="chart">
                                                <canvas id="time-distribution-chart" class="chart-canvas"
                                                    height="300"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Popular Cars -->
                            <div class="row px-3 mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header pb-0">
                                            <h6>Most Popular Cars</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                                Car</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                                License Plate</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                                Bookings Count</th>
                                                            <th
                                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                                % of Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($popularCars as $car)
                                                            <tr>
                                                                <td>
                                                                    <div class="d-flex px-2 py-1">
                                                                        <div>
                                                                            <i
                                                                                class="fas fa-car text-primary text-gradient me-2"></i>
                                                                        </div>
                                                                        <div class="d-flex flex-column justify-content-center">
                                                                            <h6 class="mb-0 text-sm">{{ $car->brand_name }}
                                                                                {{ $car->model }}</h6>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <p class="text-xs font-weight-bold mb-0">
                                                                        {{ $car->matricule }}</p>
                                                                </td>
                                                                <td>
                                                                    <p class="text-xs font-weight-bold mb-0">
                                                                        {{ $car->booking_count }}</p>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="text-xs font-weight-bold me-2">
                                                                            {{ number_format(($car->booking_count / $stats['total_bookings']) * 100, 1) }}%
                                                                        </span>
                                                                        <div>
                                                                            <div class="progress">
                                                                                <div class="progress-bar bg-gradient-info"
                                                                                    role="progressbar"
                                                                                    aria-valuenow="{{ ($car->booking_count / $stats['total_bookings']) * 100 }}"
                                                                                    aria-valuemin="0" aria-valuemax="100"
                                                                                    style="width: {{ ($car->booking_count / $stats['total_bookings']) * 100 }}%;">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <form id="exportForm" action="{{ route('admin.reports.export-bookings') }}" method="get">
                                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn bg-gradient-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-download"></i> Export
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><button type="submit" name="format" value="csv"
                                                    class="dropdown-item">CSV</button></li>
                                            <li><button type="submit" name="format" value="excel"
                                                    class="dropdown-item">Excel</button></li>
                                            <li><button type="submit" name="format" value="pdf"
                                                    class="dropdown-item">PDF</button></li>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <!-- Date filter -->
                        <div class="p-3">
                            <form id="dateRangeForm" action="{{ route('admin.reports.bookings') }}" method="get"
                                class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date" class="form-control-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="{{ $startDate }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date" class="form-control-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="{{ $endDate }}">
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn bg-gradient-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <button type="button" class="btn bg-gradient-info ms-2" id="quick-filter">
                                        <i class="fas fa-clock"></i> Quick Filters
                                    </button>
                                </div>
                            </form>

                            <!-- Quick filter dropdown -->
                            <div class="quick-filter-menu d-none" id="quick-filter-menu">
                                <div class="card shadow-sm">
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-days="7">Last 7 days</a>
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-days="30">Last 30 days</a>
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-days="90">Last 90 days</a>
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-period="this-month">This month</a>
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-period="last-month">Last month</a>
                                        <a href="#" class="list-group-item list-group-item-action quick-filter-item"
                                            data-period="this-year">This year</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row px-3">
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Bookings
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['total_bookings']) }}
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span
                                                            class="{{ $stats['percent_change'] >= 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                                            {{ $stats['percent_change'] >= 0 ? '+' : '' }}{{ number_format($stats['percent_change'], 2) }}%
                                                        </span>
                                                        vs previous period
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                                    <i class="fas fa-calendar-check text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Daily Average
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['daily_average'], 1) }}
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-success text-sm font-weight-bolder">
                                                            <i class="fa fa-calculator"></i>
                                                        </span>
                                                        Over {{ $stats['days_count'] }} days
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                                    <i class="fas fa-chart-line text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Completion Rate
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['completion_rate'], 1) }}%
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-warning text-sm font-weight-bolder">
                                                            {{ number_format($stats['cancellation_rate'], 1) }}%
                                                        </span>
                                                        cancellation rate
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                                    <i class="fas fa-percent text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Avg. Booking
                                                        Value</p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['avg_booking_value'], 2) }} MAD
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-muted text-sm">
                                                            <i class="fa fa-tag"></i>
                                                        </span>
                                                        Per booking
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                                    <i class="fas fa-money-bill-wave text-lg opacity-10"
                                                        aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .quick-filter-menu {
            position: absolute;
            z-index: 1000;
            width: 200px;
            margin-top: 0.5rem;
        }

        .progress {
            height: 8px;
            border-radius: 10px;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Bookings Chart
            const bookingData = @json($bookingData);
            const bookingChart = new Chart(document.getElementById('booking-chart'), {
                type: 'line',
                data: {
                    labels: bookingData.map(item => item.date),
                    datasets: [{
                        label: 'Bookings Count',
                        data: bookingData.map(item => item.count),
                        borderColor: '#5e72e4',
                        backgroundColor: 'rgba(94, 114, 228, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            // Status Chart
            const statusData = @json($statusBreakdown);
            const statusLabels = statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
            const statusCounts = statusData.map(item => item.count);
            const statusColors = [
                'rgba(45, 206, 137, 0.8)',   // completed
                'rgba(94, 114, 228, 0.8)',   // confirmed
                'rgba(251, 99, 64, 0.8)',    // cancelled
                'rgba(17, 205, 239, 0.8)',   // pending
                'rgba(245, 54, 92, 0.8)',    // in_progress
                'rgba(130, 214, 22, 0.8)'    // no_show
            ];

            const statusChart = new Chart(document.getElementById('status-chart'), {
                type: 'pie',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: statusColors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Time Distribution Chart
            const timeData = @json($timeDistribution);
            const timeChart = new Chart(document.getElementById('time-distribution-chart'), {
                type: 'doughnut',
                data: {
                    labels: timeData.map(item => item.name),
                    datasets: [{
                        data: timeData.map(item => item.count),
                        backgroundColor: [
                            'rgba(251, 207, 51, 0.8)',  // Morning
                            'rgba(45, 206, 137, 0.8)',  // Afternoon
                            'rgba(17, 205, 239, 0.8)',  // Evening
                            'rgba(94, 114, 228, 0.8)'   // Night
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Quick filter functionality
            const quickFilterBtn = document.getElementById('quick-filter');
            const quickFilterMenu = document.getElementById('quick-filter-menu');
            const dateRangeForm = document.getElementById('dateRangeForm');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            quickFilterBtn.addEventListener('click', function () {
                quickFilterMenu.classList.toggle('d-none');
            });

            document.addEventListener('click', function (event) {
                if (!quickFilterBtn.contains(event.target) && !quickFilterMenu.contains(event.target)) {
                    quickFilterMenu.classList.add('d-none');
                }
            });

            document.querySelectorAll('.quick-filter-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();

                    const today = new Date();
                    let startDate = new Date();
                    let endDate = new Date();

                    if (this.dataset.days) {
                        const days = parseInt(this.dataset.days);
                        startDate.setDate(today.getDate() - days);
                        endDate = today;
                    } else if (this.dataset.period) {
                        switch (this.dataset.period) {
                            case 'this-month':
                                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                                endDate = today;
                                break;
                            case 'last-month':
                                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                                break;
                            case 'this-year':
                                startDate = new Date(today.getFullYear(), 0, 1);
                                endDate = today;
                                break;
                        }
                    }

                    startDateInput.value = startDate.toISOString().split('T')[0];
                    endDateInput.value = endDate.toISOString().split('T')[0];

                    dateRangeForm.submit();
                });
            });
        });
    </script>
@endpush
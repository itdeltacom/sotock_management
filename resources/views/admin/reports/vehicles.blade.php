@extends('admin.layouts.master')

@section('title', 'Vehicles Report')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Vehicles Report</h6>
                            </div>
                            <div class="col-6 text-end">
                                <form id="exportForm" action="{{ route('admin.reports.export-vehicles') }}" method="get">
                                    @csrf
                                    <input type="hidden" name="start_date" value="{{ $startDate ?? now()->subDays(30)->format('Y-m-d') }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate ?? now()->format('Y-m-d') }}">
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
                            <form id="dateRangeForm" action="{{ route('admin.reports.vehicles') }}" method="get"
                                class="row">
                                @csrf
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="start_date" class="form-control-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="{{ $startDate ?? now()->subDays(30)->format('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="end_date" class="form-control-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="{{ $endDate ?? now()->format('Y-m-d') }}"
                                            required>
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
                                <div class="card card-stats">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Vehicles
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['total_vehicles'] ?? 0) }}
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-success text-sm font-weight-bolder">
                                                            {{ number_format($stats['active_vehicles'] ?? 0) }}
                                                        </span>
                                                        active vehicles
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                                    <i class="fas fa-car text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card card-stats">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Maintenance Cost
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['maintenance_cost'] ?? 0, 2) }} MAD
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-info text-sm font-weight-bolder">
                                                            {{ number_format($stats['maintenance_count'] ?? 0) }}
                                                        </span>
                                                        maintenance records
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                                    <i class="fas fa-tools text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card card-stats">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Avg. per Vehicle
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['bookings_per_vehicle'] ?? 0, 1) }}
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-warning text-sm font-weight-bolder">
                                                            {{ number_format($stats['revenue_per_vehicle'] ?? 0, 2) }} MAD
                                                        </span>
                                                        revenue per vehicle
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                                    <i class="fas fa-chart-bar text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 mb-4">
                                <div class="card card-stats">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Distance</p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['total_distance'] ?? 0) }} km
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-muted text-sm">
                                                            {{ number_format($stats['average_distance'] ?? 0) }} km
                                                        </span>
                                                        per vehicle
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                                    <i class="fas fa-road text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Utilization Chart -->
                        <div class="row px-3">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header pb-0">
                                        <h6>Vehicle Utilization</h6>
                                        <p class="text-sm mb-0">
                                            <i class="fa fa-info-circle text-info me-1"></i>
                                            Showing percentage of days that vehicles were rented during the selected period
                                        </p>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="utilization-chart" class="chart-canvas" height="300"></canvas>
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
                                        <h6>Maintenance Costs by Vehicle</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="maintenance-chart" class="chart-canvas" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header pb-0">
                                        <h6>Revenue by Vehicle Category</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="category-revenue-chart" class="chart-canvas" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mileage Data -->
                        <div class="row px-3 mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <h6>Vehicle Mileage Data</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Vehicle</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            License Plate</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Current Mileage</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Distance in Period</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Bookings Count</th>
                                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Avg. per Booking</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($mileageData as $item)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex px-2 py-1">
                                                                    <div>
                                                                        <i class="fas fa-car text-primary text-gradient me-2"></i>
                                                                    </div>
                                                                    <div class="d-flex flex-column justify-content-center">
                                                                        <h6 class="mb-0 text-sm">{{ $item->brand_name ?? 'N/A' }}
                                                                            {{ $item->model ?? '' }}</h6>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $item->matricule ?? 'N/A' }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ number_format($item->mileage ?? 0) }} km</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ number_format($item->total_distance ?? 0) }} km</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $item->booking_count ?? 0 }}</p>
                                                            </td>
                                                            <td>
                                                                <p class="text-xs font-weight-bold mb-0">
                                                                    {{ $item->booking_count > 0 ? number_format($item->total_distance / $item->booking_count) : 0 }}
                                                                    km
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">
                                                                No mileage data available
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
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
        .card-stats{
            height: 100%;
        }
        .progress {
            height: 8px;
            border-radius: 10px;
        }

        .chart-canvas {
            max-height: 300px;
        }

        .form-control:invalid {
            border-color: #dc3545;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            'use strict';

            // Initialize Charts
            function initializeCharts() {
                try {
                    // Vehicle Utilization Chart
                    const utilizationData = @json($vehicleUtilization ?? []);
                    const utilizationChart = new Chart(document.getElementById('utilization-chart'), {
                        type: 'bar',
                        data: {
                            labels: utilizationData.map(item => `${item.car?.brand_name ?? 'N/A'} ${item.car?.model ?? ''}`),
                            datasets: [{
                                label: 'Utilization (%)',
                                data: utilizationData.map(item => item.utilization ?? 0),
                                backgroundColor: utilizationData.map(item => {
                                    const utilization = item.utilization ?? 0;
                                    if (utilization > 80) return 'rgba(45, 206, 137, 0.8)';
                                    if (utilization > 50) return 'rgba(94, 114, 228, 0.8)';
                                    if (utilization > 30) return 'rgba(251, 99, 64, 0.8)';
                                    return 'rgba(245, 54, 92, 0.8)';
                                }),
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
                                            const item = utilizationData[context.dataIndex] ?? {};
                                            return [
                                                `Utilization: ${(item.utilization ?? 0).toFixed(1)}%`,
                                                `Rented days: ${item.rented_days ?? 0} of ${item.total_days ?? 0} days`
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function (value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Maintenance Costs Chart
                    const maintenanceData = @json($maintenanceCosts ?? []);
                    const maintenanceChart = new Chart(document.getElementById('maintenance-chart'), {
                        type: 'bar',
                        data: {
                            labels: maintenanceData.map(item => `${item.brand_name ?? 'N/A'} ${item.model ?? ''}`),
                            datasets: [{
                                label: 'Maintenance Cost (MAD)',
                                data: maintenanceData.map(item => item.total_cost ?? 0),
                                backgroundColor: 'rgba(245, 54, 92, 0.8)',
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
                                            const item = maintenanceData[context.dataIndex] ?? {};
                                            return [
                                                `Cost: ${(item.total_cost ?? 0).toLocaleString()} MAD`,
                                                `Maintenance count: ${item.maintenance_count ?? 0}`
                                            ];
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return value.toLocaleString() + ' MAD';
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Category Revenue Chart
                    const categoryData = @json($categoryRevenue ?? []);
                    const categoryRevenueChart = new Chart(document.getElementById('category-revenue-chart'), {
                        type: 'doughnut',
                        data: {
                            labels: categoryData.map(item => item.name ?? 'N/A'),
                            datasets: [{
                                data: categoryData.map(item => item.total_revenue ?? 0),
                                backgroundColor: [
                                    'rgba(94, 114, 228, 0.8)',
                                    'rgba(45, 206, 137, 0.8)',
                                    'rgba(251, 99, 64, 0.8)',
                                    'rgba(17, 205, 239, 0.8)',
                                    'rgba(245, 54, 92, 0.8)',
                                    'rgba(251, 207, 51, 0.8)'
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
                                            const label = context.label || 'N/A';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = Math.round((value / total) * 100);
                                            const item = categoryData[context.dataIndex] ?? {};
                                            return [
                                                `${label}: ${value.toLocaleString()} MAD (${percentage}%)`,
                                                `Bookings: ${item.booking_count ?? 0}`
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (error) {
                    console.error('Error initializing charts:', error);
                }
            }

            // Quick Filter Functionality
            function initializeQuickFilters() {
                const quickFilterBtn = document.getElementById('quick-filter');
                const quickFilterMenu = document.getElementById('quick-filter-menu');
                const dateRangeForm = document.getElementById('dateRangeForm');
                const startDateInput = document.getElementById('start_date');
                const endDateInput = document.getElementById('end_date');

                if (!quickFilterBtn || !quickFilterMenu || !dateRangeForm || !startDateInput || !endDateInput) {
                    console.error('Required elements for quick filters not found');
                    return;
                }

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

                        try {
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
                        } catch (error) {
                            console.error('Error processing quick filter:', error);
                        }
                    });
                });
            }

            // Form Validation
            function initializeFormValidation() {
                const dateRangeForm = document.getElementById('dateRangeForm');
                const exportForm = document.getElementById('exportForm');

                if (dateRangeForm) {
                    dateRangeForm.addEventListener('submit', function (e) {
                        const startDate = document.getElementById('start_date').value;
                        const endDate = document.getElementById('end_date').value;

                        if (!startDate || !endDate) {
                            e.preventDefault();
                            alert('Please select both start and end dates');
                            return;
                        }

                        if (new Date(startDate) > new Date(endDate)) {
                            e.preventDefault();
                            alert('Start date cannot be after end date');
                            return;
                        }
                    });
                }

                if (exportForm) {
                    exportForm.addEventListener('submit', function (e) {
                        const startDate = this.querySelector('input[name="start_date"]').value;
                        const endDate = this.querySelector('input[name="end_date"]').value;

                        if (!startDate || !endDate) {
                            e.preventDefault();
                            alert('Date range is required for export');
                            return;
                        }
                    });
                }
            }

            // Initialize everything when DOM is loaded
            document.addEventListener('DOMContentLoaded', function () {
                initializeCharts();
                initializeQuickFilters();
                initializeFormValidation();
            });
        })();
    </script>
@endpush
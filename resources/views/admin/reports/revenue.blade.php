@extends('admin.layouts.master')

@section('title', 'Revenue Report')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 p-3">
                        <div class="row">
                            <div class="col-6 d-flex align-items-center">
                                <h6 class="mb-0">Revenue Report</h6>
                            </div>
                            <div class="col-6 text-end">
                                <form id="exportForm" action="{{ route('admin.reports.export-revenue') }}" method="get">
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
                            <form id="dateRangeForm" action="{{ route('admin.reports.revenue') }}" method="get" class="row">
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Revenue
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['total_revenue'], 2) }} MAD
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
                                                    <i class="fas fa-money-bill text-lg opacity-10" aria-hidden="true"></i>
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
                                                        {{ number_format($stats['daily_average'], 2) }} MAD
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Payments Count
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['payments_count']) }}
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-info text-sm font-weight-bolder">
                                                            {{ number_format($stats['avg_payment'], 2) }} MAD
                                                        </span>
                                                        avg. payment
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                                    <i class="fas fa-file-invoice-dollar text-lg opacity-10"
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
                                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Previous Period
                                                    </p>
                                                    <h5 class="font-weight-bolder">
                                                        {{ number_format($stats['previous_revenue'], 2) }} MAD
                                                    </h5>
                                                    <p class="mb-0">
                                                        <span class="text-muted text-sm">
                                                            <i class="fa fa-calendar"></i>
                                                        </span>
                                                        Same duration
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div
                                                    class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                                    <i class="fas fa-history text-lg opacity-10" aria-hidden="true"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Chart -->
                        <div class="row px-3">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header pb-0">
                                        <h6>Revenue Over Time</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="revenue-chart" class="chart-canvas" height="300"></canvas>
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
                                        <h6>Top Earning Cars</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="top-cars-chart" class="chart-canvas" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card mb-4">
                                    <div class="card-header pb-0">
                                        <h6>Payment Method Distribution</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="chart">
                                            <canvas id="payment-methods-chart" class="chart-canvas" height="300"></canvas>
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
    </style>
@endpush

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue Chart
            const revenueData = @json($revenueData);
            const revenueChart = new Chart(document.getElementById('revenue-chart'), {
                type: 'line',
                data: {
                    labels: revenueData.map(item => item.date),
                    datasets: [{
                        label: 'Revenue (MAD)',
                        data: revenueData.map(item => item.total),
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
                            intersect: false,
                            callbacks: {
                                label: function (context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('fr-MA', {
                                            style: 'currency',
                                            currency: 'MAD',
                                            minimumFractionDigits: 2
                                        }).format(context.parsed.y);
                                    }
                                    return label;
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

            // Top Cars Chart
            const topCarsData = @json($topCars);
            const topCarsChart = new Chart(document.getElementById('top-cars-chart'), {
                type: 'bar',
                data: {
                    labels: topCarsData.map(car => `${car.brand_name} ${car.model}`),
                    datasets: [{
                        label: 'Revenue (MAD)',
                        data: topCarsData.map(car => car.total_revenue),
                        backgroundColor: [
                            'rgba(94, 114, 228, 0.8)',
                            'rgba(45, 206, 137, 0.8)',
                            'rgba(251, 99, 64, 0.8)',
                            'rgba(17, 205, 239, 0.8)',
                            'rgba(245, 54, 92, 0.8)'
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
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('fr-MA', {
                                            style: 'currency',
                                            currency: 'MAD',
                                            minimumFractionDigits: 2
                                        }).format(context.parsed.y);
                                    }
                                    return label;
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

            // Payment Methods Chart
            const paymentMethodsData = @json($paymentMethods);
            const paymentMethodsChart = new Chart(document.getElementById('payment-methods-chart'), {
                type: 'doughnut',
                data: {
                    labels: paymentMethodsData.map(item => item.payment_method.replace('_', ' ').toUpperCase()),
                    datasets: [{
                        data: paymentMethodsData.map(item => item.total),
                        backgroundColor: [
                            'rgba(45, 206, 137, 0.8)',
                            'rgba(94, 114, 228, 0.8)',
                            'rgba(251, 99, 64, 0.8)',
                            'rgba(17, 205, 239, 0.8)'
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
                                    return `${label}: ${value.toLocaleString()} MAD (${percentage}%)`;
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
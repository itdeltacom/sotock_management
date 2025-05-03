@extends('admin.layouts.master')
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Revenue Cards -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Revenue</p>
                                    <h5 class="font-weight-bolder">
                                        {{ number_format($totalRevenue, 2) }} MAD
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            {{ $thisMonthRevenue > 0 ? number_format(($thisMonthRevenue / $totalRevenue) * 100, 2) : 0 }}%
                                        </span>
                                        from this month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Bookings</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $totalBookings }}
                                    </h5>
                                    <p class="mb-0">
                                        <span
                                            class="{{ $thisMonthBookings > 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                            {{ $thisMonthBookings }}
                                        </span>
                                        this month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                    <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vehicles Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Vehicles</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $totalVehicles }}
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            {{ $availableVehicles }}
                                        </span>
                                        available now
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-bus-front-12 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Card -->
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Activity</p>
                                    <h5 class="font-weight-bolder">
                                        {{ $activityStats['totalMonth'] }}
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">
                                            {{ $activityStats['loginCount'] }}
                                        </span>
                                        logins this month
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                    <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Car Maintenance Alerts Section -->
        @if(isset($maintenance_alerts) && ($maintenance_alerts['overdue']['count'] > 0 || $maintenance_alerts['due_this_week']['count'] > 0 || $maintenance_alerts['coming_up']['count'] > 0))
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-6 d-flex align-items-center">
                                    <h6 class="mb-0">Vehicle Maintenance Alerts</h6>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="{{ route('admin.cars.maintenance.due-soon') }}"
                                        class="btn btn-outline-primary btn-sm mb-0">
                                        View All
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                @if($maintenance_alerts['overdue']['count'] > 0)
                                    <div class="col-md-4 mb-md-0 mb-4">
                                        <div class="card card-body border card-plain border-danger mb-3">
                                            <h6 class="text-danger mb-0">Overdue</h6>
                                            <span class="text-sm font-weight-bolder">{{ $maintenance_alerts['overdue']['count'] }}
                                                item(s)</span>
                                        </div>

                                        <ul class="list-group">
                                            @foreach($maintenance_alerts['overdue']['items'] as $alert)
                                                <li
                                                    class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-danger shadow text-center">
                                                            <i class="fas fa-exclamation-triangle text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $alert['car_name'] }}</h6>
                                                            <span class="text-xs">{{ $alert['maintenance_type'] }}</span>
                                                            @if(isset($alert['days_text']))
                                                                <span class="text-xs text-danger">{{ $alert['days_text'] }}</span>
                                                            @endif
                                                            @if(isset($alert['km_text']))
                                                                <span class="text-xs text-danger">{{ $alert['km_text'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $alert['url'] }}"
                                                            class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                            <i class="fas fa-chevron-right text-xs"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($maintenance_alerts['due_this_week']['count'] > 0)
                                    <div class="col-md-4 mb-md-0 mb-4">
                                        <div class="card card-body border card-plain border-warning mb-3">
                                            <h6 class="text-warning mb-0">Due This Week</h6>
                                            <span
                                                class="text-sm font-weight-bolder">{{ $maintenance_alerts['due_this_week']['count'] }}
                                                item(s)</span>
                                        </div>

                                        <ul class="list-group">
                                            @foreach($maintenance_alerts['due_this_week']['items'] as $alert)
                                                <li
                                                    class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="icon icon-shape icon-sm me-3 bg-gradient-warning shadow text-center">
                                                            <i class="fas fa-clock text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $alert['car_name'] }}</h6>
                                                            <span class="text-xs">{{ $alert['maintenance_type'] }}</span>
                                                            @if(isset($alert['days_text']))
                                                                <span class="text-xs text-warning">{{ $alert['days_text'] }}</span>
                                                            @endif
                                                            @if(isset($alert['km_text']))
                                                                <span class="text-xs text-warning">{{ $alert['km_text'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $alert['url'] }}"
                                                            class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                            <i class="fas fa-chevron-right text-xs"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if($maintenance_alerts['coming_up']['count'] > 0)
                                    <div class="col-md-4">
                                        <div class="card card-body border card-plain border-info mb-3">
                                            <h6 class="text-info mb-0">Coming Up (15 Days)</h6>
                                            <span class="text-sm font-weight-bolder">{{ $maintenance_alerts['coming_up']['count'] }}
                                                item(s)</span>
                                        </div>

                                        <ul class="list-group">
                                            @foreach($maintenance_alerts['coming_up']['items'] as $alert)
                                                <li
                                                    class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                                            <i class="fas fa-bell text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $alert['car_name'] }}</h6>
                                                            <span class="text-xs">{{ $alert['maintenance_type'] }}</span>
                                                            @if(isset($alert['days_text']))
                                                                <span class="text-xs text-info">{{ $alert['days_text'] }}</span>
                                                            @endif
                                                            @if(isset($alert['km_text']))
                                                                <span class="text-xs text-info">{{ $alert['km_text'] }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <a href="{{ $alert['url'] }}"
                                                            class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                            <i class="fas fa-chevron-right text-xs"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mt-4">
            <!-- Main Chart -->
            <div class="col-lg-7 mb-lg-0 mb-4">
                <div class="card z-index-2 h-100">
                    <div class="card-header pb-0 pt-3 bg-transparent">
                        <div class="d-flex justify-content-between">
                            <h6 class="text-capitalize">Rental Performance</h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="chartPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    This Month
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="chartPeriodDropdown">
                                    <li><a class="dropdown-item chart-period" href="#" data-period="week">This Week</a></li>
                                    <li><a class="dropdown-item chart-period" href="#" data-period="month">This Month</a>
                                    </li>
                                    <li><a class="dropdown-item chart-period" href="#" data-period="year">This Year</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart">
                            <canvas id="rentalChart" class="chart-canvas" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">Recent Bookings</h6>
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary">View
                                All</a>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group">
                            @foreach($recentBookings as $booking)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div
                                            class="icon icon-shape icon-sm me-3 bg-gradient-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : 'dark') }} shadow text-center">
                                            <i class="ni ni-calendar-grid-58 text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm">{{ $booking->car->brand_name }}
                                                {{ $booking->car->model }}</h6>
                                            <span class="text-xs">
                                                {{ $booking->user->name }} -
                                                {{ Carbon\Carbon::parse($booking->start_date)->format('M d') }} to
                                                {{ Carbon\Carbon::parse($booking->end_date)->format('M d') }}
                                            </span>
                                            <span
                                                class="text-xs font-weight-bold text-{{ $booking->status == 'confirmed' ? 'success' : ($booking->status == 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($booking->status) }} - {{ number_format($booking->total_amount, 2) }}
                                                MAD
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}"
                                            class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                            <i class="fas fa-chevron-right text-xs"></i>
                                        </a>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Activity Types -->
            <div class="col-lg-5 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Activity Types</h6>
                    </div>
                    <div class="card-body p-3">
                        <ul class="list-group">
                            @foreach($activityTypes as $activity)
                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                            <i
                                                class="ni ni-{{ $activity->type == 'login' ? 'key-25' : ($activity->type == 'logout' ? 'user-run' : 'single-copy-04') }} text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm">{{ ucfirst($activity->type) }}</h6>
                                            <span class="text-xs">{{ $activity->count }} activities</span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <span
                                            class="text-xs font-weight-bold">{{ number_format(($activity->count / $activityStats['totalMonth']) * 100, 1) }}%</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Browser & Location Stats -->
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header pb-0 p-3">
                        <div class="d-flex justify-content-between">
                            <h6 class="mb-0">System Usage</h6>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-sm mb-0 text-center">Top Browsers</h6>
                                <div class="chart">
                                    <canvas id="browserChart" height="200"></canvas>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-sm mb-0 text-center">Locations</h6>
                                <div class="chart">
                                    <canvas id="locationChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer pt-3">
            <div class="container-fluid">
                <div class="row align-items-center justify-content-lg-between">
                    <div class="col-lg-6 mb-lg-0 mb-4">
                        <div class="copyright text-center text-sm text-muted text-lg-start">
                            ©
                            <script>
                                document.write(new Date().getFullYear())
                            </script>,
                            Moroccan Car Rental Management System
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                            <li class="nav-item">
                                <a href="#" class="nav-link text-muted">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link text-muted">Bookings</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link text-muted">Vehicles</a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link pe-0 text-muted">Reports</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
@endsection

@push('js')
    <script src="{{ asset('admin/assets/js/plugins/chartjs.min.js') }}"></script>
    <script>
        // Main Rental Chart
        var rentalCtx = document.getElementById("rentalChart").getContext("2d");
        var rentalChart;

        // Browser Chart
        var browserCtx = document.getElementById("browserChart").getContext("2d");
        var browserChart;

        // Location Chart
        var locationCtx = document.getElementById("locationChart").getContext("2d");
        var locationChart;

        // Load chart data
        function loadChartData(period = 'month') {
            $.ajax({
                url: "{{ route('admin.dashboard.chart-data') }}",
                method: 'GET',
                data: { period: period },
                success: function (response) {
                    updateRentalChart(response);
                }
            });
        }

        // Update rental chart with new data
        function updateRentalChart(data) {
            if (rentalChart) {
                rentalChart.destroy();
            }

            var gradientStroke1 = rentalCtx.createLinearGradient(0, 230, 0, 50);
            gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
            gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
            gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

            rentalChart = new Chart(rentalCtx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Bookings",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#5e72e4",
                            backgroundColor: gradientStroke1,
                            borderWidth: 3,
                            fill: true,
                            data: data.bookingData,
                            yAxisID: 'y'
                        },
                        {
                            label: "Revenue (MAD)",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#2dce89",
                            backgroundColor: "rgba(45, 206, 137, 0.1)",
                            borderWidth: 3,
                            fill: true,
                            data: data.revenueData,
                            yAxisID: 'y1'
                        }
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Bookings'
                            },
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                padding: 10,
                                color: '#fbfbfb',
                                font: {
                                    size: 11,
                                    family: "Open Sans",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Revenue (MAD)'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                display: true,
                                padding: 10,
                                color: '#fbfbfb',
                                font: {
                                    size: 11,
                                    family: "Open Sans",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                color: '#ccc',
                                padding: 20,
                                font: {
                                    size: 11,
                                    family: "Open Sans",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                    },
                },
            });
        }

        // Initialize browser chart
        function initBrowserChart() {
            var browserData = @json($browserStats);
            var browserLabels = browserData.map(item => item.browser);
            var browserCounts = browserData.map(item => item.count);

            browserChart = new Chart(browserCtx, {
                type: "doughnut",
                data: {
                    labels: browserLabels,
                    datasets: [{
                        label: "Browsers",
                        weight: 9,
                        cutout: "60%",
                        tension: 0.9,
                        pointRadius: 2,
                        borderWidth: 2,
                        backgroundColor: [
                            '#5e72e4',
                            '#2dce89',
                            '#fb6340',
                            '#f5365c',
                            '#5603ad'
                        ],
                        data: browserCounts,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    cutout: "70%",
                },
            });
        }

        // Initialize location chart
        function initLocationChart() {
            var locationData = @json($locationStats);
            var locationLabels = locationData.map(item => item.location);
            var locationCounts = locationData.map(item => item.count);

            locationChart = new Chart(locationCtx, {
                type: "pie",
                data: {
                    labels: locationLabels,
                    datasets: [{
                        label: "Locations",
                        weight: 9,
                        cutout: "60%",
                        tension: 0.9,
                        pointRadius: 2,
                        borderWidth: 2,
                        backgroundColor: [
                            '#5e72e4',
                            '#2dce89',
                            '#fb6340',
                            '#f5365c',
                            '#5603ad'
                        ],
                        data: locationCounts,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                },
            });
        }

        // Document ready
        $(document).ready(function () {
            // Load initial chart data
            loadChartData();

            // Initialize other charts
            initBrowserChart();
            initLocationChart();

            // Chart period selector
            $('.chart-period').click(function (e) {
                e.preventDefault();
                var period = $(this).data('period');
                $('#chartPeriodDropdown').text($(this).text());
                loadChartData(period);
            });
        });
    </script>
@endpush
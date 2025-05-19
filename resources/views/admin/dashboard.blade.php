@extends('admin.layouts.master')
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <!-- Sales Value Card -->
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stats-cards">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Sales</p>
                                        <h5 class="font-weight-bolder">
                                            {{ number_format($totalSalesValue, 2) }} MAD
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-success text-sm font-weight-bolder">
                                                {{ $totalSalesValue > 0 ? number_format(($thisMonthSalesValue / $totalSalesValue) * 100, 2) : 0 }}%
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

                <!-- Purchase Orders Card -->
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stats-cards">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Purchase Orders</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $totalPurchaseOrders }}
                                        </h5>
                                        <p class="mb-0">
                                            <span
                                                class="{{ $thisMonthPurchaseOrders > 0 ? 'text-success' : 'text-danger' }} text-sm font-weight-bolder">
                                                {{ $thisMonthPurchaseOrders }}
                                            </span>
                                            this month
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                                        <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Card -->
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card stats-cards">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Products</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $totalProducts }}
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-danger text-sm font-weight-bolder">
                                                {{ $lowStockProducts }}
                                            </span>
                                            low in stock
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                        <i class="ni ni-box-2 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Value Card -->
                <div class="col-xl-3 col-sm-6">
                    <div class="card stats-cards">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Stock Value</p>
                                        <h5 class="font-weight-bolder">
                                            {{ number_format($totalStockValue, 2) }} MAD
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-success text-sm font-weight-bolder">
                                                {{ $stockStats['incomingMonth'] }}
                                            </span>
                                            stock movements
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

            <!-- Low Stock Alerts Section -->
            @if(count($lowStockAlerts) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card mb-4 cards-dashboard">
                            <div class="card-header pb-0 p-3">
                                <div class="row">
                                    <div class="col-6 d-flex align-items-center">
                                        <h6 class="mb-0">Low Stock Alerts</h6>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="{{ route('admin.products.index') }}"
                                            class="btn btn-outline-primary btn-sm mb-0">
                                            View All Products
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="list-group">
                                            @foreach($lowStockAlerts as $alert)
                                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-danger shadow text-center">
                                                            <i class="fas fa-exclamation-triangle text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $alert['product'] }}</h6>
                                                            <span class="text-xs">Warehouse: {{ $alert['warehouse'] }}</span>
                                                            <span class="text-xs text-danger">{{ $alert['available'] }} of {{ $alert['min_stock'] }} minimum</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <div class="progress-wrapper">
                                                            <div class="progress-info">
                                                                <div class="progress-percentage">
                                                                    <span class="text-xs font-weight-bold">{{ $alert['percentage'] }}%</span>
                                                                </div>
                                                            </div>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-gradient-danger" role="progressbar" 
                                                                    aria-valuenow="{{ $alert['percentage'] }}" aria-valuemin="0" aria-valuemax="100" 
                                                                    style="width: {{ $alert['percentage'] }}%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Expiring Products Section -->
            @if(count($expiringProducts) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card mb-4 cards-dashboard">
                            <div class="card-header pb-0 p-3">
                                <div class="row">
                                    <div class="col-6 d-flex align-items-center">
                                        <h6 class="mb-0">Expiring Products</h6>
                                    </div>
                                    <div class="col-6 text-end">
                                        <a href="#" class="btn btn-outline-primary btn-sm mb-0">
                                            View All Expiring Products
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul class="list-group">
                                            @foreach($expiringProducts as $product)
                                                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-{{ $product['status'] == 'expired' ? 'danger' : ($product['status'] == 'critical' ? 'warning' : 'info') }} shadow text-center">
                                                            <i class="fas fa-calendar-alt text-white opacity-10"></i>
                                                        </div>
                                                        <div class="d-flex flex-column">
                                                            <h6 class="mb-1 text-dark text-sm">{{ $product['product'] }}</h6>
                                                            <span class="text-xs">Lot: {{ $product['lot_number'] }} - Warehouse: {{ $product['warehouse'] }}</span>
                                                            <span class="text-xs text-{{ $product['status'] == 'expired' ? 'danger' : ($product['status'] == 'critical' ? 'warning' : 'info') }}">
                                                                {{ $product['days_text'] }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex">
                                                        <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                            <i class="fas fa-chevron-right text-xs"></i>
                                                        </button>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
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
                                <h6 class="text-capitalize">Stock Performance</h6>
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
                                <canvas id="stockChart" class="chart-canvas" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header pb-0 p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Recent Orders</h6>
                                <ul class="nav nav-tabs" id="recentOrdersTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases" type="button" role="tab" aria-controls="purchases" aria-selected="true">Purchases</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-controls="sales" aria-selected="false">Sales</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="tab-content" id="recentOrdersTabContent">
                                <!-- Purchase Orders Tab -->
                                <div class="tab-pane fade show active" id="purchases" role="tabpanel" aria-labelledby="purchases-tab">
                                    <ul class="list-group">
                                        @forelse($recentPurchaseOrders as $order)
                                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                                        <i class="ni ni-cart text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-1 text-dark text-sm">{{ $order->reference_no }}</h6>
                                                        <span class="text-xs">
                                                            {{ $order->supplier->name ?? 'Unknown Supplier' }} -
                                                            {{ $order->order_date->format('M d') }}
                                                        </span>
                                                        <span
                                                            class="text-xs font-weight-bold text-{{ $order->status == 'confirmed' ? 'success' : ($order->status == 'draft' ? 'warning' : ($order->status == 'received' ? 'primary' : 'secondary')) }}">
                                                            {{ ucfirst($order->status) }} - {{ number_format($order->total_amount, 2) }}
                                                            MAD
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex">
                                                    <a href="{{ route('admin.purchase-orders.show', $order->id) }}"
                                                        class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                        <i class="fas fa-chevron-right text-xs"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item border-0 d-flex justify-content-center ps-0 mb-2 border-radius-lg">
                                                <span class="text-xs text-muted">No recent purchase orders</span>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>

                                <!-- Sales Orders Tab -->
                                <div class="tab-pane fade" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                                    <ul class="list-group">
                                        @forelse($recentSalesOrders as $order)
                                            <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                                <div class="d-flex align-items-center">
                                                    <div class="icon icon-shape icon-sm me-3 bg-gradient-success shadow text-center">
                                                        <i class="ni ni-tag text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-1 text-dark text-sm">{{ $order->reference_no }}</h6>
                                                        <span class="text-xs">
                                                            {{ $order->customer->name ?? 'Unknown Customer' }} -
                                                            {{ $order->order_date->format('M d') }}
                                                        </span>
                                                        <span
                                                            class="text-xs font-weight-bold text-{{ $order->status == 'confirmed' ? 'success' : ($order->status == 'draft' ? 'warning' : ($order->status == 'delivered' ? 'primary' : 'secondary')) }}">
                                                            {{ ucfirst($order->status) }} - {{ number_format($order->total_amount, 2) }}
                                                            MAD
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex">
                                                    <a href="{{ route('admin.sales-orders.show', $order->id) }}"
                                                        class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                        <i class="fas fa-chevron-right text-xs"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item border-0 d-flex justify-content-center ps-0 mb-2 border-radius-lg">
                                                <span class="text-xs text-muted">No recent sales orders</span>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Top Products -->
                <div class="col-lg-5 mb-lg-0 mb-4">
                    <div class="card cards-dashboard">
                        <div class="card-header pb-0 p-3">
                            <h6 class="mb-0">Top Products</h6>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-group">
                                @forelse($topProducts as $product)
                                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center">
                                                <i class="ni ni-box-2 text-white opacity-10"></i>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <h6 class="mb-1 text-dark text-sm">{{ $product->product->name ?? 'Unknown Product' }}</h6>
                                                <span class="text-xs">{{ number_format($product->total_qty, 2) }} units moved</span>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            <a href="{{ route('admin.products.show', $product->product_id) }}"
                                                class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto">
                                                <i class="fas fa-chevron-right text-xs"></i>
                                            </a>
                                        </div>
                                    </li>
                                @empty
                                    <li class="list-group-item border-0 d-flex justify-content-center ps-0 mb-2 border-radius-lg">
                                        <span class="text-xs text-muted">No product movement data available</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Stats -->
                <div class="col-lg-7">
                    <div class="card cards-dashboard">
                        <div class="card-header pb-0 p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Warehouse Statistics</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Warehouse</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Products</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Quantity</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($warehouseStats as $warehouse)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $warehouse->name }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">{{ $warehouse->product_count }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">{{ number_format($warehouse->total_quantity, 2) }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-sm font-weight-bold mb-0">{{ number_format($warehouse->total_value, 2) }} MAD</p>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <span class="text-xs text-muted">No warehouse data available</span>
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

            <footer class="footer pt-3">
                <div class="container-fluid">
                    <div class="row align-items-center justify-content-lg-between">
                        <div class="col-lg-6 mb-lg-0 mb-4">
                            <div class="copyright text-center text-sm text-muted text-lg-start">
                                ©
                                <script>
                                    document.write(new Date().getFullYear())
                                </script>,
                                Moroccan Stock Management System
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-muted">Dashboard</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-muted">Inventory</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link text-muted">Orders</a>
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
@push('css')
    <style>
        .stats-cards,
        .cards-dashboard {
            height: 100%;
        }
    </style>
@endpush
@push('js')
    <script src="{{ asset('admin/assets/js/plugins/chartjs.min.js') }}"></script>
    <script>
        // Main Stock Chart
        var stockCtx = document.getElementById("stockChart").getContext("2d");
        var stockChart;

        // Load chart data
        function loadChartData(period = 'month') {
            $.ajax({
                url: "{{ route('admin.dashboard.chart-data') }}",
                method: 'GET',
                data: { period: period },
                success: function (response) {
                    updateStockChart(response);
                }
            });
        }

        // Update stock chart with new data
        function updateStockChart(data) {
            if (stockChart) {
                stockChart.destroy();
            }

            var gradientStroke1 = stockCtx.createLinearGradient(0, 230, 0, 50);
            gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
            gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
            gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

            var gradientStroke2 = stockCtx.createLinearGradient(0, 230, 0, 50);
            gradientStroke2.addColorStop(1, 'rgba(45, 206, 137, 0.2)');
            gradientStroke2.addColorStop(0.2, 'rgba(45, 206, 137, 0.0)');
            gradientStroke2.addColorStop(0, 'rgba(45, 206, 137, 0)');

            stockChart = new Chart(stockCtx, {
                type: "line",
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: "Stock In",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#5e72e4",
                            backgroundColor: gradientStroke1,
                            borderWidth: 3,
                            fill: true,
                            data: data.stockInData,
                            yAxisID: 'y'
                        },
                        {
                            label: "Stock Out",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#fb6340",
                            backgroundColor: "rgba(251, 99, 64, 0.1)",
                            borderWidth: 3,
                            fill: true,
                            data: data.stockOutData,
                            yAxisID: 'y'
                        },
                        {
                            label: "Purchases (MAD)",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#2dce89",
                            backgroundColor: gradientStroke2,
                            borderWidth: 3,
                            fill: false,
                            data: data.purchaseData,
                            yAxisID: 'y1'
                        },
                        {
                            label: "Sales (MAD)",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#f5365c",
                            backgroundColor: "rgba(245, 54, 92, 0.1)",
                            borderWidth: 3,
                            fill: false,
                            data: data.salesData,
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
                                text: 'Movement Count'
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
                                text: 'Value (MAD)'
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

        // Document ready
        $(document).ready(function () {
            // Load initial chart data
            loadChartData();

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
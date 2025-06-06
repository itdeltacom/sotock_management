<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('admin/assets/img/apple-icon.png')}}">
    <link rel="icon" type="image/png" href="{{asset('admin/assets/img/favicon.png')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, maximum-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=no">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, minimal-ui">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, minimal-ui, maximum-scale=1">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no, minimal-ui, maximum-scale=1, user-scalable=no">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no, minimal-ui, maximum-scale=1, user-scalable=no, minimal-ui">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, shrink-to-fit=no, minimal-ui, maximum-scale=1, user-scalable=no, minimal-ui, maximum-scale=1">
    <title>
        It Delta Com Car Rental Dashboard
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- CSS Files -->
    <link id="pagestyle" href="{{asset('admin/assets/css/argon-dashboard.css?v=2.1.0')}}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @stack('css')
</head>

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-dark position-absolute w-100"></div>
    @include('admin.includes.sidebar')
    <main class="main-content position-relative border-radius-lg ">
        <!-- Navbar -->
        @include('admin.includes.navbar')
        <!-- End Navbar -->
        @yield('content')
    </main>
    <div class="fixed-plugin">
        <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
            <i class="fa fa-cog py-2"> </i>
        </a>
        <div class="card shadow-lg">
            <div class="card-header pb-0 pt-3 ">
                <div class="float-start">
                    <h5 class="mt-3 mb-0">Argon Configurator</h5>
                    <p>See our dashboard options.</p>
                </div>
                <div class="float-end mt-4">
                    <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
                        <i class="fa fa-close"></i>
                    </button>
                </div>
                <!-- End Toggle Button -->
            </div>
            <hr class="horizontal dark my-1">
            <div class="card-body pt-sm-3 pt-0 overflow-auto">
                <!-- Sidebar Backgrounds -->
                <div>
                    <h6 class="mb-0">Sidebar Colors</h6>
                </div>
                <a href="javascript:void(0)" class="switch-trigger background-color">
                    <div class="badge-colors my-2 text-start">
                        <span class="badge filter bg-gradient-primary active" data-color="primary"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-dark" data-color="dark"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-info" data-color="info"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-success" data-color="success"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-warning" data-color="warning"
                            onclick="sidebarColor(this)"></span>
                        <span class="badge filter bg-gradient-danger" data-color="danger"
                            onclick="sidebarColor(this)"></span>
                    </div>
                </a>
                <!-- Sidenav Type -->
                <div class="mt-3">
                    <h6 class="mb-0">Sidenav Type</h6>
                    <p class="text-sm">Choose between 2 different sidenav types.</p>
                </div>
                <div class="d-flex">
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2 active me-2" data-class="bg-white"
                        onclick="sidebarType(this)">White</button>
                    <button class="btn bg-gradient-primary w-100 px-3 mb-2" data-class="bg-default"
                        onclick="sidebarType(this)">Dark</button>
                </div>
                <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
                <!-- Navbar Fixed -->
                <div class="d-flex my-3">
                    <h6 class="mb-0">Navbar Fixed</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed"
                            onclick="navbarFixed(this)">
                    </div>
                </div>
                <hr class="horizontal dark my-sm-4">
                <div class="mt-2 mb-5 d-flex">
                    <h6 class="mb-0">Light / Dark</h6>
                    <div class="form-check form-switch ps-0 ms-auto my-auto">
                        <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version"
                            onclick="darkMode(this)">
                    </div>
                </div>
                <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/argon-dashboard">Free
                    Download</a>
                <a class="btn btn-outline-dark w-100"
                    href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard">View
                    documentation</a>
                <div class="w-100 text-center">
                    <a class="github-button" href="https://github.com/creativetimofficial/argon-dashboard"
                        data-icon="octicon-star" data-size="large" data-show-count="true"
                        aria-label="Star creativetimofficial/argon-dashboard on GitHub">Star</a>
                    <h6 class="mt-3">Thank you for sharing!</h6>
                    <a href="https://twitter.com/intent/tweet?text=Check%20Argon%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fargon-dashboard"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/argon-dashboard"
                        class="btn btn-dark mb-0 me-2" target="_blank">
                        <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!--   Core JS Files   -->
    <script src="{{asset('admin/assets/js/core/popper.min.js')}}"></script>
    <script src="{{asset('admin/assets/js/core/bootstrap.min.js')}}"></script>
    <script src="{{asset('admin/assets/js/plugins/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('admin/assets/js/plugins/smooth-scrollbar.min.js')}}"></script>
    <script src="{{asset('admin/assets/js/plugins/chartjs.min.js')}}"></script>
    @stack ('js')
    <script>
        // Only initialize chart if the element exists
        document.addEventListener('DOMContentLoaded', function () {
            var chartElement = document.getElementById("chart-line");
            if (chartElement) {
                var ctx1 = chartElement.getContext("2d");
                var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

                gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
                gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
                gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
                new Chart(ctx1, {
                    type: "line",
                    data: {
                        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                        datasets: [{
                            label: "Mobile apps",
                            tension: 0.4,
                            borderWidth: 0,
                            pointRadius: 0,
                            borderColor: "#5e72e4",
                            backgroundColor: gradientStroke1,
                            borderWidth: 3,
                            fill: true,
                            data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
                            maxBarThickness: 6
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
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        },
                        scales: {
                            y: {
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
        });
    </script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <script>
        /**
 * Quick Search Functionality
 */
        $(document).ready(function () {
            // Define BASE_URL dynamically based on the current URL
            // This eliminates the need for a BASE_URL variable to be defined elsewhere
            var BASE_URL = window.location.protocol + '//' + window.location.host;

            var searchTimer;
            var searchInput = $('#global-search');
            var resultsContainer = $('.quick-search-results');

            // When user types in search box
            searchInput.on('keyup', function (e) {
                clearTimeout(searchTimer);
                var query = $(this).val().trim();

                // Clear results if query is empty
                if (query.length === 0) {
                    resultsContainer.addClass('d-none').html('');
                    return;
                }

                // Minimum 2 characters to search
                if (query.length < 2) return;

                // If Enter key is pressed, submit the form
                if (e.key === 'Enter') {
                    searchInput.closest('form').submit();
                    return;
                }

                // Delay search to prevent too many requests
                searchTimer = setTimeout(function () {
                    // Show loading
                    resultsContainer.removeClass('d-none').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><p class="text-xs mt-2">Searching...</p></div>');

                    // Make AJAX request
                    $.ajax({
                        url: BASE_URL + '/admin/quick-search',
                        method: 'GET',
                        data: { query: query },
                        success: function (response) {
                            // If no results
                            if (response.length === 0) {
                                resultsContainer.html('<div class="text-center py-3"><p class="text-xs">No results found</p></div>');
                                return;
                            }

                            // Build results HTML
                            var html = '<div class="list-group list-group-flush">';

                            // Group results by type
                            var groupedResults = {};
                            response.forEach(function (item) {
                                if (!groupedResults[item.type]) {
                                    groupedResults[item.type] = [];
                                }
                                groupedResults[item.type].push(item);
                            });

                            // Add section titles and items
                            for (var type in groupedResults) {
                                var typeName = type.charAt(0).toUpperCase() + type.slice(1) + 's';
                                html += '<div class="px-3 py-2 bg-light text-xs text-uppercase font-weight-bolder">' + typeName + '</div>';

                                groupedResults[type].forEach(function (item) {
                                    html += '<a href="' + item.url + '" class="list-group-item list-group-item-action py-2 px-3">' +
                                        '<div class="d-flex align-items-center">' +
                                        '<div class="icon icon-shape icon-xs rounded-circle me-2 bg-gradient-' +
                                        (item.type === 'car' ? 'primary' : (item.type === 'client' ? 'success' : (item.type === 'booking' ? 'info' : 'warning'))) +
                                        ' text-white shadow">' +
                                        '<i class="' + item.icon + ' opacity-10"></i>' +
                                        '</div>' +
                                        '<div class="d-flex flex-column">' +
                                        '<h6 class="mb-0 text-sm">' + item.title + '</h6>' +
                                        '<p class="text-xs text-secondary mb-0">' + item.subtitle + '</p>' +
                                        '</div>' +
                                        '</div>' +
                                        '</a>';
                                });
                            }

                            html += '</div>';
                            html += '<div class="text-center py-2 border-top">' +
                                '<a href="' + BASE_URL + '/admin/search?query=' + encodeURIComponent(query) + '" class="text-primary text-xs font-weight-bold">' +
                                'View all results <i class="fas fa-arrow-right ml-1"></i>' +
                                '</a>' +
                                '</div>';

                            resultsContainer.html(html);
                        },
                        error: function () {
                            resultsContainer.html('<div class="text-center py-3"><p class="text-xs text-danger">Error loading search results</p></div>');
                        }
                    });
                }, 500);
            });

            // Hide results when clicking outside
            $(document).on('click', function (e) {
                if (!searchInput.is(e.target) && !resultsContainer.is(e.target) && resultsContainer.has(e.target).length === 0) {
                    resultsContainer.addClass('d-none');
                }
            });

            // Show results again when focusing on search input
            searchInput.on('focus', function () {
                if ($(this).val().trim().length >= 2) {
                    resultsContainer.removeClass('d-none');
                }
            });

            // Style for dropdown
            $('<style>')
                .text('.quick-search-results {position: absolute; top: 100%; left: 0; right: 0; z-index: 1000; max-height: 400px; overflow-y: auto;}')
                .appendTo('head');
        });
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{asset('admin/assets/js/argon-dashboard.min.js?v=2.1.0')}}"></script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - BATI Car Rental Admin</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">

    <!-- Custom Styles -->
    @stack('css')
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-logo">
                <a href="{{ route('admin.dashboard') }}">
                    <img src="{{ asset('img/logo.png') }}" alt="BATI Car Rental">
                </a>
            </div>

            <nav class="sidebar-menu">
                <div class="sidebar-menu-heading">Navigation</div>

                <a href="{{ route('admin.dashboard') }}"
                    class="sidebar-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Dashboard</span>
                </a>

                @can('view bookings')
                    <div class="sidebar-menu-item {{ request()->routeIs('admin.bookings.*') ? 'active expanded' : '' }}"
                        data-toggle="collapse" data-target="#bookingsMenu">
                        <i class="fas fa-book"></i>
                        <span class="menu-text">Bookings</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                    <div class="sidebar-submenu {{ request()->routeIs('admin.bookings.*') ? 'show' : '' }}"
                        id="bookingsMenu">
                        <a href="{{ route('admin.bookings.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">All
                            Bookings</a>
                        <a href="{{ route('admin.bookings.create') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.create') ? 'active' : '' }}">Create
                            Booking</a>
                        <a href="{{ route('admin.bookings.calendar') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.calendar') ? 'active' : '' }}">Booking
                            Calendar</a>
                    </div>
                @endcan

                @can('view vehicles')
                    <div class="sidebar-menu-item {{ request()->routeIs('admin.vehicles.*') ? 'active expanded' : '' }}"
                        data-toggle="collapse" data-target="#vehiclesMenu">
                        <i class="fas fa-car"></i>
                        <span class="menu-text">Vehicles</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                    <div class="sidebar-submenu {{ request()->routeIs('admin.vehicles.*') ? 'show' : '' }}"
                        id="vehiclesMenu">
                        <a href="{{ route('admin.vehicles.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.vehicles.index') ? 'active' : '' }}">All
                            Vehicles</a>
                        <a href="{{ route('admin.vehicles.create') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.vehicles.create') ? 'active' : '' }}">Add
                            Vehicle</a>
                        <a href="{{ route('admin.vehicles.categories.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.vehicles.categories.*') ? 'active' : '' }}">Categories</a>
                    </div>
                @endcan

                @can('view customers')
                    <a href="{{ route('admin.customers.index') }}"
                        class="sidebar-menu-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span class="menu-text">Customers</span>
                    </a>
                @endcan

                @can('view reports')
                    <div class="sidebar-menu-item {{ request()->routeIs('admin.reports.*') ? 'active expanded' : '' }}"
                        data-toggle="collapse" data-target="#reportsMenu">
                        <i class="fas fa-chart-bar"></i>
                        <span class="menu-text">Reports</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                    <div class="sidebar-submenu {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="reportsMenu">
                        <a href="{{ route('admin.reports.revenue') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}">Revenue
                            Reports</a>
                        <a href="{{ route('admin.reports.bookings') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.reports.bookings') ? 'active' : '' }}">Booking
                            Reports</a>
                        <a href="{{ route('admin.reports.vehicles') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.reports.vehicles') ? 'active' : '' }}">Vehicle
                            Reports</a>
                    </div>
                @endcan

                @can('manage settings')
                    <div class="sidebar-menu-heading">Settings</div>

                    <a href="{{ route('admin.settings.general') }}"
                        class="sidebar-menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span class="menu-text">System Settings</span>
                    </a>
                @endcan

                @can('manage admins')
                    <div class="sidebar-menu-item {{ request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'active expanded' : '' }}"
                        data-toggle="collapse" data-target="#adminMenu">
                        <i class="fas fa-user-shield"></i>
                        <span class="menu-text">Administration</span>
                        <i class="fas fa-chevron-right menu-arrow"></i>
                    </div>
                    <div class="sidebar-submenu {{ request()->routeIs('admin.admins.*') || request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') ? 'show' : '' }}"
                        id="adminMenu">
                        <a href="{{ route('admin.admins.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">Admin
                            Users</a>
                        <a href="{{ route('admin.roles.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">Roles &
                            Permissions</a>
                        @hasrole('Super Admin')
                        <a href="{{ route('admin.permissions.index') }}"
                            class="sidebar-submenu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">Permissions</a>
                        @endhasrole
                    </div>
                @endcan
            </nav>
        </aside>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Content Area -->
        <div class="admin-content" id="content">
            <!-- Header -->
            <header class="admin-header">
                <div class="admin-header-left">
                    <button type="button" class="toggle-sidebar" id="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="admin-header-right">
                    <div class="notifications-dropdown">
                        <div class="dropdown-toggle">
                            <button class="btn btn-icon btn-light">
                                <i class="fas fa-bell"></i>
                                <span class="badge badge-danger position-absolute top-0 end-0">3</span>
                            </button>
                        </div>
                    </div>

                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-toggle">
                            @if(Auth::guard('admin')->user()->profile_image)
                                <img src="{{ Storage::url(Auth::guard('admin')->user()->profile_image) }}" alt="Profile">
                            @else
                                <img src="{{ asset('img/avatar.png') }}" alt="Profile">
                            @endif
                            <div class="user-info">
                                <div class="user-name">{{ Auth::guard('admin')->user()->name }}</div>
                                <div class="user-role">
                                    {{ Auth::guard('admin')->user()->roles->first()->name ?? 'Admin' }}
                                </div>
                            </div>
                        </div>

                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="user-dropdown-header">
                                <div class="user-name">{{ Auth::guard('admin')->user()->name }}</div>
                                <div class="user-role">{{ Auth::guard('admin')->user()->email }}</div>
                                <div class="user-dropdown-body">
                                    <a href="{{ route('admin.profile') }}" class="user-dropdown-item">
                                        <i class="fas fa-user"></i> My Profile
                                    </a>
                                    <a href="{{ route('admin.two-factor.setup') }}" class="user-dropdown-item">
                                        <i class="fas fa-shield-alt"></i> Security
                                    </a>
                                    <div class="user-dropdown-divider"></div>
                                    <form method="POST" action="{{ route('admin.logout') }}" id="logout-form">
                                        @csrf
                                        <a href="{{ route('admin.logout') }}" class="user-dropdown-item"
                                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
            </header>

            <!-- Main Content -->
            <main class="admin-main">
                @if(session('success'))
                    <div class="alert alert-success">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-content">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container" id="notificationsContainer"></div>

    <!-- Loader -->
    <div class="spinner-overlay" id="loader">
        <div class="spinner-content">
            <div class="spinner"></div>
            <div class="mt-3">Processing...</div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Admin JS -->
    <script src="{{ asset('js/admin.js') }}"></script>

    <script>
        // Toggle sidebar
        document.getElementById('toggleSidebar').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('expanded');

            // On mobile, show/hide sidebar and overlay
            if (window.innerWidth < 992) {
                document.getElementById('sidebar').classList.toggle('show');
                document.getElementById('sidebarOverlay').classList.toggle('show');
            }
        });

        // Close sidebar on overlay click (mobile)
        document.getElementById('sidebarOverlay').addEventListener('click', function () {
            document.getElementById('sidebar').classList.remove('show');
            document.getElementById('sidebarOverlay').classList.remove('show');
        });

        // Toggle submenu
        document.querySelectorAll('.sidebar-menu-item[data-toggle="collapse"]').forEach(function (item) {
            item.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const target = document.querySelector(targetId);

                if (target) {
                    this.classList.toggle('expanded');
                    target.classList.toggle('show');
                }
            });
        });

        // Toggle user dropdown
        document.getElementById('userDropdown').addEventListener('click', function (e) {
            document.getElementById('userDropdownMenu').classList.toggle('show');
            e.stopPropagation();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!document.getElementById('userDropdown').contains(e.target)) {
                document.getElementById('userDropdownMenu').classList.remove('show');
            }
        });

        // Show notification
        function showNotification(type, message) {
            const container = document.getElementById('notificationsContainer');
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;

            let icon = '';
            switch (type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                case 'danger':
                    icon = '<i class="fas fa-times-circle"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-info-circle"></i>';
            }

            notification.innerHTML = `
                    <div class="notification-icon">${icon}</div>
                    <div class="notification-content">
                        <div class="notification-title">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
                        <div class="notification-text">${message}</div>
                    </div>
                    <button type="button" class="notification-close">
                        <i class="fas fa-times"></i>
                    </button>
                `;

            container.appendChild(notification);

            // Show notification with animation
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);

            // Close notification when clicking close button
            notification.querySelector('.notification-close').addEventListener('click', function () {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            });

            // Auto-close after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 5000);
        }

        // Show/hide loader
        function showLoader() {
            document.getElementById('loader').classList.add('show');
        }

        function hideLoader() {
            document.getElementById('loader').classList.remove('show');
        }

        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <!-- Custom Scripts -->
    @stack('js')
</body>

</html>
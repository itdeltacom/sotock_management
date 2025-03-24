<aside class="admin-sidebar" id="sidebar">
    <div class="sidebar-logo">
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('site/img/logo.png') }}" alt="BATI Car Rental">
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
                <i class="fas fa-calendar-alt"></i>
                <span class="menu-text">Bookings</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('admin.bookings.*') ? 'show' : '' }}" id="bookingsMenu">
                <a href="{{ route('admin.bookings.index') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.index') ? 'active' : '' }}">
                    <i class="fas fa-list submenu-icon"></i> All Bookings
                </a>
                {{-- <a href="{{ route('admin.bookings.create') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.create') ? 'active' : '' }}">
                    <i class="fas fa-plus submenu-icon"></i> Create Booking
                </a> --}}
                <a href="{{ route('admin.bookings.calendar') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.bookings.calendar') ? 'active' : '' }}">
                    <i class="fas fa-calendar submenu-icon"></i> Booking Calendar
                </a>
            </div>
        @endcan
        @can('view cars')
            <div class="sidebar-menu-item {{ request()->routeIs('admin.vehicles.*') || request()->routeIs('admin.cars.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.brands.*') ? 'active expanded' : '' }}"
                data-toggle="collapse" data-target="#vehiclesMenu">
                <i class="fas fa-car"></i>
                <span class="menu-text">Vehicles</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('admin.vehicles.*') || request()->routeIs('admin.cars.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.brands.*') ? 'show' : '' }}"
                id="vehiclesMenu">
                <a href="{{ route('admin.cars.index') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.cars.*') ? 'active' : '' }}">All
                    Cars</a>
                <a href="{{ route('admin.categories.index') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categories</a>
                <a href="{{ route('admin.brands.index') }}"
                    class="sidebar-submenu-item {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">Brands</a>
            </div>
        @endcan

        @can('view customers')
            <a href="{{ route('admin.customers.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span class="menu-text">Customers</span>
            </a>
        @endcan

        {{-- Blog Management Section --}}
        @canany(['view blog posts', 'view blog categories', 'view blog tags', 'view blog comments'])
            <div class="sidebar-menu-item {{ request()->routeIs('admin.blog-*') ? 'active expanded' : '' }}"
                data-toggle="collapse" data-target="#blogMenu">
                <i class="fas fa-blog"></i>
                <span class="menu-text">Blog Management</span>
                <i class="fas fa-chevron-right menu-arrow"></i>
            </div>
            <div class="sidebar-submenu {{ request()->routeIs('admin.blog-*') ? 'show' : '' }}" id="blogMenu">
                @can('view blog posts')
                    <a href="{{ route('admin.blog-posts.index') }}"
                        class="sidebar-submenu-item {{ request()->routeIs('admin.blog-posts.*') ? 'active' : '' }}">
                        <i class="fas fa-file-alt submenu-icon"></i> Blog Posts
                    </a>
                @endcan
                @can('view blog categories')
                    <a href="{{ route('admin.blog-categories.index') }}"
                        class="sidebar-submenu-item {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}">
                        <i class="fas fa-folder submenu-icon"></i> Categories
                    </a>
                @endcan
                @can('view blog tags')
                    <a href="{{ route('admin.blog-tags.index') }}"
                        class="sidebar-submenu-item {{ request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}">
                        <i class="fas fa-tags submenu-icon"></i> Tags
                    </a>
                @endcan
                @can('view blog comments')
                    <a href="{{ route('admin.blog-comments.index') }}"
                        class="sidebar-submenu-item {{ request()->routeIs('admin.blog-comments.*') ? 'active' : '' }}">
                        <i class="fas fa-comments submenu-icon"></i> Comments
                    </a>
                @endcan
            </div>
        @endcanany
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
        @can('view activities')
            <a href="{{ route('admin.activities.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('admin.activities.*') ? 'active' : '' }}">
                <i class="fas fa-history"></i>
                <span class="menu-text">Activity Log</span>
            </a>
        @endcan
    </nav>
</aside>
@push('js')
    <script>
        // Add click event to the "Add New Car" link to open the car modal
        document.addEventListener('DOMContentLoaded', function () {
            const addNewCarLink = document.getElementById('addNewCarLink');
            if (addNewCarLink) {
                addNewCarLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Trigger the "Add New Car" button click if it exists
                    const createCarBtn = document.getElementById('createCarBtn');
                    if (createCarBtn) {
                        createCarBtn.click();
                    }
                });
            }

            // Add click event for the "Add New Category" link
            const addNewCategoryLink = document.getElementById('addNewCategoryLink');
            if (addNewCategoryLink) {
                addNewCategoryLink.addEventListener('click', function (e) {
                    e.preventDefault();
                    // Trigger the "Add New Category" button click if it exists
                    const createCategoryBtn = document.getElementById('createCategoryBtn');
                    if (createCategoryBtn) {
                        createCategoryBtn.click();
                    }
                });
            }
        });
    </script>
@endpush
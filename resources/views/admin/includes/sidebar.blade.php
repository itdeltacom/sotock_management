<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('admin.dashboard') }}">
            <img src="{{asset('site/img/logo.png')}}" width="96px" height="96px" class="navbar-brand-img h-100"
                alt="main_logo">
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            <!-- Car Management Section -->
            @canany(['manage cars', 'view cars'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Car Management</h6>
                </li>

                @can('manage cars')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.cars.*') && !request()->routeIs('admin.cars.documents.*') ? 'active' : '' }}"
                            href="{{ route('admin.cars.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-bus-front-12 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Cars</span>
                        </a>
                    </li>
                @endcan

                @can('manage cars')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.cars.documents.*') ? 'active' : '' }}"
                            href="{{ route('admin.cars.documents.expiring') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Expiring Documents</span>
                        </a>
                    </li>
                @endcan

                @can('manage cars')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.cars.maintenance.due-soon') ? 'active' : '' }}"
                            href="{{ route('admin.cars.maintenance.due-soon') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-settings text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Maintenance</span>
                        </a>
                    </li>
                @endcan

                <!-- Product_brands -->
                @canany(['view brands', 'create brands', 'edit brands', 'delete brands'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"
                            href="{{ route('admin.brands.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-badge text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Product brands</span>
                        </a>
                    </li>
                @endcanany

                @can('manage categories')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                            href="{{ route('admin.categories.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Categories</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Stock Management Section -->
            @canany(['manage products', 'view products', 'manage inventory', 'view inventory'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Stock Management</h6>
                </li>

                @canany(['view products', 'manage products'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"
                            href="{{ route('admin.products.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Products</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view warehouses', 'manage warehouses'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.warehouses.*') ? 'active' : '' }}"
                            href="{{ route('admin.warehouses.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-building text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Warehouses</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view suppliers', 'manage suppliers'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}"
                            href="{{ route('admin.suppliers.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-shop text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Suppliers</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view purchase-orders', 'manage purchase-orders'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}"
                            href="{{ route('admin.purchase-orders.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-cart text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Purchase Orders</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view sales-orders', 'manage sales-orders'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.sales-orders.*') ? 'active' : '' }}"
                            href="{{ route('admin.sales-orders.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-tag text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Sales Orders</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view stock', 'manage stock'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}"
                            href="{{ route('admin.inventory.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-app text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Inventory</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view stock-movements', 'manage stock-movements'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'active' : '' }}"
                            href="{{ route('admin.stock-movements.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-curved-next text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Stock Movements</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view stock-transfers', 'manage stock-transfers'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.stock-transfers.*') ? 'active' : '' }}"
                            href="{{ route('admin.stock-transfers.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-spaceship text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Stock Transfers</span>
                        </a>
                    </li>
                @endcanany

                @canany(['view stock-adjustments', 'manage stock-adjustments'])
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.stock-adjustments.*') ? 'active' : '' }}"
                            href="{{ route('admin.stock-adjustments.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-settings-gear-65 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Stock Adjustments</span>
                        </a>
                    </li>
                @endcanany
            @endcanany
            <!-- Contract Management Section -->
            @canany(['manage contracts', 'view contracts'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Contract Management</h6>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contracts.*') && !request()->routeIs('admin.contracts.ending-soon') && !request()->routeIs('admin.contracts.overdue') ? 'active' : '' }}"
                        href="{{ route('admin.contracts.index') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-file-signature text-primary text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">All Contracts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contracts.ending-soon') ? 'active' : '' }}"
                        href="{{ route('admin.contracts.ending-soon') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-hourglass-end text-warning text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Ending Soon</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.contracts.overdue') ? 'active' : '' }}"
                        href="{{ route('admin.contracts.overdue') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-exclamation-triangle text-danger text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Overdue Contracts</span>
                    </a>
                </li>
            @endcanany

            <!-- Booking Management Section -->
            @canany(['manage bookings', 'view bookings'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Booking Management</h6>
                </li>

                @can('manage bookings')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.bookings.*') && !request()->routeIs('admin.bookings.calendar') ? 'active' : '' }}"
                            href="{{ route('admin.bookings.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Bookings</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.bookings.calendar') ? 'active' : '' }}"
                            href="{{ route('admin.bookings.calendar') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Booking Calendar</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Clients Management Section -->
            @can('manage clients')
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Clients Management</h6>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clients.*') && !request()->routeIs('admin.clients.payments') && !request()->routeIs('admin.clients.contracts') ? 'active' : '' }}"
                        href="{{ route('admin.clients.index') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Clients</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clients.payments') ? 'active' : '' }}"
                        href="{{ route('admin.clients.index') }}/payments">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-money-coins text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Payments</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clients.contracts') ? 'active' : '' }}"
                        href="{{ route('admin.clients.index') }}/contracts">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-paper-diploma text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Contracts</span>
                    </a>
                </li>
            @endcan

            <!-- Content Management -->
            @canany(['manage blog posts', 'manage blog categories', 'manage blog tags', 'manage blog comments', 'manage testimonials'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Content Management</h6>
                </li>

                @can('manage blog posts')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.blog-posts.*') ? 'active' : '' }}"
                            href="{{ route('admin.blog-posts.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Blog Posts</span>
                        </a>
                    </li>
                @endcan

                @can('manage blog categories')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}"
                            href="{{ route('admin.blog-categories.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-archive-2 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Blog Categories</span>
                        </a>
                    </li>
                @endcan

                @can('manage blog tags')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.blog-tags.*') ? 'active' : '' }}"
                            href="{{ route('admin.blog-tags.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-tag text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Blog Tags</span>
                        </a>
                    </li>
                @endcan

                @can('manage blog comments')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.blog-comments.*') ? 'active' : '' }}"
                            href="{{ route('admin.blog-comments.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-chat-round text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Blog Comments</span>
                        </a>
                    </li>
                @endcan

                @can('manage testimonials')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}"
                            href="{{ route('admin.testimonials.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-chat-round text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Testimonials</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Admin Management Section -->
            @canany(['view admins', 'manage roles', 'manage permissions'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Admin Management</h6>
                </li>

                @can('view admins')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.admins.*') ? 'active' : '' }}"
                            href="{{ route('admin.admins.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-circle-08 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Admins</span>
                        </a>
                    </li>
                @endcan

                @can('manage roles')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                            href="{{ route('admin.roles.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-badge text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Roles</span>
                        </a>
                    </li>
                @endcan

                @can('manage permissions')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                            href="{{ route('admin.permissions.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-key-25 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Permissions</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Reports Section -->
            @can('view reports')
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Reports</h6>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.revenue') ? 'active' : '' }}"
                        href="{{ route('admin.reports.revenue') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-chart-bar-32 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Revenue</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.bookings') ? 'active' : '' }}"
                        href="{{ route('admin.reports.bookings') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-chart-pie-35 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Bookings</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.vehicles') ? 'active' : '' }}"
                        href="{{ route('admin.reports.vehicles') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-delivery-fast text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Vehicles</span>
                    </a>
                </li>
            @endcan

            <!-- Account Section -->
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}"
                    href="{{ route('admin.profile') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.password.change') ? 'active' : '' }}"
                    href="{{ route('admin.password.change') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-key-25 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Change Password</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-user-run text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Logout</span>
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside>
<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/img/logo-ct-dark.png') }}" width="26px" height="26px"
                class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">{{ config('app.name') }}</span>
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
            <!-- Admin Management Section -->
            @canany(['view admins', 'view roles', 'view permissions'])
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

                @can('view roles')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}"
                            href="{{ route('admin.roles.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-badge text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Roles</span>
                        </a>
                    </li>
                @endcan

                @can('view permissions')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}"
                            href="{{ route('admin.permissions.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-lock-circle-open text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Permissions</span>
                        </a>
                    </li>
                @endcan

                @can('assign permissions')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.roles-permissions') ? 'active' : '' }}"
                            href="{{ route('admin.roles-permissions') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-settings-gear-65 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Role Permissions</span>
                        </a>
                    </li>
                @endcan
            @endcanany
            <!-- Car Management Section -->
            @canany(['view cars', 'manage cars'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Car Management</h6>
                </li>

                @can('view cars')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.cars.*') ? 'active' : '' }}"
                            href="{{ route('admin.cars.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-bus-front-12 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Cars</span>
                        </a>
                    </li>
                @endcan

                @can('view documents')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}"
                            href="{{ route('admin.documents.expiring') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Documents</span>
                        </a>
                    </li>
                @endcan

                @can('view maintenance')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.maintenance.*') ? 'active' : '' }}"
                            href="{{ route('admin.maintenance.due.soon') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-settings text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Maintenance</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Contract Management Section -->
            @canany(['view contracts', 'manage contracts'])
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Rental Management</h6>
                </li>

                @can('view contracts')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.contracts.*') ? 'active' : '' }}"
                            href="{{ route('admin.contracts.index') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-paper-diploma text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Contracts</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.contracts.ending.soon') ? 'active' : '' }}"
                            href="{{ route('admin.contracts.ending.soon') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-time-alarm text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Ending Soon</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.contracts.overdue') ? 'active' : '' }}"
                            href="{{ route('admin.contracts.overdue') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-notification-70 text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Overdue</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Client Management -->
            @can('view clients')
                <li class="nav-item mt-3">
                    <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Client Management</h6>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"
                        href="{{ route('admin.clients.index') }}">
                        <div
                            class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                        </div>
                        <span class="nav-link-text ms-1">Clients</span>
                    </a>
                </li>
            @endcan

            <!-- Admin Management Section -->
            @canany(['view admins', 'view roles'])
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

                @can('view roles')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}"
                            href="{{ route('admin.roles-permissions') }}">
                            <div
                                class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="ni ni-badge text-dark text-sm opacity-10"></i>
                            </div>
                            <span class="nav-link-text ms-1">Roles & Permissions</span>
                        </a>
                    </li>
                @endcan
            @endcanany

            <!-- Account Section -->
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
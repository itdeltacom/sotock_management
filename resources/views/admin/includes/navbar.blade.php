<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl " id="navbarBlur"
    data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white"
                        href="{{ route('admin.dashboard') }}">{{ __('Pages') }}</a></li>
                @if(isset($breadcrumb))
                    @foreach($breadcrumb as $item)
                        @if($loop->last)
                            <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{ $item['title'] }}</li>
                        @else
                            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white"
                                    href="{{ $item['url'] }}">{{ $item['title'] }}</a></li>
                        @endif
                    @endforeach
                @else
                    <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                        {{ $pageTitle ?? 'Dashboard' }}
                    </li>
                @endif
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">{{ $pageTitle ?? 'Dashboard' }}</h6>
            <!-- Display current date in Moroccan format -->
            <p class="text-xs text-white opacity-8 mb-0">{{ \Carbon\Carbon::now()->locale('fr')->format('d F Y') }}</p>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <!-- Quick stats summary -->
            <div class="d-none d-md-block me-md-4">
                <div class="d-flex">
                    @if(isset($todayBookings))
                        <div class="px-3 py-1 text-center">
                            <span class="d-block text-xs text-white opacity-7">{{ __("Today's Bookings") }}</span>
                            <span class="d-block text-white font-weight-bold">{{ $todayBookings }}</span>
                        </div>
                    @endif

                    @if(isset($availableVehicles) && isset($totalVehicles))
                        <div class="px-3 py-1 text-center border-start border-white border-opacity-25">
                            <span class="d-block text-xs text-white opacity-7">{{ __('Available Vehicles') }}</span>
                            <span
                                class="d-block text-white font-weight-bold">{{ $availableVehicles }}/{{ $totalVehicles }}</span>
                        </div>
                    @endif

                    @if(isset($pendingReturns))
                        <div class="px-3 py-1 text-center border-start border-white border-opacity-25">
                            <span class="d-block text-xs text-white opacity-7">{{ __('Pending Returns') }}</span>
                            <span class="d-block text-white font-weight-bold">{{ $pendingReturns }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Search form -->
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <form action="{{ route('admin.search') }}" method="GET">
                    <div class="input-group">
                        <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                        <input type="text" name="query" class="form-control" id="global-search"
                            placeholder="{{ __('Search vehicles, clients, bookings...') }}"
                            value="{{ request('query') }}">
                    </div>
                    <!-- Quick search results dropdown -->
                    <div
                        class="quick-search-results d-none position-absolute bg-white rounded shadow-lg mt-1 p-2 w-100 z-index-dropdown">
                    </div>
                </form>
            </div>

            <!-- Right nav items -->
            <ul class="navbar-nav justify-content-end">
                <!-- Mobile menu toggle -->
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item px-3 d-flex align-items-center">
                    <a href="{{ route('admin.profile') }}" class="nav-link text-white p-0" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" title="{{ __('Settings') }}">
                        <i class="fa fa-cog cursor-pointer"></i>
                    </a>
                </li>

                <!-- Notifications -->
                <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                        @if(isset($unreadNotifications) && $unreadNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}
                            </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                        @if(isset($notifications) && count($notifications) > 0)
                            @foreach($notifications as $notification)
                                <li class="mb-2">
                                    <a class="dropdown-item border-radius-md {{ $notification->read_at ? '' : 'bg-light' }}"
                                        href="#">
                                        <div class="d-flex py-1">
                                            <div class="my-auto">
                                                <div
                                                    class="avatar avatar-sm bg-gradient-primary me-3 d-flex align-items-center justify-content-center">
                                                    <i class="ni ni-bell-55 text-white"></i>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="text-sm font-weight-normal mb-1">
                                                    <span
                                                        class="font-weight-bold">{{ $notification->data['title'] ?? 'New notification' }}</span>
                                                </h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    <i class="fa fa-clock me-1"></i>
                                                    {{ isset($notification->created_at) ? \Carbon\Carbon::parse($notification->created_at)->diffForHumans() : 'Just now' }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <img src="{{asset('admin/assets/img/team-2.jpg')}}"
                                                class="avatar avatar-sm me-3">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">New message</span> from Laur
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                13 minutes ago
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="mb-2">
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="my-auto">
                                            <img src="{{asset('admin/assets/img/small-logos/logo-spotify.svg')}}"
                                                class="avatar avatar-sm bg-gradient-dark me-3">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                <span class="font-weight-bold">New booking</span> by Mohamed
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                1 day ago
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item border-radius-md" href="javascript:;">
                                    <div class="d-flex py-1">
                                        <div class="avatar avatar-sm bg-gradient-secondary me-3 my-auto">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="text-sm font-weight-normal mb-1">
                                                Payment successfully completed
                                            </h6>
                                            <p class="text-xs text-secondary mb-0">
                                                <i class="fa fa-clock me-1"></i>
                                                2 days ago
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <!-- Modified Notifications Dropdown -->
                {{-- <li class="nav-item dropdown pe-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell cursor-pointer"></i>
                        @if($unreadNotifications > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}
                        </span>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                        @if(count($notifications) > 0)
                        @foreach($notifications as $notification)
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md {{ $notification['read_at'] ? '' : 'bg-light' }}"
                                href="{{ $notification['link'] }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <div
                                            class="avatar avatar-sm bg-gradient-{{ $notification['color'] }} me-3 d-flex align-items-center justify-content-center">
                                            <i class="{{ $notification['icon'] }} text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">{{ $notification['title'] }}</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0">
                                            {{ $notification['message'] }}
                                        </p>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            {{ $notification['created_at']->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endforeach
                        <li class="text-center mt-2">
                            <a href="{{ route('admin.notifications.index') }}" class="dropdown-item text-primary">
                                <i class="fas fa-eye me-1"></i> View All Notifications
                            </a>
                        </li>
                        @else
                        <li class="text-center py-2">
                            <span class="text-secondary">No new notifications</span>
                        </li>
                        @endif
                    </ul>
                </li> --}}

                <!-- User Profile - Now with auth profile photo -->
                <li class="nav-item dropdown ps-2 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="userDropdown" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        @if(Auth::guard('admin')->user()->profile_image)
                            <img src="{{ Storage::url(Auth::guard('admin')->user()->profile_image) }}"
                                class="avatar avatar-sm rounded-circle" alt="{{ Auth::guard('admin')->user()->name }}">
                        @else
                            <div
                                class="avatar avatar-sm bg-gradient-primary rounded-circle d-flex justify-content-center align-items-center">
                                {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="userDropdown">
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="{{ route('admin.profile') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <i class="ni ni-single-02 text-dark me-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-weight-normal mb-0">{{ __('My Profile') }}</h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md" href="{{ route('admin.password.change') }}">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <i class="ni ni-key-25 text-dark me-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-weight-normal mb-0">{{ __('Change Password') }}</h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item border-radius-md" href="{{ route('admin.logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <i class="ni ni-user-run text-dark me-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-sm font-weight-normal mb-0">{{ __('Logout') }}</h6>
                                    </div>
                                </div>
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
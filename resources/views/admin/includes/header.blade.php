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
                @php
                    $authUser = Auth::guard('admin')->user();
                @endphp
                @if($authUser->profile_image)
                    <img src="{{ Storage::url($authUser->profile_image) }}" alt="Profile">
                @else
                    <img src="{{ asset('img/avatar.png') }}" alt="Profile">
                @endif
                <div class="user-info">
                    <div class="user-name">{{ $authUser->name }}</div>
                    <div class="user-role">
                        {{ $authUser->roles->first()->name ?? 'Admin' }}
                    </div>
                </div>
            </div>

            <div class="user-dropdown-menu" id="userDropdownMenu">
                <div class="user-dropdown-header">
                    <div class="user-name">{{ $authUser->name }}</div>
                    <div class="user-role">{{ $authUser->email }}</div>
                </div>
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
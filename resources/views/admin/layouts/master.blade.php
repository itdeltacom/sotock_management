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

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    @if(isset($sweet_alert_styles))
        {!! $sweet_alert_styles !!}
    @endif

    <!-- Admin CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <!-- Custom Styles -->
    @stack('css')
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        @include('admin.includes.sidebar')

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Content Area -->
        <div class="admin-content" id="content">
            <!-- Header -->
            @include('admin.includes.header')

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

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bootstrap 5 Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 JS -->
    @if(isset($sweet_alert_scripts))
        {!! $sweet_alert_scripts !!}
    @else
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif

    <!-- Admin JS -->
    <script src="{{ asset('admin/js/admin.js') }}"></script>

    <!-- Display SweetAlert from session flash -->
    @if(isset($sweet_alert))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                {!! $sweet_alert !!}
            });
        </script>
    @endif

    <!-- Custom Scripts -->
    @stack('js')
</body>

</html>
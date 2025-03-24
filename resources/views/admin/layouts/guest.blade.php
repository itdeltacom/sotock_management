<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Login') - BATI Car Rental</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    @if(isset($sweet_alert_styles))
        {!! $sweet_alert_styles !!}
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <style>
            :root {
                --swal2-primary: #2D3FE0;
                --swal2-success: #34D399;
                --swal2-warning: #FBBF24;
                --swal2-error: #EF4444;
                --swal2-info: #2D3FE0;
                --swal2-accent: #FF7D3B;
            }

            .colored-toast.swal2-icon-success {
                background-color: rgba(52, 211, 153, 0.9) !important;
            }

            .colored-toast.swal2-icon-error {
                background-color: rgba(239, 68, 68, 0.9) !important;
            }

            .colored-toast.swal2-icon-warning {
                background-color: rgba(251, 191, 36, 0.9) !important;
            }

            .colored-toast.swal2-icon-info {
                background-color: rgba(45, 63, 224, 0.9) !important;
            }

            .colored-toast .swal2-title {
                color: white !important;
            }

            .colored-toast .swal2-html-container {
                color: rgba(255, 255, 255, 0.8) !important;
            }

            .swal2-popup {
                font-family: var(--font-sans, "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif) !important;
                border-radius: var(--radius-lg, 0.75rem) !important;
            }

            .swal2-confirm {
                background-color: var(--swal2-primary) !important;
            }

            .swal2-styled.swal2-confirm:focus {
                box-shadow: 0 0 0 3px rgba(45, 63, 224, 0.3) !important;
            }
        </style>
    @endif

    <!-- Custom Styles -->
    @stack('css')
</head>

<body>
    @yield('content')

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('admin/js/sweet-alert.js') }}"></script>
    <!-- SweetAlert2 JS -->
    @if(isset($sweet_alert_scripts))
        {!! $sweet_alert_scripts !!}
    @else
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif

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
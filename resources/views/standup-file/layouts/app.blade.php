<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('custom/brand/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@6..144,1..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('custom/css/app.css') }}?v=1.0">
    @yield('css')
</head>
<body>
    @yield('hero')

    @include('layouts.navs')

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        function showComingSoon(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Coming Soon!',
                text: 'This feature is under development.',
                icon: 'info',
                confirmButtonColor: '#e65856',
                background: '#121212',
                color: '#ffffff',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
        $(document).ready(function() {
            // Navbar scroll effect
            const nav = $('#mainNav');
            const heroExists = $('.hero-section').length;
            
            function handleNavScroll() {
                if (heroExists) {
                    if ($(window).scrollTop() > 50) {
                        nav.addClass('scrolled');
                    } else {
                        nav.removeClass('scrolled');
                    }
                } else {
                    nav.addClass('scrolled');
                }
            }
            
            $(window).on('scroll', handleNavScroll);
            handleNavScroll();
            
            // Admin dropdown
            $('.icon-item.dropdown .icon-circle').on('click', function(e) {
                e.stopPropagation();
                $(this).closest('.icon-item.dropdown').find('.dropdown-menu').toggleClass('show');
            });

            $(document).on('click', function() {
                $('.dropdown-menu').removeClass('show');
            });
        });
    </script>
    
    @yield('js')
</body>
</html>
<!-- resources/views/layouts/auth.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #0a0a1a;
        }
        @keyframes slowRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        .animate-shimmer {
            animation: shimmer 3s linear infinite;
            background-size: 200% auto;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-[#0a0a1a]">
    <div class="flex min-h-screen">
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
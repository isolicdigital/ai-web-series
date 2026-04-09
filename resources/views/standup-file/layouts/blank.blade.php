<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @yield('metadata')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ $metadata->login_url ?? url('/') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans+Flex:opsz,wght@6..144,1..1000&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('custom/brand/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('custom/css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @yield('css')
</head>
<body>
    @yield('content')
</body>
</html>
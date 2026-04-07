@php
    $settings = App\Models\Setting::first();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0"> -->
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Fablead Inventory-Billing Software">
    <meta name="keywords" content="inventory management, billing system, invoice generator, purchase orders, inventory control, POS system, admin dashboard">
    <meta name="author" content="Fablead Developers Technolab">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Default Title')</title>

    <link rel="shortcut icon" type="image/x-icon"
        href="{{ optional($settings)->favicon ? env('ImagePath').'/storage/' . $settings->favicon : 'https://fableadtechnolabs.com/favicon-192x192.webp' }}">

    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/css/bootstrap.min.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/plugins/owlcarousel/owl.carousel.min.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/plugins/owlcarousel/owl.theme.default.min.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/css/animate.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/plugins/select2/css/select2.min.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/css/bootstrap-datetimepicker.min.css' }}">

    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/css/dataTables.bootstrap4.min.css' }}">

    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/plugins/fontawesome/css/fontawesome.min.css' }}">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/plugins/fontawesome/css/all.min.css' }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ env('ImagePath').'/admin/assets/css/style.css' }}">
    @stack('css')
</head>

<body style="min-height: 100vh; display: flex; flex-direction: column;">

    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper" style="flex: 1;">
        @include('layout.header')
        @include('layout.sidebar')

        <!-- Main Content Section -->
        <div class="page-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Footer with copyright -->
    <footer style="text-align: center; padding: 10px 0; background-color: #f4f4f4; height: 50px;">
        <h1 style="font-size: 14px; font-weight: 600;">© <?= date('Y') ?> Copyright - Fablead Developers Technolab</h1>
    </footer>

    @include('layout.footer')
    <div class="mobile-bottom-nav">
        <a href="{{ route('auth.dashboard') }}" class="nav-item {{ request()->routeIs('auth.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('product.list') }}" class="nav-item {{ request()->routeIs('product.list') ? 'active' : '' }}">
            <i class="fas fa-cubes"></i>
            <span>Products</span>
        </a>
        <a href="{{ route('sales.list') }}" class="nav-item {{ request()->routeIs('sales.list') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i>
            <span>Order</span>
        </a>
        <a href="{{ route('purchase.lists') }}" class="nav-item {{ request()->routeIs('purchase.lists') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i>
            <span>Purchase</span>
        </a>
        <a href="{{ route('customer.list') }}" class="nav-item {{ request()->routeIs('customer.list') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Customer</span>
        </a>
    </div>
 
    @stack('js')
</body>

</html>
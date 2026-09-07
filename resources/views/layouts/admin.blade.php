<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') &mdash; My-set Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <span class="brand__mark">MS</span>
                My-set <span style="color: var(--color-accent); font-weight: 700;">Admin</span>
            </a>

            <div class="header-actions">
                <a href="{{ route('home') }}" class="btn btn-outline btn-sm">View store</a>
                <span class="user-menu__name">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="admin-shell">
        <aside class="admin-sidebar">
            <h2>Manage</h2>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">📊 Dashboard</a>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">👟 Products</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}">🏷️ Categories</a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">
                📦 Orders
                @if($sidebarPendingOrders > 0)
                    <span class="sidebar-badge">{{ $sidebarPendingOrders }}</span>
                @endif
            </a>
        </aside>

        <div class="admin-main">
            @if(session('status'))
                <div class="alert alert-success" data-auto-hide>{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <div id="toast-container"></div>

    @if(session('status'))
        <script>window.flashStatus = @json(session('status'));</script>
    @endif

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>

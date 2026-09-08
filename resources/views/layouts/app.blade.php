<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My-set') &mdash; My-set</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand__mark">MS</span>
                My-set
            </a>

            <nav class="main-nav">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') && !request('category') ? 'is-active' : '' }}">All</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'is-active' : '' }}">My Orders</a>
                    <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.*') ? 'is-active' : '' }}">My Account</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Admin</a>
                    @endif
                @endauth
            </nav>

            <div class="header-actions">
                @auth
                    <a href="{{ route('cart.index') }}" class="icon-btn cart-link" title="Cart">
                        🛒
                        @php($cartCount = auth()->user()->cartItems()->sum('qty'))
                        @if($cartCount > 0)
                            <span class="cart-badge">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('profile.edit') }}" class="user-menu__name">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
                @endauth

                <button class="hamburger" data-mobile-toggle aria-label="Toggle menu" aria-expanded="false">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <div class="mobile-nav" data-mobile-menu>
            <a href="{{ route('home') }}">All Products</a>
            @auth
                <a href="{{ route('cart.index') }}">Cart ({{ auth()->user()->cartItems()->sum('qty') }})</a>
                <a href="{{ route('orders.index') }}">My Orders</a>
                <a href="{{ route('profile.edit') }}">My Account</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Sign up</a>
            @endauth
        </div>
    </header>

    @include('partials.deals-banner')

    <main>
        <div class="container">
            @if(session('status'))
                <div class="alert alert-success" data-auto-hide style="margin-top:24px;">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" style="margin-top:24px;">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <a href="{{ route('home') }}" class="brand">
                    <span class="brand__mark">MS</span>
                    My-set
                </a>
                <p>Fresh sneakers, boots and shirts &mdash; pick your size, color and style, and we'll handle the rest.</p>
            </div>

            <div class="footer-col footer-col--center">
                <h4>Contact me </h4>
                <a href="https://t.me/Chombunthoeun" target="_blank" rel="noopener" class="btn btn-primary btn-sm btn-telegram" aria-label="Message us on Telegram" title="Message us on Telegram"><svg viewBox="0 0 240 240" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M120 0C53.7 0 0 53.7 0 120s53.7 120 120 120 120-53.7 120-120S186.3 0 120 0Zm56.2 79.6-19.6 92.5c-1.5 6.6-5.4 8.2-10.9 5.1l-30.1-22.2-14.5 14c-1.6 1.6-2.9 2.9-6 2.9l2.1-30.6 55.8-50.4c2.4-2.2-.5-3.4-3.8-1.2l-69 43.5-29.7-9.3c-6.5-2-6.6-6.5 1.3-9.6l116.1-44.8c5.4-2 10.1 1.3 8.3 9.1Z"/></svg></a>
            </div>

            <div class="footer-col">
                <h4>Account</h4>
                <ul>
                    @auth
                        <li><a href="{{ route('cart.index') }}">Your cart</a></li>
                        <li><a href="{{ route('orders.index') }}">Order history</a></li>
                        <li><a href="{{ route('profile.edit') }}">Account details</a></li>
                        @if(auth()->user()->isAdmin())
                            <li><a href="{{ route('admin.dashboard') }}">Admin dashboard</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}">Log in</a></li>
                        <li><a href="{{ route('register') }}">Create an account</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </footer>

    <div id="toast-container"></div>

    @if(session('status'))
        <script>window.flashStatus = @json(session('status'));</script>
    @endif

    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>

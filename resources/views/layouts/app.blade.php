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
    <header class="site-header" id="top">
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
                <div class="footer-socials">
                    <a href="https://t.me/Chombunthoeun" target="_blank" rel="noopener" class="footer-social" aria-label="Message us on Telegram" title="Telegram">
                        <svg viewBox="0 0 240 240" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M120 0C53.7 0 0 53.7 0 120s53.7 120 120 120 120-53.7 120-120S186.3 0 120 0Zm56.2 79.6-19.6 92.5c-1.5 6.6-5.4 8.2-10.9 5.1l-30.1-22.2-14.5 14c-1.6 1.6-2.9 2.9-6 2.9l2.1-30.6 55.8-50.4c2.4-2.2-.5-3.4-3.8-1.2l-69 43.5-29.7-9.3c-6.5-2-6.6-6.5 1.3-9.6l116.1-44.8c5.4-2 10.1 1.3 8.3 9.1Z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/kview.bt" target="_blank" rel="noopener" class="footer-social" aria-label="Follow us on Facebook" title="Facebook">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Shop</h4>
                <ul>
                    <li><a href="{{ route('home') }}">All products</a></li>
                    <li><a href="{{ route('home') }}#deals">Big discounts</a></li>
                    @guest
                        <li><a href="{{ route('register') }}">Create an account</a></li>
                    @endguest
                </ul>
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

            <div class="footer-col">
                <h4>Help</h4>
                <ul>
                    <li><a href="https://t.me/Chombunthoeun" target="_blank" rel="noopener">Sizing &amp; fit</a></li>
                    <li><a href="https://t.me/Chombunthoeun" target="_blank" rel="noopener">Shipping &amp; returns</a></li>
                    <li><a href="https://t.me/Chombunthoeun" target="_blank" rel="noopener">Contact support</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container footer-bottom__inner">
                <p>&copy; {{ date('Y') }} My-set. All rights reserved.</p>
                <div class="footer-bottom__meta">
                    <span class="footer-pay">Cash on delivery &bull; Card &bull; ABA</span>
                    <a href="#top" class="footer-top-link">Back to top <span aria-hidden="true">&uarr;</span></a>
                </div>
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

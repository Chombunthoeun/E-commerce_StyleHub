@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<section class="hero">
    <div class="hero__glow hero__glow--one" aria-hidden="true"></div>
    <div class="hero__glow hero__glow--two" aria-hidden="true"></div>
    <div class="container">
        <span class="hero__eyebrow">Welcome to StyleHub</span>
        <h1>Step out in style</h1>
        <p>Fresh sneakers, boots and shirts &mdash; pick your size, color and style, and we'll handle the rest.</p>
        <div class="hero__stats">
            <div class="hero__stat">
                <strong>{{ number_format($products->total()) }}+</strong>
                <span>Styles</span>
            </div>
            <div class="hero__stat-divider" aria-hidden="true"></div>
            <div class="hero__stat">
                <strong>{{ $categories->count() }}</strong>
                <span>Categories</span>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="shop-toolbar">
        <div class="chip-row">
            <a href="{{ route('home') }}" class="chip {{ $activeCategory === '' ? 'is-active' : '' }}">All</a>
            @foreach($categories as $category)
                <a href="{{ route('home', ['category' => $category->slug]) }}" class="chip {{ $activeCategory === $category->slug ? 'is-active' : '' }}">{{ $category->name }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('home') }}" class="search-box">
            @if($activeCategory)
                <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif
            <span class="search-box__icon" aria-hidden="true">🔍</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="Search products&hellip;">
        </form>
    </div>

    @if($products->isEmpty())
        <div class="empty-state">
            <h3>No products found</h3>
            <p>Try a different category or search term.</p>
        </div>
    @else
        <div class="product-grid">
            @foreach($products as $product)
                <a href="{{ route('products.show', $product->slug) }}" class="product-card">
                    <div class="product-card__media">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                        @if($product->totalStock() === 0)
                            <span class="badge badge-out">Out of stock</span>
                        @elseif($product->isLowStock())
                            <span class="badge badge-low">Low stock</span>
                        @endif
                        @if($product->hasDiscount())
                            <span class="badge badge-discount">-{{ $product->discount_percent }}%</span>
                        @endif
                        <span class="product-card__view">View details &rarr;</span>
                    </div>
                    <div class="product-card__body">
                        <div class="product-card__category">{{ $product->category->name }}</div>
                        <h3>{{ $product->name }}</h3>
                        @include('products._price', ['product' => $product])
                    </div>
                </a>
            @endforeach
        </div>

        @include('partials.pagination', ['paginator' => $products])
    @endif
</div>
@endsection

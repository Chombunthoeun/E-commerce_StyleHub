@extends('layouts.app')

@section('title', $product->name)

@php
    $optionFields = ['size' => 'Size', 'color' => 'Color', 'style' => 'Style'];
    $activeFields = collect($optionFields)->filter(function ($label, $field) use ($product) {
        return $product->variants->pluck($field)->filter()->isNotEmpty();
    });
@endphp

@section('content')
<div class="container">
    <div class="product-detail">
        <div class="product-detail__media reveal" data-media data-zoom-trigger>
            <div class="media-shimmer" data-media-shimmer></div>
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="media-img is-active" data-media-img>
            <img alt="{{ $product->name }}" class="media-img" data-media-img aria-hidden="true">
            <span class="media-zoom-hint">🔍 Click to enlarges</span>
        </div>

        <div class="product-detail__info">
            <div class="product-detail__category">{{ $product->category->name }}</div>
            <h1>{{ $product->name }}</h1>
            <div class="product-detail__price-row">
                <span class="product-detail__price" data-current-price>${{ number_format($product->discountedPrice(), 2) }}</span>
                <span class="price-original" data-original-price {{ $product->hasDiscount() ? '' : 'hidden' }}>${{ number_format($product->price, 2) }}</span>
                <span class="badge-discount-inline" data-discount-badge {{ $product->hasDiscount() ? '' : 'hidden' }}>-{{ $product->discount_percent }}%</span>
            </div>

            @if($product->description)
                <p class="product-detail__description">{{ $product->description }}</p>
            @endif

            @auth
                <form method="POST" action="{{ route('cart.store') }}" data-add-to-cart-form>
                    @csrf
                    <input type="hidden" name="product_variant_id" value="" data-variant-id-input>

                    <div data-variant-picker data-base-price="{{ $product->price }}" data-discount-percent="{{ $product->discount_percent ?? 0 }}">
                        @foreach($activeFields as $field => $label)
                            <div class="option-group" data-option-group="{{ $field }}">
                                <div class="option-group__label">{{ $label }} <span class="selected-value"></span></div>
                                <div class="option-buttons {{ $field === 'color' ? 'option-buttons--swatches' : '' }}">
                                    @foreach($product->variants->pluck($field)->filter()->unique() as $value)
                                        @if($field === 'color')
                                            @php($swatchVariant = $product->variants->first(fn ($v) => $v->color === $value && $v->image))
                                            <span class="option-swatch-wrap">
                                                <button
                                                    type="button"
                                                    class="option-btn option-swatch"
                                                    data-value="{{ $value }}"
                                                    aria-label="{{ $value }}"
                                                    @if($swatchVariant) style="background-image: url('{{ $swatchVariant->imageUrl() }}')" @endif
                                                >
                                                    @unless($swatchVariant)
                                                        <span class="option-swatch__fallback">{{ $value }}</span>
                                                    @endunless
                                                </button>
                                                @if($swatchVariant)
                                                    <span class="option-swatch__tip">{{ $value }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <button type="button" class="option-btn" data-value="{{ $value }}">{{ $value }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <p class="stock-note" data-stock-note>@if($activeFields->isEmpty()) &nbsp; @else Select all options to see availability @endif</p>

                        <div class="buy-row">
                            <div class="qty-stepper">
                                <button type="button" data-step="down">&minus;</button>
                                <input type="number" name="qty" value="1" min="1" max="99" data-qty-input>
                                <button type="button" data-step="up">+</button>
                            </div>
                            <button type="submit" class="btn btn-accent" data-add-to-cart-button {{ $activeFields->isNotEmpty() ? 'disabled' : '' }}>Add to cart</button>
                        </div>
                    </div>
                </form>

                <script id="variant-data" type="application/json">{!! $product->variants->map(fn ($v) => [
                    'id' => $v->id,
                    'size' => $v->size,
                    'color' => $v->color,
                    'style' => $v->style,
                    'stock_qty' => $v->stock_qty,
                    'price_override' => $v->price_override,
                    'discount_percent' => $v->discount_percent,
                    'image' => $v->imageUrl(),
                ])->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
            @else
                <div class="buy-row">
                    <a href="{{ route('login') }}" class="btn btn-primary">Log in to purchase</a>
                </div>
            @endauth
        </div>
    </div>

    <div class="lightbox" data-lightbox hidden>
        <div class="lightbox__frame">
            <button type="button" class="lightbox__close" data-lightbox-close aria-label="Close preview">✕</button>
            <img src="" alt="{{ $product->name }}" class="lightbox__img" data-lightbox-img>
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="related-section">
            <h2>You might also likes</h2>
            <div class="product-grid">
                @foreach($related as $item)
                    <a href="{{ route('products.show', $item->slug) }}" class="product-card">
                        <div class="product-card__media">
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" loading="lazy">
                        </div>
                        <div class="product-card__body">
                            <h3>{{ $item->name }}</h3>
                            @include('products._price', ['product' => $item])
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@if(request()->routeIs('home') && $discountedProducts->isNotEmpty())
    <div class="deals-banner">
        <div class="deals-banner__label">
            <span class="deals-banner__fire">🔥</span>
            <span>Big<br>Discount</span>
        </div>
        <div class="deals-banner__viewport">
            <div class="deals-banner__track">
                @foreach(range(1, 2) as $copy)
                    @foreach($discountedProducts as $product)
                        <a href="{{ route('products.show', $product->slug) }}" class="deals-banner__item">
                            <img src="{{ $product->imageUrl() }}" alt="">
                            <span class="deals-banner__info">
                                <span class="deals-banner__name">{{ $product->name }}</span>
                                <span class="deals-banner__prices">
                                    <span class="deals-banner__original">${{ number_format($product->price, 2) }}</span>
                                    <span class="deals-banner__price">${{ number_format($product->discountedPrice(), 2) }}</span>
                                </span>
                            </span>
                            <span class="deals-banner__pct">-{{ $product->discount_percent }}%</span>
                        </a>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
@endif

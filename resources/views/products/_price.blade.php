@if($product->hasDiscount())
    <div class="product-card__price">
        <span class="price-original">${{ number_format($product->price, 2) }}</span>
        <span class="price-discounted">${{ number_format($product->discountedPrice(), 2) }}</span>
    </div>
@else
    <div class="product-card__price">${{ number_format($product->price, 2) }}</div>
@endif

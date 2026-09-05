@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Checkout</h1>
    </div>

    <div class="cart-layout">
        <div class="form-card">
            <form method="POST" action="{{ route('checkout.store') }}">
                @csrf

                <div class="form-group">
                    <label for="shipping_name">Full name</label>
                    <input type="text" id="shipping_name" name="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="shipping_address">Shipping address</label>
                    <input type="text" id="shipping_address" name="shipping_address" value="{{ old('shipping_address', auth()->user()->address) }}" required>
                </div>

                <div class="form-group">
                    <label for="shipping_phone">Phone number</label>
                    <input type="tel" id="shipping_phone" name="shipping_phone" value="{{ old('shipping_phone', auth()->user()->phone) }}" required>
                </div>

                @unless(auth()->user()->phone && auth()->user()->address)
                    <p style="color: var(--color-text-muted); font-size: 0.82rem; margin-top: -10px; margin-bottom: 20px;">
                        Tip: save these in <a href="{{ route('profile.edit') }}" style="color: var(--color-primary); font-weight: 600;">My Account</a> so they're pre-filled next time.
                    </p>
                @endunless

                <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 20px;">
                    This is a demo checkout &mdash; no payment is collected. Placing the order will reserve stock and create an order you can track under "My Orders".
                </p>

                <button type="submit" class="btn btn-accent btn-block">Place order &mdash; ${{ number_format($total, 2) }}</button>
            </form>
        </div>

        <div class="summary-card">
            <h3>Order summary</h3>
            @foreach($cartItems as $item)
                <div class="order-line">
                    <span>{{ $item->variant->product->name }} ({{ $item->variant->label() ?: 'Standard' }}) &times; {{ $item->qty }}</span>
                    <span>${{ number_format($item->subtotal(), 2) }}</span>
                </div>
            @endforeach
            <div class="summary-row total">
                <span>Total</span>
                <span>${{ number_format($total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

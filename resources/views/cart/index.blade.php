@extends('layouts.app')

@section('title', 'Your cart')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Your cart</h1>
    </div>

    @if($cartItems->isEmpty())
        <div class="empty-state">
            <h3>Your cart is empty</h3>
            <p>Browse our collection and find something you like.</p>
            <a href="{{ route('home') }}" class="btn btn-primary" style="margin-top:16px;">Continue shopping</a>
        </div>
    @else
        <div class="cart-layout">
            <div>
                @foreach($cartItems as $item)
                    <div class="cart-item">
                        <div class="cart-item__media">
                            <img src="{{ $item->variant->product->imageUrl() }}" alt="{{ $item->variant->product->name }}">
                        </div>
                        <div>
                            <div class="cart-item__name">{{ $item->variant->product->name }}</div>
                            <div class="cart-item__meta">{{ $item->variant->label() ?: 'Standard' }}</div>
                            <form method="POST" action="{{ route('cart.update', $item) }}" style="margin-top:8px;">
                                @csrf
                                @method('PATCH')
                                <div class="qty-stepper">
                                    <button type="button" data-step="down">&minus;</button>
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="{{ $item->variant->stock_qty }}" onchange="this.form.submit()">
                                    <button type="button" data-step="up">+</button>
                                </div>
                            </form>
                        </div>
                        <div class="cart-item__price">${{ number_format($item->subtotal(), 2) }}</div>
                        <form method="POST" action="{{ route('cart.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="summary-card">
                <h3>Order summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>${{ number_format($total, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>${{ number_format($total, 2) }}</span>
                </div>
                <a href="{{ route('checkout.index') }}" class="btn btn-accent btn-block">Proceed to checkout</a>
            </div>
        </div>
    @endif
</div>
@endsection

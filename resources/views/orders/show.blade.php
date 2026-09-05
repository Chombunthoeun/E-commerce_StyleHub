@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Order #{{ $order->id }}</h1>
        <span class="status-badge status-{{ $order->statusColor() }}">{{ $order->status }}</span>
    </div>

    <div class="cart-layout">
        <div class="panel">
            <h3 style="margin-bottom: 16px;">Items</h3>
            @foreach($order->items as $item)
                <div class="order-line">
                    <span>{{ $item->product_name }} ({{ $item->variant_label ?: 'Standard' }}) &times; {{ $item->qty }}</span>
                    <span>${{ number_format($item->subtotal(), 2) }}</span>
                </div>
            @endforeach
        </div>

        <div class="summary-card">
            <h3>Shipping details</h3>
            <p style="color: var(--color-text-muted); font-size: 0.9rem;">
                {{ $order->shipping_name }}<br>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_phone }}
            </p>
            <div class="summary-row total">
                <span>Total</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection

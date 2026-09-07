@extends('layouts.admin')

@section('title', 'Order #'.$order->id)

@section('content')
<div class="page-header">
    <h1>Order #{{ $order->id }}</h1>
    <span class="status-badge status-{{ $order->statusColor() }}">{{ $order->status }}</span>
</div>

<div class="cart-layout">
    <div class="panel">
        <h3 style="margin-bottom:16px;">Items</h3>
        @foreach($order->items as $item)
            <div class="order-line">
                <span style="display:flex; align-items:center; gap:12px;">
                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->product_name }}" style="width:48px; height:48px; object-fit:cover; border-radius:8px; flex-shrink:0;">
                    <span>{{ $item->product_name }} ({{ $item->variant_label ?: 'Standard' }}) &times; {{ $item->qty }}</span>
                </span>
                <span>${{ number_format($item->subtotal(), 2) }}</span>
            </div>
        @endforeach
        <div class="summary-row total">
            <span>Total</span>
            <span>${{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <div class="summary-card">
        <h3>Customer</h3>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom:20px;">
            {{ $order->user->name }}<br>
            {{ $order->user->email }}<br><br>
            {{ $order->shipping_name }}<br>
            {{ $order->shipping_address }}<br>
            {{ $order->shipping_phone }}
        </p>

        <form method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label for="status">Update status</label>
                <select id="status" name="status" onchange="this.form.submit()">
                    @foreach(['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'] as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
@endsection

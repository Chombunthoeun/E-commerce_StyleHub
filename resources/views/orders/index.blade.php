@extends('layouts.app')

@section('title', 'My orders')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>My orders</h1>
    </div>

    @if($orders->isEmpty())
        <div class="empty-state">
            <h3>No orders yet</h3>
            <p>Your placed orders will show up here.</p>
            <a href="{{ route('home') }}" class="btn btn-primary" style="margin-top:16px;">Start shopping</a>
        </div>
    @else
        @foreach($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="order-card" style="display:block;">
                <div class="order-card__header">
                    <div>
                        <strong>Order #{{ $order->id }}</strong>
                        <span style="color: var(--color-text-muted); font-size: 0.85rem;"> &middot; {{ $order->created_at->format('M j, Y') }}</span>
                    </div>
                    <span class="status-badge status-{{ $order->statusColor() }}">{{ $order->status }}</span>
                </div>
                <div style="color: var(--color-text-muted); font-size: 0.9rem;">{{ $order->items->count() }} item(s) &mdash; ${{ number_format($order->total, 2) }} &middot; {{ $order->paymentMethodLabel() }}: {{ $order->paymentStatusLabel() }}</div>
            </a>
        @endforeach
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="page-header">
    <h1>Orders</h1>
</div>

<div class="panel">
    <div class="filter-tabs">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm {{ $payment === '' ? 'btn-primary' : 'btn-outline' }}">All</a>
        <a href="{{ route('admin.orders.index', ['payment' => 'review']) }}" class="btn btn-sm {{ $payment === 'review' ? 'btn-primary' : 'btn-outline' }}">Awaiting review ({{ $awaitingReview }})</a>
        <a href="{{ route('admin.orders.index', ['payment' => 'khqr']) }}" class="btn btn-sm {{ $payment === 'khqr' ? 'btn-primary' : 'btn-outline' }}">KHQR</a>
        <a href="{{ route('admin.orders.index', ['payment' => 'cod']) }}" class="btn btn-sm {{ $payment === 'cod' ? 'btn-primary' : 'btn-outline' }}">Cash on delivery</a>
    </div>

    @if($orders->isEmpty())
        <p style="color: var(--color-text-muted);">No orders found.</p>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->user->name }}</td>
                        <td>{{ $order->items->count() }}</td>
                        <td>${{ number_format($order->total, 2) }}</td>
                        <td>
                            {{ $order->isKhqr() ? 'KHQR' : 'COD' }}
                            <span class="status-badge status-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
                        </td>
                        <td><span class="status-badge status-{{ $order->statusColor() }}">{{ $order->status }}</span></td>
                        <td>{{ $order->created_at->format('M j, Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline">View</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
</div>
@endsection

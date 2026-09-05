@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
<div class="page-header">
    <h1>Orders</h1>
</div>

<div class="panel">
    @if($orders->isEmpty())
        <p style="color: var(--color-text-muted);">No orders placed yet.</p>
    @else
        <div style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
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

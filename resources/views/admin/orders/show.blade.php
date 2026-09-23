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

        <h3 style="margin-top:24px;">Payment</h3>
        <div class="summary-row">
            <span>Method</span>
            <span>{{ $order->paymentMethodLabel() }}</span>
        </div>
        <div class="summary-row">
            <span>Status</span>
            <span class="status-badge status-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
        </div>

        @if($order->payment_proof)
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-top:12px;">
                Screenshot sent {{ $order->payment_submitted_at?->format('M j, Y g:i A') }}. Check that ${{ number_format($order->total, 2) }} arrived in your ACLEDA account before confirming.
            </p>
            <a href="{{ route('admin.orders.payment-proof', $order) }}" target="_blank" rel="noopener">
                <img src="{{ route('admin.orders.payment-proof', $order) }}" alt="Payment screenshot for order #{{ $order->id }}" class="payment-proof-img">
            </a>
        @elseif($order->isKhqr())
            <p style="color: var(--color-text-muted); font-size: 0.85rem; margin-top:12px;">The customer hasn't uploaded a payment screenshot yet.</p>
        @endif

        <form method="POST" action="{{ route('admin.orders.payment.update', $order) }}">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label for="payment_status">Update payment</label>
                <select id="payment_status" name="payment_status" onchange="this.form.submit()">
                    @foreach(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'rejected' => 'Proof rejected'] as $value => $label)
                        <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                    @endforeach
                    @if($order->payment_status === 'submitted')
                        <option value="" selected disabled>Awaiting review</option>
                    @endif
                </select>
            </div>
        </form>
    </div>
</div>
@endsection

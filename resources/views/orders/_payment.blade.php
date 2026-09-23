<div class="summary-card" style="margin-top: 20px;">
    <h3>Payment</h3>
    <div class="summary-row">
        <span>Method</span>
        <span>{{ $order->paymentMethodLabel() }}</span>
    </div>
    <div class="summary-row">
        <span>Status</span>
        <span class="status-badge status-{{ $order->paymentStatusColor() }}">{{ $order->paymentStatusLabel() }}</span>
    </div>

    @if($order->isKhqr())
        @if($order->payment_status === 'paid')
            <p style="color: var(--color-text-muted); font-size: 0.9rem;">Thank you! Your payment has been confirmed.</p>
        @elseif($order->canUploadPaymentProof())
            <div class="khqr-box">
                <img src="{{ asset('images/khqr.jpg') }}" alt="KHQR payment code">
                <div class="khqr-amount">${{ number_format($order->total, 2) }}</div>
                <p style="color: var(--color-text-muted); font-size: 0.85rem;">
                    Scan with any KHQR bank app and enter <strong>${{ number_format($order->total, 2) }}</strong>.
                    Put <strong>Order #{{ $order->id }}</strong> in the payment note.
                </p>
            </div>

            @if($order->payment_status === 'rejected')
                <div class="alert alert-error">Your last screenshot could not be confirmed. Please upload a clear screenshot of the successful payment.</div>
            @endif

            <form method="POST" action="{{ route('orders.payment-proof.store', $order) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="payment_proof">{{ $order->payment_proof ? 'Replace payment screenshot' : 'Upload payment screenshot' }}</label>
                    <input type="file" id="payment_proof" name="payment_proof" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <button type="submit" class="btn btn-accent btn-block">Send payment proof</button>
            </form>
        @endif

        @if($order->payment_proof)
            <p style="margin-top: 16px; font-size: 0.85rem; color: var(--color-text-muted);">
                Sent {{ $order->payment_submitted_at?->format('M j, Y g:i A') }}
            </p>
            <a href="{{ route('orders.payment-proof', $order) }}" target="_blank" rel="noopener">
                <img src="{{ route('orders.payment-proof', $order) }}" alt="Your payment screenshot" class="payment-proof-img">
            </a>
        @endif
    @else
        <p style="color: var(--color-text-muted); font-size: 0.9rem;">Please have ${{ number_format($order->total, 2) }} in cash ready when your order arrives.</p>
    @endif
</div>

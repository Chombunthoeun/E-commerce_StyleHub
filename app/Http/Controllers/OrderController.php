<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->with('items')->orderByDesc('created_at')->get();

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items');

        return view('orders.show', ['order' => $order]);
    }

    public function uploadPaymentProof(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->canUploadPaymentProof(), 422, 'This order does not accept a payment proof.');

        $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldProof = $order->payment_proof;

        // Receipts are private: stored on the local disk and served only through
        // paymentProof() below, never from /storage.
        $order->update([
            'payment_proof' => $request->file('payment_proof')->store('payment-proofs', 'local'),
            'payment_status' => 'submitted',
            'payment_submitted_at' => now(),
        ]);

        if ($oldProof) {
            Storage::disk('local')->delete($oldProof);
        }

        return back()->with('status', 'Payment screenshot uploaded. We will confirm your payment soon.');
    }

    public function paymentProof(Request $request, Order $order): StreamedResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->payment_proof && Storage::disk('local')->exists($order->payment_proof), 404);

        return Storage::disk('local')->response($order->payment_proof);
    }
}

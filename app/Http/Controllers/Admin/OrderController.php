<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $payment = $request->string('payment')->toString();

        $orders = Order::with('user', 'items')
            ->when($payment === 'khqr', fn ($query) => $query->where('payment_method', 'khqr'))
            ->when($payment === 'cod', fn ($query) => $query->where('payment_method', 'cod'))
            ->when($payment === 'review', fn ($query) => $query->where('payment_status', 'submitted'))
            ->orderByDesc('created_at')
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'payment' => $payment,
            'awaitingReview' => Order::where('payment_status', 'submitted')->count(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load('items', 'user');

        return view('admin.orders.show', ['order' => $order]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Pending,Processing,Shipped,Completed,Cancelled'],
        ]);

        $order->update($validated);

        return back()->with('status', 'Order status updated.');
    }

    public function updatePayment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_status' => ['required', 'in:unpaid,paid,rejected'],
        ]);

        $order->update($validated);

        return back()->with('status', 'Payment status updated.');
    }

    public function paymentProof(Order $order): StreamedResponse
    {
        abort_unless($order->payment_proof && Storage::disk('local')->exists($order->payment_proof), 404);

        return Storage::disk('local')->response($order->payment_proof);
    }
}

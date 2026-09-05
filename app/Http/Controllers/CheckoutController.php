<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cartItems = $request->user()->cartItems()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', [
            'cartItems' => $cartItems,
            'total' => $cartItems->sum(fn (CartItem $item) => $item->subtotal()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:50'],
        ]);

        $cartItems = $request->user()->cartItems()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        foreach ($cartItems as $item) {
            if ($item->qty > $item->variant->stock_qty) {
                return redirect()->route('cart.index')
                    ->withErrors(['stock' => "\"{$item->variant->product->name}\" no longer has enough stock."]);
            }
        }

        $order = DB::transaction(function () use ($request, $validated, $cartItems) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => 'Pending',
                'total' => $cartItems->sum(fn (CartItem $item) => $item->subtotal()),
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_phone' => $validated['shipping_phone'],
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->variant->product_id,
                    'product_variant_id' => $item->variant->id,
                    'product_name' => $item->variant->product->name,
                    'variant_label' => $item->variant->label(),
                    'qty' => $item->qty,
                    'price' => $item->variant->price(),
                ]);

                $item->variant->decrement('stock_qty', $item->qty);
                $item->delete();
            }

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('status', 'Order placed successfully!');
    }
}

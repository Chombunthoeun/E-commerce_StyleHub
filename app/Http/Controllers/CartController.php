<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cartItems = $request->user()->cartItems()->with('variant.product')->get();

        return view('cart.index', [
            'cartItems' => $cartItems,
            'total' => $cartItems->sum(fn (CartItem $item) => $item->subtotal()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($validated['product_variant_id']);

        if ($variant->stock_qty < 1) {
            return back()->withErrors(['product_variant_id' => 'That option is out of stock.']);
        }

        $cartItem = CartItem::firstOrNew([
            'user_id' => $request->user()->id,
            'product_variant_id' => $variant->id,
        ]);

        $newQty = ($cartItem->exists ? $cartItem->qty : 0) + $validated['qty'];
        $cartItem->qty = min($newQty, $variant->stock_qty);
        $cartItem->user_id = $request->user()->id;
        $cartItem->product_variant_id = $variant->id;
        $cartItem->save();

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwner($request, $cartItem);

        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $cartItem->update([
            'qty' => min($validated['qty'], $cartItem->variant->stock_qty),
        ]);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeOwner($request, $cartItem);

        $cartItem->delete();

        return back()->with('status', 'Item removed.');
    }

    private function authorizeOwner(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->user_id === $request->user()->id, 403);
    }
}

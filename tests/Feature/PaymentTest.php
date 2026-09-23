<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function orderFor(User $user, string $method = 'khqr'): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'status' => 'Pending',
            'payment_method' => $method,
            'payment_status' => 'unpaid',
            'total' => 25,
            'shipping_name' => 'Test',
            'shipping_address' => 'Phnom Penh',
            'shipping_phone' => '012345678',
        ]);
    }

    public function test_checkout_saves_chosen_payment_method(): void
    {
        $user = User::factory()->create();
        Category::create(['name' => 'Shoes', 'slug' => 'shoes']);
        $product = Product::factory()->create(['is_active' => true]);
        $variant = $product->variants()->first();
        $variant->update(['stock_qty' => 5]);
        $user->cartItems()->create(['product_variant_id' => $variant->id, 'qty' => 1]);

        $this->actingAs($user)->post('/checkout', [
            'shipping_name' => 'Test',
            'shipping_address' => 'Phnom Penh',
            'shipping_phone' => '012345678',
            'payment_method' => 'khqr',
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'payment_method' => 'khqr', 'payment_status' => 'unpaid']);
    }

    public function test_checkout_rejects_unknown_payment_method(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/checkout', [
            'shipping_name' => 'Test',
            'shipping_address' => 'Phnom Penh',
            'shipping_phone' => '012345678',
            'payment_method' => 'bitcoin',
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_customer_can_upload_payment_proof_and_admin_can_view_it(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->orderFor($customer);

        $this->actingAs($customer)->post("/orders/{$order->id}/payment-proof", [
            'payment_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('submitted', $order->payment_status);
        Storage::disk('local')->assertExists($order->payment_proof);

        $this->actingAs($admin)->get("/admin/orders/{$order->id}/payment-proof")->assertOk();
        $this->actingAs($admin)->get('/admin/orders?payment=review')->assertOk()->assertSee("#{$order->id}");

        $this->actingAs($admin)->patch("/admin/orders/{$order->id}/payment", ['payment_status' => 'paid'])->assertRedirect();
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_other_customers_cannot_see_or_upload_proof(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = $this->orderFor($owner);

        $this->actingAs($stranger)->post("/orders/{$order->id}/payment-proof", [
            'payment_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertForbidden();

        $this->actingAs($stranger)->get("/orders/{$order->id}/payment-proof")->assertForbidden();
    }

    public function test_cash_on_delivery_order_does_not_accept_proof(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create();
        $order = $this->orderFor($customer, 'cod');

        $this->actingAs($customer)->post("/orders/{$order->id}/payment-proof", [
            'payment_proof' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertStatus(422);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['order_id', 'product_id', 'product_variant_id', 'product_name', 'variant_label', 'variant_image', 'qty', 'price'])]
class OrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Image for this line: the variant photo captured at purchase time,
     * falling back to the live variant image, then the product image.
     */
    public function imageUrl(): string
    {
        if ($this->variant_image) {
            return asset('storage/'.$this->variant_image);
        }

        return $this->variant?->imageUrl()
            ?? $this->product?->imageUrl()
            ?? asset('images/placeholder.svg');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function subtotal(): float
    {
        return (float) $this->price * $this->qty;
    }
}

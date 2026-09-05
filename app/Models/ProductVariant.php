<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['product_id', 'size', 'color', 'style', 'image', 'sku', 'stock_qty', 'price_override', 'discount_percent'])]
class ProductVariant extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function basePrice(): float
    {
        return (float) ($this->price_override ?? $this->product->price);
    }

    public function effectiveDiscountPercent(): ?int
    {
        return $this->discount_percent !== null ? $this->discount_percent : $this->product->discount_percent;
    }

    public function hasDiscount(): bool
    {
        return ! empty($this->effectiveDiscountPercent());
    }

    public function price(): float
    {
        $base = $this->basePrice();
        $discount = $this->effectiveDiscountPercent();

        if (empty($discount)) {
            return $base;
        }

        return round($base * (1 - $discount / 100), 2);
    }

    public function label(): string
    {
        return collect([$this->size, $this->color, $this->style])
            ->filter()
            ->implode(' / ');
    }

    public function inStock(): bool
    {
        return $this->stock_qty > 0;
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}

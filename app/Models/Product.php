<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'discount_percent', 'image', 'low_stock_threshold', 'is_active'])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function totalStock(): int
    {
        return $this->variants->sum('stock_qty');
    }

    public function isLowStock(): bool
    {
        return $this->totalStock() <= $this->low_stock_threshold;
    }

    public function hasDiscount(): bool
    {
        return ! empty($this->discount_percent) && $this->discount_percent > 0;
    }

    public function discountedPrice(): float
    {
        if (! $this->hasDiscount()) {
            return (float) $this->price;
        }

        return round((float) $this->price * (1 - $this->discount_percent / 100), 2);
    }

    public function imageUrl(): string
    {
        return $this->image
            ? asset('storage/'.$this->image)
            : asset('images/placeholder.svg');
    }
}

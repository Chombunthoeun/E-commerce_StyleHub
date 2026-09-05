<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'status', 'total', 'shipping_name', 'shipping_address', 'shipping_phone'])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'Pending' => 'amber',
            'Processing' => 'blue',
            'Shipped' => 'violet',
            'Completed' => 'green',
            'Cancelled' => 'red',
            default => 'gray',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'status', 'payment_method', 'payment_status', 'payment_proof', 'payment_submitted_at', 'total', 'shipping_name', 'shipping_address', 'shipping_phone'])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'payment_submitted_at' => 'datetime',
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

    public function isKhqr(): bool
    {
        return $this->payment_method === 'khqr';
    }

    public function paymentMethodLabel(): string
    {
        return $this->isKhqr() ? 'KHQR (ACLEDA)' : 'Cash on delivery';
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'submitted' => 'Awaiting review',
            'paid' => 'Paid',
            'rejected' => 'Proof rejected',
            default => 'Unpaid',
        };
    }

    public function paymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'submitted' => 'blue',
            'paid' => 'green',
            'rejected' => 'red',
            default => 'amber',
        };
    }

    public function canUploadPaymentProof(): bool
    {
        return $this->isKhqr()
            && in_array($this->payment_status, ['unpaid', 'submitted', 'rejected'], true)
            && $this->status !== 'Cancelled';
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

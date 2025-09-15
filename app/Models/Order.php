<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'shipping_address',
        'billing_address',
        'payment_method',
        'payment_status',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function generateOrderNumber()
    {
        $this->order_number = 'ORD-' . date('Y') . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        $this->save();
    }
}

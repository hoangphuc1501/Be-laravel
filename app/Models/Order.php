<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'userId', 'voucherId', 'code', 'note', 'totalPrice',
        'shippingAddress', 'paymentStatus', 'paymentMethod', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(UserClient::class, 'userId');
    }

    public function orderItems()
{
    return $this->hasMany(OrderItem::class, 'orderId', 'id');
}
    
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

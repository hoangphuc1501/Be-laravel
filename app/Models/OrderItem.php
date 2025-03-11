<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'orderitems';

    protected $fillable = [
        'orderId',
        'productVariantId',
        'sizeId',
        'colorId',
        'price',
        'quantity',
        'subTotal'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId');
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class, 'productVariantId', 'id');
    }

    public function size()
    {
        return $this->belongsTo(Size::class, 'sizeId');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'colorId');
    }

    public $timestamps = false; 
}

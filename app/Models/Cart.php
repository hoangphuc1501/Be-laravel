<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'userId',
        'productVariantId',
        'sizeId',
        'colorId',
        'quantity'
    ];


    public function productVariant()
    {
        return $this->belongsTo(ProductVariants::class, 'productVariantId', 'id');
    }
    public function user()
{
    return $this->belongsTo(UserClient::class, 'userId', 'id');
}
public function color()
{
    return $this->belongsTo(Color::class, 'colorId', 'id');
}

// Quan hệ với Kích thước
public function size()
{
    return $this->belongsTo(Size::class, 'sizeId', 'id');
}
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

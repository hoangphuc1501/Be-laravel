<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariants extends Model
{
    use HasFactory;
    protected $table = 'productsvariants';
    protected $fillable = [
        'ProductID', 'price', 'status', 'code', 'stock',
        'discount', 'specialPrice', 'size', 'color',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'ProductID');
    }
    
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'productVariantID');
    }
    public $timestamps = false;
}

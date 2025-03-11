<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariants extends Model
{
    use HasFactory;
    protected $table = 'productsvariants';
    protected $fillable = [
        'ProductID', 'price', 'status',
        'discount', 'specialPrice', 'stock', 'deleted'
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'ProductID');
    }
    public function variationOptions()
    {
        return $this->hasMany(VariationOptions::class, 'variantId', 'id');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'productVariantID', 'id');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'variantoptions', 'variantId', 'colorId');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'variantoptions', 'variantId', 'sizeId');
    }
    
    public $timestamps = false;
}

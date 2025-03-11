<?php

namespace App\Models;

use Cloudinary\Tag\Sizes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class VariationOptions extends Model
{
    use HasFactory;

    protected $table = 'variantoptions';

    protected $fillable = [
        'variantId',
        'sizeId',
        'colorId',
        'stock'
    ];

    public $timestamps = false;

    // Quan hệ với bảng ProductVariants
    public function variant()
    {
        return $this->belongsTo(ProductVariants::class, 'variantId', 'id');
    }

    // Quan hệ với bảng Sizes
    public function size()
    {
        return $this->belongsTo(Size::class, 'sizeId', 'id');
    }

    // Quan hệ với bảng Colors
    public function color()
    {
        return $this->belongsTo(Color::class, 'colorId', 'id');;
    }
}

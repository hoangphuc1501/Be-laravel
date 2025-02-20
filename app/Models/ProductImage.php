<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'productimage';

    protected $fillable = [
        'productVariantID', 'image', 'imageName', 'status', 'deleted'
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariants::class, 'productVariantID');
    }
    public $timestamps = false;
}

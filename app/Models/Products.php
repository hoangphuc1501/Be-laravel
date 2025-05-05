<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'title', 'brandID', 'description', 'featured', 'status', 'deleted','slug',
        'categoriesID', 'codeProduct', 'position', 'descriptionPromotion'
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariants::class, 'ProductID')->with('images');
    }
    public function specifications()
    {
        return $this->hasOne(ProductSpecification::class, 'productId');
    }
    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brandID');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'categoriesID');
    }
    public function reviews()
{
    return $this->hasMany(Review::class, 'productId');
}
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'productcategories';
    protected $fillable = [
        'name', 'image', 'description', 'status', 'parentID', 'position', 'deleted', 'slug'
    ];

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parentID');
    }

    // Lấy danh mục con
    public function children()
{
    return $this->hasMany(ProductCategory::class, 'parentID')->where('deleted', false)->where('status', 1);
}

    
    public function products()
    {
        return $this->hasMany(Products::class, 'categoriesID');
    }
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';

}

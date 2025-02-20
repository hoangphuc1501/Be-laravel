<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;
    protected $table = 'newscategories';
    protected $fillable = [
        'name', 'image', 'description', 'status', 'parentID', 
        'deleted', 'position', 'slug'
    ];

    public function parent()
    {
        return $this->belongsTo(NewsCategory::class, 'parentID');
    }

    // Lấy danh mục con
    public function children()
    {
        return $this->hasMany(NewsCategory::class, 'parentID');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

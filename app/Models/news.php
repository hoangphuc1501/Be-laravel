<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class news extends Model
{
    protected $table = 'news';
    protected $fillable = [
        'title', 'content', 'image', 'slug', 'author',
        'position', 'deleted', 'newsCategory', 'status', 'featured'
    ];

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'newsCategory');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

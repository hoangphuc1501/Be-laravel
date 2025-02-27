<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class news extends Model
{
    protected $table = 'news';
    use HasFactory;
    protected $fillable = [
        'title', 
        'content', 
        'image', 
        'slug', 
        'author', 
        'position', 
        'newsCategory', 
        'status', 
        'featured', 
        'deleted'
    ];

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'newsCategory');
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('deleted', 0);
    }

    public function scopeOnlyDeleted($query)
    {
        return $query->where('deleted', 1);
    }

    public function restoreNews()
    {
        $this->deleted = 0;
        $this->save();
    }
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments'; 

    protected $fillable = ['content', 'deleted', 'userID', 'productID'];

    public function scopeVisible($query)
    {
        return $query->where('deleted', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userID');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'productID');
    }
    public $timestamps = true; 

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = null; 
}


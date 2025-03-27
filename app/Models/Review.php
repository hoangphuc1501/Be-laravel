<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $fillable = ['userId', 'productId', 'content', 'star'];

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    public function user()
    {
        return $this->belongsTo(UserClient::class, 'userId')->select('id', 'fullname', 'email');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'productId')->select('id', 'title');
    }
    
}

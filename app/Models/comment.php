<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments'; 

    protected $fillable = ['content', 'userId', 'productId'];


    public function user()
    {
        return $this->belongsTo(UserClient::class, 'userId')->select('id', 'fullname');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'productId')->select('id', 'title');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}


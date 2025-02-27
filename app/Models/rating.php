<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = ['content', 'star', 'deleted', 'userID', 'productID'];

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
        return $this->belongsTo(Product::class, 'productID');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    use HasFactory;

    protected $table = 'productspecifications';

    protected $fillable = [
        'productId', 'origin', 'stiffness', 'balance_point', 'length',
        'tension', 'weight', 'racketHandleSize', 'frameMaterial',
        'shaftMaterial', 'color', 'deleted', 'status'
    ];

    public function product()
    {
        return $this->belongsTo(Products::class, 'productId');
    }
    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

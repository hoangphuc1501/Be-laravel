<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $table = 'vouchers';

    protected $fillable = [
        'name','code', 'discountType', 'discountValue', 'description', 'status', 
        'minOrderValue', 'maxOrderValue', 'deleted', 'maxDiscount', 'startDate', 
        'endDate', 'usageLimit', 'numberOfUses'
    ];

    protected $casts = [
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
}

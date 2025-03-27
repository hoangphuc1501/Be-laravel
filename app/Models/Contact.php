<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'contacts';

    protected $fillable = [
        'fullName', 'email', 'phone', 'content',
        'status', 'position', 'deleted', 'deletedAt'
    ];

    public $timestamps = true;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
}

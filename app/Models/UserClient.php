<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserClient extends Model
{
    use HasFactory;
    protected $table = 'users';
    protected $fillable = [
        'fullname', 'email', 'password', 'address', 'phone',
        'role', 'image', 'birthday', 'gender', 'status',
        'position', 'deleted'
    ];

    protected $hidden = [
        'password',
    ];

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
    const DELETED_AT = 'deletedAt';
}

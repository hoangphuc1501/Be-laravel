<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $table = 'userroles';
    protected $fillable = [
        'userId', 'roleId',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

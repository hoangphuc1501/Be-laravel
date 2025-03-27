<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';
    protected $fillable = [
        'name', 'description', 'module', 'slug'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rolepermissions', 'permissionId', 'roleId');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

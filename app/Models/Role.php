<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';
    protected $fillable = [
        'name', 'description'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'rolepermissions', 'roleId', 'permissionId');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'userroles', 'roleId', 'userId');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

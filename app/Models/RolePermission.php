<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'rolepermissions';
    protected $fillable = [
        'roleId', 'permissionId', 
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionId');
    }

    public $timestamps = true; 

    const CREATED_AT = 'createdAt'; 
    const UPDATED_AT = 'updatedAt'; 
}

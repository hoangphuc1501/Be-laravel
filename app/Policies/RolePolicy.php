<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_role'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Role $role): bool
    {
        return $user->hasPermission('view_role'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_role'); 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Role $role): bool
    {
        return $user->hasPermission('update_role'); 
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Role $role): bool
    {
        return $user->hasPermission('delete_role'); 
    }
}

<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_permission'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Permission $permission): bool
    {
        return $user->hasPermission('view_permission'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_permission'); 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Permission $permission): bool
    {
        return $user->hasPermission('update_permission'); 
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Permission $permission): bool
    {
        return $user->hasPermission('delete_permission'); 
    }

}

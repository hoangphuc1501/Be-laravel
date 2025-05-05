<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_account'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, UserClient $userClient): bool
    {
        return $user->hasPermission('view_account'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_account');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, UserClient $userClient): bool
    {
        return $user->hasPermission('edit_account');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, UserClient $userClient): bool
    {
        return $user->hasPermission('softDelete_account');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, UserClient $userClient): bool
    {
        return $user->hasPermission('restore_account');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(UserClient $user, UserClient $userClient): bool
    {
        return $user->hasPermission('delete_account');
    }
}

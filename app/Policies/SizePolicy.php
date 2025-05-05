<?php

namespace App\Policies;

use App\Models\Size;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class SizePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_size'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Size $size): bool
    {
        return $user->hasPermission('view_size'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_size');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Size $size): bool
    {
        return $user->hasPermission('edit_size');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Size $size): bool
    {
        return $user->hasPermission('softDelete_size');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, Size $size): bool
    {
        return $user->hasPermission('restore_size');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(UserClient $user, Size $size): bool
    {
        return $user->hasPermission('delete_size');
    }
}

<?php

namespace App\Policies;

use App\Models\Color;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class ColorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_color'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Color $color): bool
    {
        return $user->hasPermission('view_color'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_color');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Color $color): bool
    {
        return $user->hasPermission('edit_color');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Color $color): bool
    {
        return $user->hasPermission('softDelete_color');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, Color $color): bool
    {
        return $user->hasPermission('restore_color');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(UserClient $user, Color $color): bool
    {
        return $user->hasPermission('delete_color');
    }
}

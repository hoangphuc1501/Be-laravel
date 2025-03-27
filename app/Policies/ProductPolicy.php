<?php

namespace App\Policies;

use App\Models\Products;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    // public function viewAny(User $user): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Products $product): bool
    {
        return $user->hasPermission('view_product');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_product');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Products $product): bool
    {
        return $user->hasPermission('edit_product');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Products $product): bool
    {
        return $user->hasPermission('delete_product');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, Products $product): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Products $product): bool
    {
        //
    }
}

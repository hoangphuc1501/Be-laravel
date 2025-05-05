<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\ProductCategory;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_productCategory');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, ProductCategory $category): bool
    {
        return $user->hasPermission('view_productCategory');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_productCategory');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, ProductCategory $category): bool
    {
        return $user->hasPermission('edit_productCategory');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, ProductCategory $category): bool
    {
        return $user->hasPermission('delete_productCategory');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, ProductCategory $category): bool
    {
        return $user->hasPermission('restore_productCategory');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function SoftDelete(UserClient $user, ProductCategory $category): bool
    {
        return $user->hasPermission('softDelete_productCatego');
    }
}

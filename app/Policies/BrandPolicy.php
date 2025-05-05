<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class BrandPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
{
    return $user->hasPermission('view_brand'); 
}

    public function view(UserClient $user, $brand): bool
    {
        return $user->hasPermission('view_brand');
    }
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_brand');
    }
    public function update(UserClient $user, $brand): bool
    {
        return $user->hasPermission('edit_brand');
    }

    public function delete(UserClient $user, $brand): bool
    {
        return $user->hasPermission('softDelete_brand');
    }

    public function restore(UserClient $user, $brand): bool
    {
        return $user->hasPermission('restore_brand');
    }

    public function forceDelete(UserClient $user, $brand): bool
    {
        return $user->hasPermission('delete_brand');
    }

}

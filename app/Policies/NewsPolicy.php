<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class NewsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_news'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, News $news): bool
    {
        return $user->hasPermission('view_news'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_news');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, News $news): bool
    {
        return $user->hasPermission('edit_news');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, News $news): bool
    {
        return $user->hasPermission('softDelete_news');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, News $news): bool
    {
        return $user->hasPermission('restore_news');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(UserClient $user, News $news): bool
    {
        return $user->hasPermission('delete_news');
    }
}

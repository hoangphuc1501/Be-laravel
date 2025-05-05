<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_comment'); 
    }

    /**
     * Determine whether the user can view the model.
     */
    
    public function delete(UserClient $user, Comment $comment): bool
    {
        return $user->hasPermission('delete_comment'); 
    }
}

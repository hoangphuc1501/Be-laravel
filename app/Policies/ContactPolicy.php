<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class ContactPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_contact'); 
    }

    public function delete(UserClient $user, Contact $contact): bool
    {
        return $user->hasPermission('delete_contact'); 
    }

}

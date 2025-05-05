<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Models\UserClient;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_order');
    }

    public function view(UserClient $user, Order $order): bool
    {
        return $user->hasPermission('view_order');
    }

    public function update(UserClient $user, Order $order): bool
    {
        return $user->hasPermission('edit_order');
    }

}

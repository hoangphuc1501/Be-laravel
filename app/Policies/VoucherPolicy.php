<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserClient;
use App\Models\Voucher;
use Illuminate\Auth\Access\Response;

class VoucherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(UserClient $user): bool
    {
        return $user->hasPermission('view_voucher');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(UserClient $user, Voucher $voucher): bool
    {
        return $user->hasPermission('view_voucher'); 
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(UserClient $user): bool
    {
        return $user->hasPermission('create_voucher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(UserClient $user, Voucher $voucher): bool
    {
        return $user->hasPermission('edit_voucher');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(UserClient $user, Voucher $voucher): bool
    {
        return $user->hasPermission('softDelete_voucher');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(UserClient $user, Voucher $voucher): bool
    {
        return $user->hasPermission('restore_voucher');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(UserClient $user, Voucher $voucher): bool
    {
        return $user->hasPermission('delete_voucher');
    }
}

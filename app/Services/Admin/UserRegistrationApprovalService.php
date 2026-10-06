<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Support\Facades\DB;

class UserRegistrationApprovalService
{
    /**
     * Approve a pending user registration, assign roles, and dispatch welcome email.
     *
     * @param array<int, string> $roles
     */
    public function approve(User $user, array $roles): void
    {
        DB::transaction(function () use ($user, $roles) {
            $user->update([
                'registration_status' => 'approved',
                'is_active' => true,
            ]);

            $user->syncRoles($roles);
        });

        $user->loadMissing(['department', 'roles']);
        $user->notify(new AccountActivatedNotification($user));
    }

    /**
     * Reject a pending user registration, clear roles, and dispatch rejection notification.
     */
    public function reject(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'registration_status' => 'rejected',
                'is_active' => false,
            ]);

            $user->syncRoles([]);
        });

        $user->loadMissing('department');
        $user->notify(new AccountRejectedNotification($user));
    }
}

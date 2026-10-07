<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\AccountActivatedNotification;
use App\Notifications\AccountRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRegistrationApprovalService
{
    /**
     * Approve a pending user registration, assign roles, and dispatch welcome email.
     *
     * @param array<int, string> $roles
     * @return array{email_sent: bool, error: ?string}
     */
    public function approve(User $user, array $roles): array
    {
        DB::transaction(function () use ($user, $roles) {
            $user->update([
                'registration_status' => 'approved',
                'is_active' => true,
            ]);

            $user->syncRoles($roles);
        });

        $user->loadMissing(['department', 'roles']);

        $emailSent = true;
        $error = null;

        try {
            $user->notify(new AccountActivatedNotification($user));
        } catch (\Throwable $e) {
            $emailSent = false;
            $error = $e->getMessage();
            Log::warning("Gagal mengirim AccountActivatedNotification untuk pengguna ID {$user->id} ({$user->email}): {$error}");
        }

        return [
            'email_sent' => $emailSent,
            'error' => $error,
        ];
    }

    /**
     * Reject a pending user registration, clear roles, and dispatch rejection notification.
     *
     * @return array{email_sent: bool, error: ?string}
     */
    public function reject(User $user): array
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'registration_status' => 'rejected',
                'is_active' => false,
            ]);

            $user->syncRoles([]);
        });

        $user->loadMissing('department');

        $emailSent = true;
        $error = null;

        try {
            $user->notify(new AccountRejectedNotification($user));
        } catch (\Throwable $e) {
            $emailSent = false;
            $error = $e->getMessage();
            Log::warning("Gagal mengirim AccountRejectedNotification untuk pengguna ID {$user->id} ({$user->email}): {$error}");
        }

        return [
            'email_sent' => $emailSent,
            'error' => $error,
        ];
    }
}

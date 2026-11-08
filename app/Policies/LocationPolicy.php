<?php

namespace App\Policies;

use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LocationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any locations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('location.view');
    }

    /**
     * Determine whether the user can view the location.
     */
    public function view(User $user, Location $location): bool
    {
        return $user->hasRole('Super Admin') || $user->can('location.view');
    }

    /**
     * Determine whether the user can create locations.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('location.create');
    }

    /**
     * Determine whether the user can update the location.
     */
    public function update(User $user, Location $location): bool
    {
        return $user->hasRole('Super Admin') || $user->can('location.edit');
    }

    /**
     * Determine whether the user can toggle the location active status.
     */
    public function toggleStatus(User $user, Location $location): bool
    {
        return $user->hasRole('Super Admin') || $user->can('location.toggle-status');
    }

    /**
     * Determine whether the user can delete the location (BR-L04).
     * Locations cannot be permanently deleted, only deactivated to maintain transactional integrity and audit trails.
     */
    public function delete(User $user, Location $location): Response
    {
        return Response::deny('Lokasi tidak dapat dihapus secara permanen, hanya dapat dinonaktifkan (BR-L04).');
    }
}

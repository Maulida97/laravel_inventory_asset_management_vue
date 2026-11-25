<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any categories.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('category.manage');
    }

    /**
     * Determine whether the user can view the category.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin') || $user->can('category.manage');
    }

    /**
     * Determine whether the user can create categories.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('category.manage');
    }

    /**
     * Determine whether the user can update the category.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin') || $user->can('category.manage');
    }

    /**
     * Determine whether the user can toggle the category active status.
     */
    public function toggleStatus(User $user, Category $category): bool
    {
        return $user->hasRole('Super Admin') || $user->can('category.manage');
    }

    /**
     * Determine whether the user can delete the category.
     */
    public function delete(User $user, Category $category): Response
    {
        if (! ($user->hasRole('Super Admin') || $user->can('category.manage'))) {
            return Response::deny('Anda tidak memiliki izin untuk menghapus kategori.');
        }

        return Response::allow();
    }
}

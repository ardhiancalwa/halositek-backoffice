<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminOrSuperAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdminOrSuperAdmin() || $user->id === $target->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdminOrSuperAdmin();
    }

    public function update(User $user, User $target): bool
    {
        // Super admin can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Regular admin cannot update other admins
        if ($user->isAdmin()) {
            return ! $target->isAdminOrSuperAdmin() && $user->id !== $target->id;
        }

        return false;
    }

    public function delete(User $user, User $target): bool
    {
        // Super admin can delete anyone (except themselves)
        if ($user->isSuperAdmin()) {
            return $user->id !== $target->id;
        }

        // Regular admin cannot delete other admins
        if ($user->isAdmin()) {
            return ! $target->isAdminOrSuperAdmin() && $user->id !== $target->id;
        }

        return false;
    }

    public function updateProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }
}

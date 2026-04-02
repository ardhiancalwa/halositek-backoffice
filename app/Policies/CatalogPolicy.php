<?php

namespace App\Policies;

use App\Models\Catalog;
use App\Models\User;

class CatalogPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Public list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Catalog $catalog): bool
    {
        return $catalog->status === 'approved' || $catalog->architect_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isArchitect();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Catalog $catalog): bool
    {
        return $catalog->architect_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Catalog $catalog): bool
    {
        return $catalog->architect_id === $user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can verify the model.
     */
    public function verify(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can like the model.
     */
    public function like(User $user, Catalog $catalog): bool
    {
        return true;
    }
}

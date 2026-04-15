<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;

class AwardPolicy
{
    public function create(User $user): bool
    {
        return $user->isArchitect();
    }

    public function update(User $user, Award $award): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $award->architect_id === $user->id;
    }

    public function delete(User $user, Award $award): bool
    {
        return $award->architect_id === $user->id;
    }
}

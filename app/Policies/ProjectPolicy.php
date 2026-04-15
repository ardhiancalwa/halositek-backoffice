<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->status === 'approved' || $project->architect_id === $user->id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isArchitect();
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $project->architect_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->architect_id === $user->id;
    }
}

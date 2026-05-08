<?php

namespace App\Actions\Project;

use App\DTOs\Project\UpdateProjectDTO;
use App\Enums\ProjectStatus;
use App\Models\Project;

final class UpdateProjectAction
{
    public function execute(Project $project, UpdateProjectDTO $dto, bool $resetStatus = false): Project
    {
        $attributes = $this->normalizeAttributes($dto->attributes);

        $project->fill($attributes);

        if ($resetStatus) {
            $project->status = ProjectStatus::Pending->value;
        }

        $project->save();

        return $project;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        foreach (['style', 'status'] as $key) {
            if (isset($attributes[$key]) && $attributes[$key] instanceof \BackedEnum) {
                $attributes[$key] = $attributes[$key]->value;
            }
        }

        return $attributes;
    }
}

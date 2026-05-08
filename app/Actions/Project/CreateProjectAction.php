<?php

namespace App\Actions\Project;

use App\DTOs\Project\CreateProjectDTO;
use App\Models\Project;

final class CreateProjectAction
{
    public function execute(CreateProjectDTO $dto): Project
    {
        return Project::create([
            'architect_id' => $dto->architectId,
            'name' => $dto->name,
            'style' => $dto->style->value,
            'description' => $dto->description,
            'images' => $dto->images,
            'estimated_cost' => $dto->estimatedCost,
            'layout_images' => $dto->layoutImages,
            'highlight_features' => $dto->highlightFeatures,
            'area' => $dto->area,
            'status' => $dto->status->value,
            'likes_count' => $dto->likesCount,
        ]);
    }
}

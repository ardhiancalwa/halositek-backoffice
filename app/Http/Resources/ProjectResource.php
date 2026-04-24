<?php

namespace App\Http\Resources;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Project $project */
        $project = $this->resource;

        $rawImages = $project->getAttribute('images');
        $rawLayoutImages = $project->getAttribute('layout_images');

        $images = is_string($rawImages)
            ? (json_decode($rawImages, true) ?: [])
            : (is_array($rawImages) ? $rawImages : []);

        $layoutImages = is_string($rawLayoutImages)
            ? (json_decode($rawLayoutImages, true) ?: [])
            : (is_array($rawLayoutImages) ? $rawLayoutImages : []);

        $imagesFiltered = array_values(array_filter($images, static fn (mixed $value): bool => is_string($value)));
        $layoutImagesFiltered = array_values(array_filter($layoutImages, static fn (mixed $value): bool => is_string($value)));

        /** @var list<string> $images */
        $images = $imagesFiltered;
        /** @var list<string> $layoutImages */
        $layoutImages = $layoutImagesFiltered;

        return [
            'id' => $project->id,
            'architect_id' => $project->architect_id,
            'name' => $project->name,
            'style' => $project->style,
            'description' => $project->description,
            'images' => $images,
            'image_urls' => collect($images)
                ->map(fn (string $path): string => Storage::url($path))
                ->values(),
            'estimated_cost' => $project->estimated_cost,
            'layout_images' => $layoutImages,
            'layout_image_urls' => collect($layoutImages)
                ->map(fn (string $path): string => Storage::url($path))
                ->values(),
            'highlight_features' => $project->highlight_features,
            'area' => $project->area,
            'likes_count' => (int) ($project->likes_count ?? 0),
            'liked' => $request->user()
                ? $project->likes()->where('user_id', $request->user()->id)->exists()
                : false,
            'status' => $project->status,
            'architect' => $this->whenLoaded('architect', function () use ($project) {
                return [
                    'id' => $project->architect->id,
                    'name' => $project->architect->name,
                    'email' => $project->architect->email,
                    'photo_profile' => $project->architect->photo_profile,
                    'photo_profile_url' => $project->architect->photo_profile ? Storage::url($project->architect->photo_profile) : null,
                ];
            }),
            'created_at' => $project->created_at?->toIso8601String(),
            'updated_at' => $project->updated_at?->toIso8601String(),
        ];
    }
}

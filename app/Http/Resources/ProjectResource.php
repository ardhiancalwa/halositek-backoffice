<?php

namespace App\Http\Resources;

use App\Enums\ProjectStatus;
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
        $status = $this->status instanceof ProjectStatus ? $this->status->value : $this->status;
        $images = is_array($this->images) ? $this->images : [];
        $layoutImages = is_array($this->layout_images) ? $this->layout_images : [];

        return [
            'id' => $this->id,
            'architect_id' => $this->architect_id,
            'name' => $this->name,
            'style' => $this->style,
            'description' => $this->description,
            'images' => $images,
            'image_urls' => collect($images)
                ->map(fn (string $path): string => Storage::url($path))
                ->values(),
            'estimated_cost' => $this->estimated_cost,
            'layout_images' => $layoutImages,
            'layout_image_urls' => collect($layoutImages)
                ->map(fn (string $path): string => Storage::url($path))
                ->values(),
            'highlight_features' => $this->highlight_features,
            'area' => $this->area,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'status' => $status,
            'architect' => $this->whenLoaded('architect', function () {
                return [
                    'id' => $this->architect->id,
                    'name' => $this->architect->name,
                    'email' => $this->architect->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

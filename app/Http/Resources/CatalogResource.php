<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'style' => $this->style,
            'description' => $this->description,
            'status' => $this->status,
            'rooms' => $this->rooms,
            'area' => $this->area,
            'estimated_cost' => $this->estimated_cost,
            'estimated_cost_formatted' => 'Rp ' . number_format($this->estimated_cost, 0, ',', '.'),
            'rating' => $this->rating,
            'likes_count' => $this->likes_count,
            'is_liked' => $this->is_liked_by_user,
            'media' => [
                'images' => is_string($this->images) ? json_decode($this->images, true) : ($this->images ?? []),
                'layout_image' => $this->layout_image,
                'interior_highlights' => is_string($this->interior_highlights) ? json_decode($this->interior_highlights, true) : ($this->interior_highlights ?? []),
            ],
            'architect' => $this->whenLoaded('architect', function () {
                return [
                    'id' => $this->architect->id,
                    'name' => $this->architect->name,
                    'headline' => $this->architect->headline ?? null,
                    'avatar' => $this->architect->avatar ?? null,
                    'rating' => $this->architect->rating ?? 0.0,
                ];
            }),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}

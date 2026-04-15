<?php

namespace App\Http\Resources;

use App\Models\Award;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AwardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Award $award */
        $award = $this->resource;

        return [
            'id' => $award->id,
            'architect_id' => $award->architect_id,
            'name' => $award->name,
            'project_name' => $award->project_name,
            'role' => $award->role,
            'award_date' => $award->award_date,
            'description' => $award->description,
            'verification_file' => $award->verification_file,
            'verification_file_url' => $award->verification_file
                ? Storage::url($award->verification_file)
                : null,
            'status' => $award->status,
            'architect' => $this->whenLoaded('architect', function () use ($award) {
                return [
                    'id' => $award->architect->id,
                    'name' => $award->architect->name,
                    'email' => $award->architect->email,
                ];
            }),
            'created_at' => $award->created_at?->toIso8601String(),
            'updated_at' => $award->updated_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use App\Enums\AwardStatus;
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
        $status = $this->status instanceof AwardStatus ? $this->status->value : $this->status;

        return [
            'id' => $this->id,
            'architect_id' => $this->architect_id,
            'name' => $this->name,
            'project_name' => $this->project_name,
            'role' => $this->role,
            'award_date' => $this->award_date?->toDateString(),
            'description' => $this->description,
            'verification_file' => $this->verification_file,
            'verification_file_url' => $this->verification_file
                ? Storage::url($this->verification_file)
                : null,
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

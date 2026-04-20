<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArchitectProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->architectProfile;
        $role = $this->role instanceof UserRole ? $this->role->value : $this->role;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'profile_picture' => $this->profile_picture,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'role' => $role,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'headline' => $profile?->headline,
            'bio' => $profile?->bio,
            'location' => $profile?->location,
            'status' => $profile?->status,
            'total_projects' => (int) ($this->total_projects ?? 0),
            'total_awards' => (int) ($this->total_awards ?? 0),
            'specialization' => $profile?->specialization,
            'rating' => (float) ($profile?->rating ?? 0),
        ];
    }
}

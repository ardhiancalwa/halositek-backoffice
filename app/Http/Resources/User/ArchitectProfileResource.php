<?php

namespace App\Http\Resources\User;

use App\Enums\UserRole;
use App\Models\ArchitectProfile;
use App\Models\User;
use Carbon\CarbonInterface;
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
        $user = $this->resource instanceof User ? $this->resource : null;
        $profile = $user?->getAttribute('architectProfile');
        $profile = $profile instanceof ArchitectProfile ? $profile : null;
        $role = $user?->getAttribute('role');
        $role = $role instanceof UserRole ? $role->value : $role;
        $emailVerifiedAt = $user?->getAttribute('email_verified_at');
        $createdAt = $user?->getAttribute('created_at');
        $updatedAt = $user?->getAttribute('updated_at');

        return [
            'id' => $user?->getKey(),
            'name' => $user?->getAttribute('name'),
            'email' => $user?->getAttribute('email'),
            'profile_picture' => $user?->getAttribute('photo_profile'),
            'email_verified_at' => $emailVerifiedAt instanceof CarbonInterface ? $emailVerifiedAt->toIso8601String() : null,
            'role' => $role,
            'created_at' => $createdAt instanceof CarbonInterface ? $createdAt->toIso8601String() : null,
            'updated_at' => $updatedAt instanceof CarbonInterface ? $updatedAt->toIso8601String() : null,
            'headline' => $profile?->getAttribute('headline'),
            'bio' => $profile?->getAttribute('bio'),
            'location' => $profile?->getAttribute('location'),
            'status' => $profile?->getAttribute('status'),
            'total_projects' => (int) ($user?->getAttribute('total_projects') ?? 0),
            'total_awards' => (int) ($user?->getAttribute('total_awards') ?? 0),
            'is_wishlisted' => $this->when(
                $user?->offsetExists('is_wishlisted') === true,
                (bool) $user?->getAttribute('is_wishlisted')
            ),
            'specialization' => $profile?->getAttribute('specialization'),
            'rating' => (float) ($profile?->getAttribute('rating') ?? 0),
            'year_of_experience' => (int) ($profile?->getAttribute('year_of_experience') ?? 0),
            'consultation_fee' => (int) ($profile?->getAttribute('consultation_fee') ?? 0),
            'consultation_hours' => (int) ($profile?->getAttribute('consultation_duration') ?? 1),
            'consultation_duration' => (int) ($profile?->getAttribute('consultation_duration') ?? 1),
        ];
    }
}

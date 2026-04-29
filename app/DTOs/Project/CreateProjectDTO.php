<?php

namespace App\DTOs\Project;

use App\Enums\ProjectStatus;
use App\Enums\ProjectStyle;
use Illuminate\Foundation\Http\FormRequest;

final readonly class CreateProjectDTO
{
    /**
     * @param  list<string>  $images
     * @param  list<string>  $layoutImages
     */
    public function __construct(
        public string $architectId,
        public string $name,
        public ProjectStyle $style,
        public string $estimatedCost,
        public array $images = [],
        public array $layoutImages = [],
        public ?string $description = null,
        public ?string $highlightFeatures = null,
        public ?string $area = null,
        public ProjectStatus $status = ProjectStatus::Pending,
        public int $likesCount = 0,
    ) {
    }

    /**
     * @param  list<string>  $imagePaths
     * @param  list<string>  $layoutImagePaths
     */
    public static function fromRequest(FormRequest $request, string $architectId, array $imagePaths = [], array $layoutImagePaths = []): self
    {
        return self::fromArray($request->validated(), $architectId, $imagePaths, $layoutImagePaths);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $imagePaths
     * @param  list<string>  $layoutImagePaths
     */
    public static function fromArray(array $data, string $architectId, array $imagePaths = [], array $layoutImagePaths = []): self
    {
        return new self(
            architectId: $architectId,
            name: (string) $data['name'],
            style: $data['style'] instanceof ProjectStyle
                ? $data['style']
                : ProjectStyle::from((string) $data['style']),
            estimatedCost: (string) $data['estimated_cost'],
            images: $imagePaths,
            layoutImages: $layoutImagePaths,
            description: isset($data['description']) ? (string) $data['description'] : null,
            highlightFeatures: isset($data['highlight_features']) ? (string) $data['highlight_features'] : null,
            area: isset($data['area']) ? (string) $data['area'] : null,
        );
    }
}

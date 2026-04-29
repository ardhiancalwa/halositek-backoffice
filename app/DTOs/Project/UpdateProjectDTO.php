<?php

namespace App\DTOs\Project;

use App\Enums\ProjectStatus;
use App\Enums\ProjectStyle;
use Illuminate\Foundation\Http\FormRequest;

final readonly class UpdateProjectDTO
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    private function __construct(
        public array $attributes,
    ) {
    }

    /**
     * @param  list<string>|null  $imagePaths
     * @param  list<string>|null  $layoutImagePaths
     */
    public static function fromRequest(FormRequest $request, ?array $imagePaths = null, ?array $layoutImagePaths = null): self
    {
        return self::fromArray($request->validated(), $imagePaths, $layoutImagePaths);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>|null  $imagePaths
     * @param  list<string>|null  $layoutImagePaths
     */
    public static function fromArray(array $data, ?array $imagePaths = null, ?array $layoutImagePaths = null): self
    {
        if (isset($data['style']) && ! $data['style'] instanceof ProjectStyle) {
            $data['style'] = ProjectStyle::from((string) $data['style']);
        }

        if (isset($data['status']) && ! $data['status'] instanceof ProjectStatus) {
            $data['status'] = ProjectStatus::from((string) $data['status']);
        }

        if ($imagePaths !== null) {
            $data['images'] = $imagePaths;
        }

        if ($layoutImagePaths !== null) {
            $data['layout_images'] = $layoutImagePaths;
        }

        return new self($data);
    }

    public function withoutStatus(): self
    {
        $attributes = $this->attributes;
        unset($attributes['status']);

        return new self($attributes);
    }
}

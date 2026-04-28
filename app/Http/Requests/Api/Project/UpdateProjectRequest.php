<?php

namespace App\Http\Requests\Api\Project;

use App\Enums\ProjectStyle;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $images = $this->file('images');
        if ($images instanceof UploadedFile) {
            $this->files->set('images', [$images]);
        }

        $layoutImages = $this->file('layout_images');
        if ($layoutImages instanceof UploadedFile) {
            $this->files->set('layout_images', [$layoutImages]);
        }

        if ($this->has('style')) {
            $this->merge([
                'style' => strtolower((string) $this->input('style')),
            ]);
        }
    }

    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user !== null && ($user->isArchitect() || $user->isAdmin());
    }

    /**
     * @return array<string, array<int, string|Enum>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'required', 'string', 'in:pending,approved,declined'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'style' => ['sometimes', 'required', new Enum(ProjectStyle::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'estimated_cost' => ['sometimes', 'required', 'string', 'max:255'],
            'layout_images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'layout_images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'highlight_features' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
        ];
    }
}

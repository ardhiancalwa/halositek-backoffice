<?php

namespace App\Http\Requests\Api\V1\Project;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class StoreProjectRequest extends FormRequest
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
    }

    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user !== null && $user->isArchitect();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'style' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'estimated_cost' => ['required', 'string', 'max:255'],
            'layout_images' => ['sometimes', 'array', 'min:1', 'max:10'],
            'layout_images.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'highlight_features' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:100'],
        ];
    }
}

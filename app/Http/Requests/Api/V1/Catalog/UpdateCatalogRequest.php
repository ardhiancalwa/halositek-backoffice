<?php

namespace App\Http\Requests\Api\V1\Catalog;

use App\Models\Catalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->check() || ! auth()->user()->isArchitect()) {
            return false;
        }

        // Identify the catalog from route (assuming parameter name is 'id' or 'catalog')
        $catalog = $this->route('catalog') ?? $this->route('id');

        if (is_string($catalog)) {
            $catalog = Catalog::find($catalog);
        }

        return $catalog && $catalog->architect_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'style' => 'sometimes|required|string|in:Traditional,Modern,Minimalist,Futuristic,Industrial',
            'description' => 'nullable|string|max:5000',
            'images' => 'sometimes|required|array|min:1|max:10',
            'images.*' => 'url|string',
            'interior_highlights' => 'nullable|array',
            'interior_highlights.*' => 'string|max:255',
            'layout_image' => 'nullable|url|string',
            'rooms' => 'sometimes|required|string|max:100',
            'estimated_cost' => 'sometimes|required|integer|min:1',
            'area' => 'sometimes|required|string|max:100',
        ];
    }
}

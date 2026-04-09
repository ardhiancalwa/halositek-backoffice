<?php

namespace App\Http\Requests\Api\V1\Catalog;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCatalogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isArchitect();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'style' => 'required|string|in:Traditional,Modern,Minimalist,Futuristic,Industrial',
            'description' => 'nullable|string|max:5000',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'url|string',
            'interior_highlights' => 'nullable|array',
            'interior_highlights.*' => 'string|max:255',
            'layout_image' => 'nullable|url|string',
            'rooms' => 'required|string|max:100',
            'estimated_cost' => 'required|integer|min:1',
            'area' => 'required|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'style.in' => 'Style arsitektur yang dipilih tidak valid.',
            'images.min' => 'Setidaknya satu gambar harus diunggah.',
            'images.max' => 'Maksimal 10 gambar yang boleh diunggah.',
            'images.*.url' => 'Format gambar harus berupa URL yang valid.',
            'rooms.required' => 'Informasi ruangan wajib diisi.',
            'estimated_cost.min' => 'Estimasi biaya minimal adalah 1 rupiah.',
        ];
    }
}

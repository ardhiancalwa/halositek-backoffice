<?php

namespace App\Http\Resources\Faq;

use App\Models\Faq;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $faq = $this->resource instanceof Faq ? $this->resource : null;
        $createdAt = $faq?->getAttribute('created_at');
        $updatedAt = $faq?->getAttribute('updated_at');

        return [
            'id' => $faq?->getKey(),
            'question' => $faq?->getAttribute('question'),
            'answer' => $faq?->getAttribute('answer'),
            'is_active' => (bool) $faq?->getAttribute('is_active'),
            'created_at' => $createdAt instanceof CarbonInterface ? $createdAt->toIso8601String() : null,
            'updated_at' => $updatedAt instanceof CarbonInterface ? $updatedAt->toIso8601String() : null,
        ];
    }
}

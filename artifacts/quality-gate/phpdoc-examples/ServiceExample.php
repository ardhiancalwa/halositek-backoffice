<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Catalog;
use Illuminate\Support\Arr;
use RuntimeException;

class CatalogService
{
    /**
     * Create a catalog domain record from normalized input.
     *
     * @param  array<string, mixed>  $payload  Validated payload from controller/request.
     *
     * @throws RuntimeException When required fields are missing.
     */
    public function create(array $payload): Catalog
    {
        $title = Arr::get($payload, 'title');

        if (! is_string($title) || trim($title) === '') {
            throw new RuntimeException('Catalog title is required.');
        }

        return Catalog::query()->create([
            'title' => $title,
            'description' => Arr::get($payload, 'description'),
            'is_published' => (bool) Arr::get($payload, 'is_published', false),
        ]);
    }
}

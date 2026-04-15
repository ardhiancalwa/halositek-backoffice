<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Arr;
use RuntimeException;

class ProjectService
{
    /**
     * Create a project domain record from normalized input.
     *
     * @param  array<string, mixed>  $payload  Validated payload from controller/request.
     *
     * @throws RuntimeException When required fields are missing.
     */
    public function create(array $payload): Project
    {
        $name = Arr::get($payload, 'name');

        if (! is_string($name) || trim($name) === '') {
            throw new RuntimeException('Project name is required.');
        }

        return Project::query()->create([
            'name' => $name,
            'style' => Arr::get($payload, 'style', 'Modern'),
            'description' => Arr::get($payload, 'description'),
            'estimated_cost' => Arr::get($payload, 'estimated_cost', 'Rp 0 - 0'),
            'status' => Arr::get($payload, 'status', 'pending'),
        ]);
    }
}

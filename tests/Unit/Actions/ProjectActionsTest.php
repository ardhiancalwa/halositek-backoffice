<?php

use App\Actions\Project\CreateProjectAction;
use App\Actions\Project\UpdateProjectAction;
use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('projects')->delete();
    DB::connection('mongodb')->table('users')->delete();
});

it('creates project from dto', function () {
    $architect = User::factory()->architect()->create();

    $project = app(CreateProjectAction::class)->execute(CreateProjectDTO::fromArray([
        'name' => 'Project Unit',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'description' => 'Unit test project',
    ], (string) $architect->id, ['projects/images/unit.jpg'], ['projects/layouts/unit.jpg']));

    expect($project)->toBeInstanceOf(Project::class)
        ->and($project->architect_id)->toBe((string) $architect->id)
        ->and($project->name)->toBe('Project Unit')
        ->and($project->status->value)->toBe('pending')
        ->and($project->likes_count)->toBe(0);
});

it('updates project from dto and can reset status to pending', function () {
    $architect = User::factory()->architect()->create();
    $project = Project::create([
        'architect_id' => $architect->id,
        'name' => 'Before',
        'style' => 'minimalist',
        'estimated_cost' => 'Rp 1M - 2M',
        'status' => 'approved',
        'likes_count' => 0,
    ]);

    $updated = app(UpdateProjectAction::class)->execute(
        $project,
        UpdateProjectDTO::fromArray([
            'name' => 'After',
            'style' => 'industrial',
        ]),
        resetStatus: true,
    );

    expect($updated->fresh()->name)->toBe('After')
        ->and($updated->fresh()->style->value)->toBe('industrial')
        ->and($updated->fresh()->status->value)->toBe('pending');
});

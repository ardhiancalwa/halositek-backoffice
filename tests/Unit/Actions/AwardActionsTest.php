<?php

use App\Actions\Award\CreateAwardAction;
use App\Actions\Award\UpdateAwardAction;
use App\DTOs\Award\CreateAwardDTO;
use App\DTOs\Award\UpdateAwardDTO;
use App\Models\Award;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('awards')->delete();
    DB::connection('mongodb')->table('users')->delete();
});

it('creates award from dto', function () {
    $architect = User::factory()->architect()->create();

    $award = app(CreateAwardAction::class)->execute(CreateAwardDTO::fromArray([
        'name' => 'Design Award',
        'project_name' => 'Project Unit',
        'role' => 'Lead Architect',
        'award_date' => '2026-04-01',
        'description' => 'Winner',
    ], (string) $architect->id, 'awards/verification-files/proof.pdf'));

    expect($award)->toBeInstanceOf(Award::class)
        ->and($award->architect_id)->toBe((string) $architect->id)
        ->and($award->project_name)->toBe('Project Unit')
        ->and($award->status->value)->toBe('pending');
});

it('updates award from dto and can reset status to pending', function () {
    $architect = User::factory()->architect()->create();
    $award = Award::create([
        'architect_id' => $architect->id,
        'name' => 'Before Award',
        'project_name' => 'Before Project',
        'role' => 'Architect',
        'award_date' => '2026-04-01',
        'status' => 'approved',
    ]);

    $updated = app(UpdateAwardAction::class)->execute(
        $award,
        UpdateAwardDTO::fromArray([
            'name' => 'After Award',
            'verification_file' => 'awards/verification-files/new.pdf',
        ]),
        resetStatus: true,
    );

    expect($updated->fresh()->name)->toBe('After Award')
        ->and($updated->fresh()->verification_file)->toBe('awards/verification-files/new.pdf')
        ->and($updated->fresh()->status->value)->toBe('pending');
});

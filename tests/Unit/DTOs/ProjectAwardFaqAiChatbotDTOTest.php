<?php

use App\DTOs\AiChatbot\AiLogFilterDTO;
use App\DTOs\Award\CreateAwardDTO;
use App\DTOs\Award\UpdateAwardDTO;
use App\DTOs\Faq\CreateFaqDTO;
use App\DTOs\Faq\UpdateFaqDTO;
use App\DTOs\Project\CreateProjectDTO;
use App\DTOs\Project\UpdateProjectDTO;
use App\Enums\AwardStatus;
use App\Enums\ProjectStatus;
use App\Enums\ProjectStyle;

it('maps project payloads into create and update DTOs', function () {
    $createDto = CreateProjectDTO::fromArray([
        'name' => 'Rumah Tropis',
        'style' => 'modern',
        'estimated_cost' => 'Rp 2M - 3M',
        'description' => 'Rumah dengan bukaan besar.',
        'highlight_features' => 'Skylight',
        'area' => '120 m2',
    ], 'architect-1', ['projects/images/a.jpg'], ['projects/layouts/a.jpg']);

    expect($createDto->architectId)->toBe('architect-1')
        ->and($createDto->style)->toBe(ProjectStyle::Modern)
        ->and($createDto->status)->toBe(ProjectStatus::Pending)
        ->and($createDto->images)->toBe(['projects/images/a.jpg'])
        ->and($createDto->layoutImages)->toBe(['projects/layouts/a.jpg']);

    $updateDto = UpdateProjectDTO::fromArray([
        'name' => 'Rumah Tropis Updated',
        'style' => 'industrial',
        'status' => 'approved',
    ], ['projects/images/new.jpg']);

    expect($updateDto->attributes['style'])->toBe(ProjectStyle::Industrial)
        ->and($updateDto->attributes['status'])->toBe(ProjectStatus::Approved)
        ->and($updateDto->attributes['images'])->toBe(['projects/images/new.jpg'])
        ->and($updateDto->withoutStatus()->attributes)->not->toHaveKey('status');
});

it('maps award payloads into create and update DTOs', function () {
    $createDto = CreateAwardDTO::fromArray([
        'name' => 'Best Residential Design',
        'project_name' => 'Rumah Tropis',
        'role' => 'Lead Architect',
        'award_date' => '2026-04-01',
        'description' => 'Winner',
    ], 'architect-1', 'awards/verification-files/proof.pdf');

    expect($createDto->architectId)->toBe('architect-1')
        ->and($createDto->projectName)->toBe('Rumah Tropis')
        ->and($createDto->status)->toBe(AwardStatus::Pending)
        ->and($createDto->verificationFile)->toBe('awards/verification-files/proof.pdf');

    $updateDto = UpdateAwardDTO::fromArray([
        'status' => 'declined',
        'name' => 'Updated Award',
    ]);

    expect($updateDto->attributes['status'])->toBe(AwardStatus::Declined)
        ->and($updateDto->withoutStatus()->attributes)->not->toHaveKey('status');
});

it('maps faq payloads and defaults active state', function () {
    $createDto = CreateFaqDTO::fromArray([
        'question' => 'Apa itu Halositek?',
        'answer' => 'Platform arsitek.',
    ]);

    expect($createDto->isActive)->toBeTrue();

    $updateDto = UpdateFaqDTO::fromArray([
        'answer' => 'Platform konsultasi arsitektur.',
        'is_active' => false,
    ]);

    expect($updateDto->attributes)->toBe([
        'answer' => 'Platform konsultasi arsitektur.',
        'is_active' => false,
    ]);
});

it('maps ai chatbot log filter defaults and optional status', function () {
    expect(AiLogFilterDTO::fromArray([]))
        ->status->toBeNull()
        ->perPage->toBe(15);

    expect(AiLogFilterDTO::fromArray([
        'status' => 'failed',
        'per_page' => 25,
    ]))
        ->status->toBe('failed')
        ->perPage->toBe(25);
});

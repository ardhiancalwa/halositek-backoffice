<?php

use App\Actions\Faq\CreateFaqAction;
use App\Actions\Faq\UpdateFaqAction;
use App\DTOs\Faq\CreateFaqDTO;
use App\DTOs\Faq\UpdateFaqDTO;
use App\Models\Faq;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('faqs')->delete();
});

it('creates faq from dto with active default', function () {
    $faq = app(CreateFaqAction::class)->execute(CreateFaqDTO::fromArray([
        'question' => 'Bagaimana cara daftar?',
        'answer' => 'Gunakan halaman registrasi.',
    ]));

    expect($faq)->toBeInstanceOf(Faq::class)
        ->and($faq->question)->toBe('Bagaimana cara daftar?')
        ->and($faq->is_active)->toBeTrue();
});

it('updates faq from dto', function () {
    $faq = Faq::create([
        'question' => 'Before?',
        'answer' => 'Before answer.',
        'is_active' => true,
    ]);

    $updated = app(UpdateFaqAction::class)->execute($faq, UpdateFaqDTO::fromArray([
        'answer' => 'After answer.',
        'is_active' => false,
    ]));

    expect($updated->fresh()->answer)->toBe('After answer.')
        ->and($updated->fresh()->is_active)->toBeFalse();
});

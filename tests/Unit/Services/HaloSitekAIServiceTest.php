<?php

use App\Exceptions\HaloSitekAIException;
use App\Services\HaloSitekAIService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('returns true on healthy ai service response', function () {
    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'status' => 'healthy',
        'ollama_connected' => true,
    ], 200));

    $service = app(HaloSitekAIService::class);

    expect($service->isHealthy())->toBeTrue();
});

it('generates text response successfully', function () {
    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'type' => 'text',
        'content' => 'Jawaban AI',
        'prompt_used' => null,
    ], 200));

    $service = app(HaloSitekAIService::class);
    $result = $service->generate('user-1', 'Halo AI');

    expect($result['type'])->toBe('text');
    expect($result['content'])->toBe('Jawaban AI');
    expect($result['prompt_used'])->toBeNull();
});

it('generates image response and converts base64 content into storage url', function () {
    Http::preventStrayRequests();
    Storage::fake('public');

    Http::fake(fn () => Http::response([
        'type' => 'image',
        'content' => base64_encode('dummy-png-bytes'),
        'prompt_used' => 'test prompt',
    ], 200));

    $service = app(HaloSitekAIService::class);
    $result = $service->generate('user-2', 'Buatkan gambar rumah');

    expect($result['type'])->toBe('image');
    expect($result['content'])->toStartWith('/storage/ai_images/user-2/');
    expect($result['content'])->toEndWith('.png');
});

it('throws 503 halo sitek ai exception when upstream ai service returns 503', function () {
    Http::preventStrayRequests();
    Http::fake(fn () => Http::response([
        'detail' => 'Tidak dapat terhubung ke Ollama',
    ], 503));

    $service = app(HaloSitekAIService::class);

    $caughtException = null;
    try {
        $service->generate('user-3', 'Tes error');
    } catch (HaloSitekAIException $exception) {
        $caughtException = $exception;
    }

    expect($caughtException)->toBeInstanceOf(HaloSitekAIException::class);
    expect($caughtException?->getMessage())->toContain('HaloSitek AI service/Ollama sedang tidak tersedia.');
    expect($caughtException?->statusCode())->toBe(503);
});

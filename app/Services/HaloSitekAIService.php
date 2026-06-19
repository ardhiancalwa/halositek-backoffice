<?php

namespace App\Services;

use App\Exceptions\HaloSitekAIException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HaloSitekAIService
{
    private string $baseUrl;

    private bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.halositek_ai.url'), '/');
        $this->verifySsl = (bool) config('services.halositek_ai.verify_ssl', true);
    }

    public function isHealthy(): bool
    {
        try {
            $response = $this->http(5)->get("{$this->baseUrl}/health");

            return $response->ok() && $response->json('ollama_connected') === true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    public function generate(string $userId, string $message, array $history = [], ?string $generationId = null): array
    {
        $payload = [
            'user_id' => $userId,
            'message' => $message,
            'history' => $history,
        ];

        if ($generationId !== null && $generationId !== '') {
            $payload['generation_id'] = $generationId;
        }

        try {
            $response = $this->http(120)->post("{$this->baseUrl}/api/v1/generate", $payload);
        } catch (ConnectionException $exception) {
            Log::error('HaloSitek AI service is unavailable.', [
                'user_id' => $userId,
                'base_url' => $this->baseUrl,
                'error' => $exception->getMessage(),
            ]);

            throw new HaloSitekAIException(
                'HaloSitek AI service/Ollama sedang tidak tersedia. Coba lagi beberapa saat.',
                503,
                ['user_id' => $userId, 'base_url' => $this->baseUrl],
                $exception
            );
        } catch (Throwable $exception) {
            Log::error('Unexpected error while requesting HaloSitek AI service.', [
                'user_id' => $userId,
                'base_url' => $this->baseUrl,
                'error' => $exception->getMessage(),
            ]);

            throw new HaloSitekAIException(
                'Terjadi kesalahan saat memproses permintaan ke AI service.',
                500,
                ['user_id' => $userId, 'base_url' => $this->baseUrl],
                $exception
            );
        }

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error('HaloSitek AI generate request failed.', [
                'user_id' => $userId,
                'status' => $status,
                'body' => $body,
            ]);

            if (in_array($status, [404, 502, 503, 504], true)) {
                throw new HaloSitekAIException(
                    'HaloSitek AI service/Ollama sedang tidak tersedia. Coba lagi beberapa saat.',
                    503,
                    ['user_id' => $userId, 'status' => $status]
                );
            }

            if ($status === 422) {
                throw new HaloSitekAIException(
                    'Permintaan ke AI service tidak valid. Silakan periksa input dan coba lagi.',
                    422,
                    ['user_id' => $userId, 'status' => $status]
                );
            }

            throw new HaloSitekAIException(
                'Terjadi kesalahan saat memproses permintaan ke AI service.',
                500,
                ['user_id' => $userId, 'status' => $status]
            );
        }

        $data = (array) $response->json();
        $type = is_string($data['type'] ?? null) && $data['type'] !== ''
            ? $data['type']
            : 'text';
        $content = $data['content'] ?? '';

        if ($type === 'image') {
            if (! is_string($content) || $content === '') {
                Log::error('HaloSitek AI image response is missing valid content.', [
                    'user_id' => $userId,
                    'payload' => $data,
                ]);

                throw new HaloSitekAIException(
                    'Terjadi kesalahan saat memproses hasil gambar dari AI service.',
                    500,
                    ['user_id' => $userId]
                );
            }

            $decodedImage = base64_decode($content, true);

            if ($decodedImage === false) {
                Log::error('HaloSitek AI image response contains invalid base64 content.', [
                    'user_id' => $userId,
                ]);

                throw new HaloSitekAIException(
                    'Terjadi kesalahan saat memproses hasil gambar dari AI service.',
                    500,
                    ['user_id' => $userId]
                );
            }

            $timestamp = now()->format('YmdHisv');
            $filename = "ai_images/{$userId}/{$timestamp}.png";

            Storage::disk('public')->put($filename, $decodedImage);

            $content = Storage::url($filename);
        }

        if ($type === 'text' && ! is_string($content)) {
            $content = is_scalar($content) ? (string) $content : '';
        }

        $data['type'] = $type;
        $data['content'] = $content;

        return $data;
    }

    public function stopGeneration(string $userId, ?string $generationId = null): bool
    {
        $payload = ['user_id' => $userId];

        if ($generationId !== null && $generationId !== '') {
            $payload['generation_id'] = $generationId;
        }

        try {
            $response = $this->http(10)->post("{$this->baseUrl}/api/v1/generate/stop", $payload);

            return $response->successful();
        } catch (Throwable $exception) {
            Log::warning('Unable to send stop signal to HaloSitek AI service.', [
                'user_id' => $userId,
                'generation_id' => $generationId,
                'base_url' => $this->baseUrl,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function http(int $timeout): PendingRequest
    {
        return Http::timeout($timeout)->withOptions([
            'verify' => $this->verifySsl,
        ]);
    }
}

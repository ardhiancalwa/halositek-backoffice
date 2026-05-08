<?php

namespace App\Services;

use App\Exceptions\HaloSitekAIException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HaloSitekAIService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.halositek_ai.url'), '/');
    }

    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            return $response->ok() && $response->json('ollama_connected') === true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    public function generate(string $userId, string $message, array $history = []): array
    {
        try {
            $response = Http::timeout(120)->post("{$this->baseUrl}/api/v1/generate", [
                'user_id' => $userId,
                'message' => $message,
                'history' => $history,
            ]);
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

            if ($status === 503) {
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
}

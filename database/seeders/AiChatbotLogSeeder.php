<?php

namespace Database\Seeders;

use App\Models\AiChatbotLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiChatbotLogSeeder extends Seeder
{
    /**
     * Seed AI chatbot activity logs for backoffice management.
     */
    public function run(): void
    {
        $users = User::query()->limit(5)->get();

        if ($users->isEmpty()) {
            return;
        }

        $samples = [
            [
                'status' => 'success',
                'prompt_preview' => 'Generate konsep fasad rumah modern tropis.',
                'request_payload' => '{"prompt":"Generate konsep fasad rumah modern tropis"}',
                'generate_time_ms' => 1810,
                'result_type' => 'text',
                'generated_text' => 'Konsep fasad: roster, kanopi kayu, bukaan lebar, vertical fins...',
            ],
            [
                'status' => 'failed',
                'prompt_preview' => 'Generate image masterplan urban mixed-use.',
                'request_payload' => '{"prompt":"Generate image masterplan urban mixed-use"}',
                'generate_time_ms' => 620,
                'error_log' => 'Inference timeout on provider A.',
            ],
            [
                'status' => 'success',
                'prompt_preview' => 'Buat render eksterior rumah minimalis 2 lantai.',
                'request_payload' => '{"prompt":"Buat render eksterior rumah minimalis 2 lantai"}',
                'generate_time_ms' => 2450,
                'result_type' => 'image',
                'generated_image_url' => 'https://picsum.photos/seed/halositek-ai-1/1200/800',
            ],
        ];

        foreach ($samples as $index => $sample) {
            $user = $users->get($index % $users->count());
            $createdAt = now()->subMinutes(10 * ($index + 1));

            AiChatbotLog::query()->create([
                'user_id' => (string) $user->getKey(),
                'prompt_preview' => $sample['prompt_preview'],
                'request_payload' => $sample['request_payload'],
                'status' => $sample['status'],
                'generate_time_ms' => $sample['generate_time_ms'],
                'result_type' => $sample['result_type'] ?? null,
                'generated_text' => $sample['generated_text'] ?? null,
                'generated_image_url' => $sample['generated_image_url'] ?? null,
                'error_log' => $sample['error_log'] ?? null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}

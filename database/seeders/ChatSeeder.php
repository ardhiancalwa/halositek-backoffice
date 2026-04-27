<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Seed baseline chat conversations and messages.
     */
    public function run(): void
    {
        $admin = User::query()->where('role', UserRole::Admin->value)->first();
        $architect = User::query()->where('role', UserRole::Architect->value)->first();
        $user = User::query()->where('role', UserRole::User->value)->first();

        if (! $admin || ! $architect || ! $user) {
            return;
        }

        $privateConversation = Conversation::query()->firstOrCreate(
            [
                'is_group' => false,
                'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
            ],
            [
                'name' => null,
                'last_read_at' => [
                    (string) $user->getKey() => now()->subMinutes(30)->toIso8601String(),
                    (string) $architect->getKey() => now()->subMinutes(15)->toIso8601String(),
                ],
            ]
        );

        $groupConversation = Conversation::query()->firstOrCreate(
            [
                'is_group' => true,
                'participant_ids' => [
                    (string) $admin->getKey(),
                    (string) $user->getKey(),
                    (string) $architect->getKey(),
                ],
            ],
            [
                'name' => 'Project Consultation Group',
                'last_read_at' => [
                    (string) $admin->getKey() => now()->subMinutes(5)->toIso8601String(),
                    (string) $user->getKey() => now()->subMinutes(20)->toIso8601String(),
                    (string) $architect->getKey() => now()->subMinutes(10)->toIso8601String(),
                ],
            ]
        );

        $seedMessages = [
            [
                'conversation' => $privateConversation,
                'user_id' => (string) $user->getKey(),
                'body' => 'Halo kak, saya mau konsultasi desain rumah modern tropis.',
                'created_at' => now()->subMinutes(60),
            ],
            [
                'conversation' => $privateConversation,
                'user_id' => (string) $architect->getKey(),
                'body' => 'Siap, boleh jelaskan kebutuhan ruang dan ukuran lahan?',
                'created_at' => now()->subMinutes(55),
            ],
            [
                'conversation' => $groupConversation,
                'user_id' => (string) $admin->getKey(),
                'body' => 'Reminder: mohon lengkapi detail jadwal dan biaya konsultasi.',
                'created_at' => now()->subMinutes(25),
            ],
            [
                'conversation' => $groupConversation,
                'user_id' => (string) $user->getKey(),
                'body' => 'Saya tersedia hari Jumat jam 19.00 WIB, fee per sesi berapa ya?',
                'created_at' => now()->subMinutes(20),
            ],
            [
                'conversation' => $groupConversation,
                'user_id' => (string) $architect->getKey(),
                'body' => 'Fee per sesi 300rb untuk 1 jam. Kita mulai dari kebutuhan ruang ya.',
                'created_at' => now()->subMinutes(15),
            ],
        ];

        foreach ($seedMessages as $item) {
            /** @var Conversation $conversation */
            $conversation = $item['conversation'];

            Message::query()->firstOrCreate(
                [
                    'conversation_id' => (string) $conversation->getKey(),
                    'user_id' => (string) $item['user_id'],
                    'body' => (string) $item['body'],
                ],
                [
                    'attachment' => null,
                    'read_at' => null,
                    'created_at' => $item['created_at'],
                    'updated_at' => $item['created_at'],
                ]
            );
        }
    }
}

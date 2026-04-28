<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@halositek.com')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Admin User'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'Password123!')),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => env('SEED_ARCHITECT_EMAIL', 'architect@halositek.com')],
            [
                'name' => env('SEED_ARCHITECT_NAME', 'Architect User'),
                'password' => Hash::make(env('SEED_ARCHITECT_PASSWORD', 'Password123!')),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => env('SEED_USER_EMAIL', 'user@halositek.com')],
            [
                'name' => env('SEED_USER_NAME', 'Regular User'),
                'password' => Hash::make(env('SEED_USER_PASSWORD', 'Password123!')),
            ]
        );

        $this->call([
            ArchitectFaqSeeder::class,
            ProjectSeeder::class,
            AwardSeeder::class,
            ConsultationSeeder::class,
            ConsultationReportSeeder::class,
            ChatSeeder::class,
            AiChatbotLogSeeder::class,
        ]);
    }
}

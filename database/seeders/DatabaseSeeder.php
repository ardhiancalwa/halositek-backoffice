<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ArchitectProfile;
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
            ['email' => env('SEED_SUPER_ADMIN_EMAIL', 'superadmin@halositek.com')],
            [
                'name' => env('SEED_SUPER_ADMIN_NAME', 'Super Admin User'),
                'password' => Hash::make(env('SEED_SUPER_ADMIN_PASSWORD', 'Password123!')),
                'role' => UserRole::SuperAdmin->value,
            ]
        );

        User::query()->firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@halositek.com')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Admin User'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'Password123!')),
                'role' => UserRole::Admin->value,
            ]
        );

        $architect = User::query()->updateOrCreate(
            ['email' => env('SEED_ARCHITECT_EMAIL', 'architect@halositek.com')],
            [
                'name' => env('SEED_ARCHITECT_NAME', 'Architect User'),
                'password' => Hash::make(env('SEED_ARCHITECT_PASSWORD', 'Password123!')),
                'role' => UserRole::Architect->value,
            ]
        );

        ArchitectProfile::query()->updateOrCreate(
            ['user_id' => (string) $architect->getKey()],
            [
                'consultation_fee' => (int) ($_ENV['SEED_ARCHITECT_FEE'] ?? 250000),
                'consultation_duration' => (int) ($_ENV['SEED_ARCHITECT_HOURS'] ?? 1),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => env('SEED_USER_EMAIL', 'user@halositek.com')],
            [
                'name' => env('SEED_USER_NAME', 'Regular User'),
                'password' => Hash::make(env('SEED_USER_PASSWORD', 'Password123!')),
                'role' => UserRole::User->value,
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

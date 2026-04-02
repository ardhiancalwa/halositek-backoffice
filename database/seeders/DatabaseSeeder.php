<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => env('SEED_ADMIN_NAME', 'Admin User'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@halositek.com'),
            'password' => bcrypt(env('SEED_ADMIN_PASSWORD', 'Password123!')),
        ]);

        User::factory()->architect()->create([
            'name' => env('SEED_ARCHITECT_NAME', 'Architect User'),
            'email' => env('SEED_ARCHITECT_EMAIL', 'architect@halositek.com'),
            'password' => bcrypt(env('SEED_ARCHITECT_PASSWORD', 'Password123!')),
        ]);

        User::factory()->create([
            'name' => env('SEED_USER_NAME', 'Regular User'),
            'email' => env('SEED_USER_EMAIL', 'user@halositek.com'),
            'password' => bcrypt(env('SEED_USER_PASSWORD', 'Password123!')),
        ]);
    }
}

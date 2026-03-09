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
        // User::factory(10)->create();

        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@halositek.com',
            'password' => bcrypt('Password123!'),
        ]);

        User::factory()->architect()->create([
            'name' => 'Architect User',
            'email' => 'architect@halositek.com',
            'password' => bcrypt('Password123!'),
        ]);

        User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@halositek.com',
            'password' => bcrypt('Password123!'),
        ]);
    }
}

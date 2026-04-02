<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Catalog;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all architects or create one if none exist
        $architects = User::where('role', UserRole::Architect)->get();

        if ($architects->isEmpty()) {
            $architect = User::factory()->create([
                'name' => 'Sample Architect',
                'email' => 'architect_seed@halositek.com',
                'role' => UserRole::Architect,
            ]);
            $architects->push($architect);
        }

        // Create 30 catalogs
        foreach (range(1, 30) as $index) {
            Catalog::factory()->create([
                'architect_id' => $architects->random()->id,
            ]);
        }
    }
}

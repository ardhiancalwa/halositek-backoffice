<?php

namespace Database\Seeders;

use App\Enums\AwardStatus;
use App\Enums\UserRole;
use App\Models\Award;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class AwardSeeder extends Seeder
{
    /**
     * Seed awards for backoffice management.
     */
    public function run(): void
    {
        $architects = User::query()->where('role', UserRole::Architect->value)->get();
        if ($architects->isEmpty()) {
            return;
        }

        $projectNames = Project::query()
            ->whereIn('architect_id', $architects->pluck('id')->map('strval')->all())
            ->limit(10)
            ->pluck('name')
            ->values();

        foreach ($architects as $index => $architect) {
            $projectName = (string) ($projectNames->get($index % max(1, $projectNames->count())) ?? 'Sample Project');

            $status = match ($index % 3) {
                0 => AwardStatus::Approved->value,
                1 => AwardStatus::Pending->value,
                default => AwardStatus::Declined->value,
            };

            Award::query()->create([
                'architect_id' => (string) $architect->getKey(),
                'name' => 'Best Residential Design ' . ($index + 1),
                'project_name' => $projectName,
                'role' => 'Lead Architect',
                'award_date' => now()->subMonths($index + 1)->toDateString(),
                'description' => 'Penghargaan desain untuk proyek hunian.',
                'verification_file' => null,
                'status' => $status,
            ]);
        }
    }
}

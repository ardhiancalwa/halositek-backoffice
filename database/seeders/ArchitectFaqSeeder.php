<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ArchitectProfile;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArchitectFaqSeeder extends Seeder
{
    /**
     * Seed architects profile and faq baseline data.
     */
    public function run(): void
    {
        $architects = User::query()->where('role', UserRole::Architect->value)->get();

        foreach ($architects as $architect) {
            ArchitectProfile::query()->firstOrCreate(
                ['user_id' => $architect->id],
                [
                    'headline' => 'Arsitek HaloSitek',
                    'bio' => 'Arsitek profesional untuk kebutuhan hunian modern.',
                    'location' => 'Bandung, Jawa Barat',
                    'status' => 'approved',
                    'specialization' => 'Residential, Modern, Tropical',
                    'rating' => 4.8,
                ]
            );
        }

        $faqs = [
            [
                'question' => 'Bagaimana cara konsultasi dengan arsitek?',
                'answer' => 'Pilih arsitek, mulai sesi konsultasi, lalu lanjutkan komunikasi melalui fitur chat.',
                'is_active' => true,
            ],
            [
                'question' => 'Bagaimana proses verifikasi arsitek?',
                'answer' => 'Arsitek mendaftar lalu diverifikasi oleh admin sebelum tampil untuk publik.',
                'is_active' => true,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::query()->firstOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}

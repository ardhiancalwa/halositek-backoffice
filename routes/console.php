<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:normalize-conversations {--dry-run : Preview changes without updating data}', function () {
    $collection = DB::connection('mongodb')->table('conversations')->get();

    $total = 0;
    $updated = 0;
    $skipped = 0;
    $invalid = 0;

    foreach ($collection as $document) {
        $total++;
        $payload = [];
        $needsUpdate = false;

        foreach (['participant_ids', 'last_read_at'] as $field) {
            $value = $document->{$field} ?? null;

            if (! is_string($value)) {
                continue;
            }

            try {
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $invalid++;

                continue;
            }

            $payload[$field] = $decoded;
            $needsUpdate = true;
        }

        if (! $needsUpdate) {
            $skipped++;

            continue;
        }

        if (! $this->option('dry-run')) {
            $id = $document->id ?? null;

            if ($id === null) {
                $invalid++;

                continue;
            }

            DB::connection('mongodb')
                ->table('conversations')
                ->where('id', (string) $id)
                ->update($payload);
        }

        $updated++;
    }

    $this->info('Conversation normalization done.');
    $this->line('Total documents : ' . $total);
    $this->line('Updated         : ' . $updated);
    $this->line('Skipped         : ' . $skipped);
    $this->line('Invalid         : ' . $invalid);
})->purpose('Normalize legacy JSON-string chat fields into proper MongoDB arrays/objects');

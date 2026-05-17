<?php

use App\Models\ArchitectProfile;
use App\Models\Consultation;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

afterEach(function () {
    DB::connection('mongodb')->table('conversations')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('returns user conversations when user id is inside participant_ids array', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    Conversation::create([
        'name' => null,
        'is_group' => false,
        'participant_ids' => [
            (string) $userA->getKey(),
            (string) $userB->getKey(),
        ],
        'last_read_at' => [
            (string) $userA->getKey() => now()->toIso8601String(),
        ],
    ]);

    actingAs($userA, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.is_group', false);
});

it('includes user and architect details in conversations list and detail', function () {
    $user = User::factory()->create(['role' => 'user', 'name' => 'John Client']);
    $architect = User::factory()->create(['role' => 'architect', 'name' => 'Jane Architect']);
    ArchitectProfile::create([
        'user_id' => $architect->id,
        'headline' => 'Expert Green Architect',
        'status' => 'approved',
    ]);

    $conversation = Conversation::create([
        'name' => 'Private Session',
        'is_group' => false,
        'participant_ids' => [
            (string) $user->getKey(),
            (string) $architect->getKey(),
        ],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
        ],
    ]);

    // GET /conversations list
    actingAs($user, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.user.name', 'John Client')
        ->assertJsonPath('data.0.architect.name', 'Jane Architect')
        ->assertJsonPath('data.0.architect.headline', 'Expert Green Architect');

    // GET /conversations/{id} detail
    actingAs($user, 'sanctum')
        ->getJson("/api/v1/chat/conversations/{$conversation->getKey()}")
        ->assertOk()
        ->assertJsonPath('data.user.name', 'John Client')
        ->assertJsonPath('data.architect.name', 'Jane Architect')
        ->assertJsonPath('data.architect.headline', 'Expert Green Architect');
});

it('formats last chat time correctly', function () {
    $user = User::factory()->create();
    $architect = User::factory()->create(['role' => 'architect']);

    // Mock time to 2026-05-17 12:00:00
    $now = Carbon::parse('2026-05-17 12:00:00');
    Carbon::setTestNow($now);

    // 1. Less than 24 hours ago (e.g. 10:00)
    $conv1 = Conversation::create([
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
    ]);
    $conv1->updated_at = Carbon::parse('2026-05-17 10:00:00');
    $conv1->timestamps = false;
    $conv1->save();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.last_chat_formatted', '10:00');

    // 2. Yesterday (2026-05-16)
    $conv1->updated_at = Carbon::parse('2026-05-16 10:00:00');
    $conv1->save();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.last_chat_formatted', 'Kemarin');

    // 3. Within 7 days (e.g. 3 days ago - 2026-05-14)
    $conv1->updated_at = Carbon::parse('2026-05-14 10:00:00');
    $conv1->save();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.last_chat_formatted', 'Kamis');

    // 4. More than 7 days ago (e.g. 10 days ago - 2026-05-07)
    $conv1->updated_at = Carbon::parse('2026-05-07 10:00:00');
    $conv1->save();

    actingAs($user, 'sanctum')
        ->getJson('/api/v1/chat/conversations')
        ->assertOk()
        ->assertJsonPath('data.0.last_chat_formatted', '07/05/2026');

    // Reset test time
    Carbon::setTestNow();
});

it('supports uploading images as chat attachments and shows image correctly', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'user']);
    $architect = User::factory()->create(['role' => 'architect']);

    // Create an active consultation
    $conversation = Conversation::create([
        'name' => 'Consultation Session',
        'is_group' => false,
        'participant_ids' => [(string) $user->getKey(), (string) $architect->getKey()],
        'last_read_at' => [
            (string) $user->getKey() => now()->toIso8601String(),
            (string) $architect->getKey() => now()->toIso8601String(),
        ],
    ]);

    $consultation = Consultation::create([
        'user_id' => (string) $user->getKey(),
        'architect_id' => (string) $architect->getKey(),
        'consultation_date' => now(),
        'duration_hours' => 2,
        'status' => 'active',
        'verification_status' => 'unverified',
        'payout_status' => 'pending',
        'conversation_id' => (string) $conversation->getKey(),
    ]);

    $conversation->consultation_id = (string) $consultation->getKey();
    $conversation->save();

    $file = UploadedFile::fake()->image('design_concept.png');

    // Post to /messages with file as attachment
    actingAs($architect, 'sanctum') // Architect doesn't trigger AI assistant auto reply
        ->postJson('/api/v1/chat/messages', [
            'conversation_id' => (string) $conversation->getKey(),
            'attachment' => $file,
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.type', 'image')
        ->assertJsonPath('data.attachment_url', Storage::url('chat/attachments/' . $file->hashName()));

    Storage::disk('public')->assertExists('chat/attachments/' . $file->hashName());
});

it('allows searching conversations by user name', function () {
    $userA = User::factory()->create(['name' => 'Budi Sudarsono']);
    $userB = User::factory()->architect()->create(['name' => 'Ardhian Calwa']);
    $userC = User::factory()->architect()->create(['name' => 'John Doe']);

    Conversation::create([
        'participant_ids' => [(string) $userA->getKey(), (string) $userB->getKey()],
    ]);

    Conversation::create([
        'participant_ids' => [(string) $userA->getKey(), (string) $userC->getKey()],
    ]);

    // Search matches 'Ardhian'
    actingAs($userA, 'sanctum')
        ->getJson('/api/v1/chat/conversations?search=Ardhian')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.architect.name', 'Ardhian Calwa');

    // Search matches 'Budi'
    actingAs($userA, 'sanctum')
        ->getJson('/api/v1/chat/conversations?search=Budi')
        ->assertOk()
        ->assertJsonPath('meta.total', 2);

    // Search matches non-existent 'Zack'
    actingAs($userA, 'sanctum')
        ->getJson('/api/v1/chat/conversations?search=Zack')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});

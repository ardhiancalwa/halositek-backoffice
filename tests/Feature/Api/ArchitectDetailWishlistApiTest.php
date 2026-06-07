<?php

use App\Models\ArchitectWishlist;
use App\Models\User;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    DB::connection('mongodb')->table('architect_wishlists')->delete();
    DB::connection('mongodb')->table('users')->delete();
    DB::connection('mongodb')->table('personal_access_tokens')->delete();
});

it('keeps architect detail public without wishlist status', function () {
    $architect = User::factory()->architect()->create();

    $this->getJson("/api/v1/architects/{$architect->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonMissingPath('data.is_wishlisted');
});

it('includes wishlist status when a valid bearer token is provided', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    ArchitectWishlist::create([
        'user_id' => (string) $user->id,
        'architect_id' => (string) $architect->id,
    ]);

    $token = $user->createToken('access-token')->plainTextToken;

    $this->getJson("/api/v1/architects/{$architect->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_wishlisted', true);
});

it('returns false wishlist status when the authenticated user has not saved the architect', function () {
    $user = User::factory()->create();
    $architect = User::factory()->architect()->create();

    $token = $user->createToken('access-token')->plainTextToken;

    $this->getJson("/api/v1/architects/{$architect->id}", [
        'Authorization' => 'Bearer ' . $token,
    ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_wishlisted', false);
});

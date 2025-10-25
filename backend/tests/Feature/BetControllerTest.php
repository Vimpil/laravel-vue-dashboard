<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;

class BetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_bet_creation()
    {
        $user = User::factory()->create(['balance' => 100]);

        $response = $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => (string) Str::uuid(),
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bets', [
            'user_id' => $user->id,
            'event_id' => 1,
            'amount' => 50,
        ]);
    }

    public function test_insufficient_funds()
    {
        $user = User::factory()->create(['balance' => 10]);

        $response = $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => (string) Str::uuid(),
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Insufficient funds']);
    }

    public function test_idempotency()
    {
        $user = User::factory()->create(['balance' => 100]);
        $key = (string) Str::uuid();

        $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => $key,
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response = $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => $key,
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('bets', 1);
    }

    public function test_double_bet()
    {
        $user = User::factory()->create(['balance' => 100]);

        $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => (string) Str::uuid(),
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response = $this->postJson('/api/bets', [
            'event_id' => 1, // Ensure this matches an existing event ID
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'X-User-Id' => $user->id,
            'Idempotency-Key' => (string) Str::uuid(),
            'X-Signature' => 'test-signature',
            'X-Timestamp' => now()->timestamp,
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Double bet not allowed']);
    }
}


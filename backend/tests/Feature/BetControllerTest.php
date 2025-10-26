<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Event; // Import Event model

class BetControllerTest extends TestCase
{
    use RefreshDatabase;

    // Helper to create a default event
    protected function setUp(): void
    {
        parent::setUp();
        Event::factory()->create([
            'id' => 1,
            'title' => 'Test Event',
            'outcomes' => ['win', 'lose'],
        ]);
    }

    public function test_successful_bet_creation()
    {
        $user = User::factory()->create(['balance' => 100]);

        $response = $this->actingAs($user)->postJson('/api/bets', [
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'Idempotency-Key' => (string) Str::uuid(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bets', [
            'user_id' => $user->id,
            'event_id' => 1,
            'amount' => 50,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'balance' => 50,
        ]);
    }

    public function test_insufficient_funds()
    {
        $user = User::factory()->create(['balance' => 10]);

        $response = $this->actingAs($user)->postJson('/api/bets', [
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'Idempotency-Key' => (string) Str::uuid(),
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Insufficient funds']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'balance' => 10,
        ]);
    }

    public function test_idempotency()
    {
        $user = User::factory()->create(['balance' => 100]);
        $key = (string) Str::uuid();

        $response1 = $this->actingAs($user)->postJson('/api/bets', [
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'Idempotency-Key' => $key,
        ]);
        $response1->assertStatus(201);

        $response2 = $this->actingAs($user)->postJson('/api/bets', [
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
        ], [
            'Idempotency-Key' => $key,
        ]);

        $response2->assertStatus(200);
        $this->assertDatabaseCount('bets', 1);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'balance' => 50,
        ]);
    }
}

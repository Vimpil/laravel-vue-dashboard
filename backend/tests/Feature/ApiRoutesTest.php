<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_route()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200); // Updated expected status
    }

    public function test_logout_route()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken; // Create token for Sanctum

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->postJson('/api/logout');

        $response->assertStatus(200); // Updated to use token
    }

    public function test_user_route()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson(['id' => $user->id]);
    }

    public function test_bets_route()
    {
        $user = User::factory()->create(['balance' => 100]); // Ensure sufficient balance
        $this->actingAs($user);

        Event::factory()->create(['id' => 1]); // Create event with id=1

        $response = $this->postJson('/api/bets', [
            'event_id' => 1,
            'amount' => 50,
            'outcome' => 'win',
        ]);

        $response->assertStatus(201); // Ensure bet is created successfully
    }

    public function test_log_test_route()
    {
        Log::shouldReceive('info')->once()->with('Test log entry from /log-test route.');

        $response = $this->getJson('/api/log-test');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Log entry created.']);
    }

    public function test_events_route()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_fetch_all_bets_route()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/bets');

        $response->assertStatus(200);
    }
}

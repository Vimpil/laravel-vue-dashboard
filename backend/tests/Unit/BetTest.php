<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Bet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BetTest extends TestCase
{
    use RefreshDatabase;

    public function test_bet_creation_updates_user_balance()
    {
        $user = User::factory()->create(['balance' => 100]);

        $bet = Bet::create([
            'user_id' => $user->id,
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
            'idempotency_key' => 'test-key',
            'status' => 'placed',
        ]);

        $user->refresh();

        $this->assertEquals(50, $user->balance);
    }

    public function test_bet_creation_fails_with_insufficient_balance()
    {
        $user = User::factory()->create(['balance' => 10]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds');

        Bet::create([
            'user_id' => $user->id,
            'event_id' => 1,
            'outcome' => 'win',
            'amount' => 50,
            'idempotency_key' => 'test-key',
            'status' => 'placed',
        ]);
    }
}

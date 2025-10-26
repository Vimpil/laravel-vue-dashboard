<?php

namespace Database\Factories;

use App\Models\Bet;
use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class BetFactory extends Factory
{
    protected $model = Bet::class;

    public function definition(): array
    {
        $user = User::factory()->create(['balance' => 100]); // Ensure sufficient balance

        return [
            'user_id' => $user->id,
            'event_id' => Event::factory(),
            'outcome' => $this->faker->randomElement(['win', 'lose']),
            'amount' => $this->faker->numberBetween(10, $user->balance), // Dynamically adjust amount
            'idempotency_key' => $this->faker->uuid(),
            'status' => 'placed',
        ];
    }
}

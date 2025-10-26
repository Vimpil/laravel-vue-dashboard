<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Exception;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'outcome',
        'amount',
        'idempotency_key',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bet) {
            $user = User::find($bet->user_id);

            if ($user->balance < $bet->amount) {
                throw new Exception('Insufficient funds');
            }

            $user->balance -= $bet->amount;
            $user->save();
        });
    }
}

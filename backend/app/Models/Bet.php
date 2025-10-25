<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'outcome',
        'amount',
        'idempotency_key',
        'status',
    ];
}
